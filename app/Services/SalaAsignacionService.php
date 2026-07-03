<?php

namespace App\Services;

use App\Models\Ficha;
use App\Models\Sala;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SalaAsignacionService
{
    public function obtenerTiposSalaParaServicio(Servicio $servicio): array
    {
        if (! empty($servicio->tipo_sala_requerido)) {
            return [$servicio->tipo_sala_requerido];
        }

        $mapa = config('salas.categoria_servicio_a_tipos_sala', []);

        return $mapa[$servicio->categoria] ?? ['CONSULTORIO'];
    }

    public function obtenerSalasCompatibles(Servicio $servicio): Collection
    {
        $tipos = $this->obtenerTiposSalaParaServicio($servicio);
        $estados = config('salas.estados_asignables', ['DISPONIBLE', 'OCUPADA']);

        return Sala::whereIn('categoria', $tipos)
            ->whereIn('estado', $estados)
            ->orderBy('numero')
            ->get();
    }

    public function buscarSalaDisponible(
        Servicio $servicio,
        string $fecha,
        string $hora,
        ?string $medicoId = null,
        ?string $excluirFichaId = null
    ): ?Sala {
        $duracion = (int) ($servicio->duracion_minutos ?? config('salas.duracion_default_minutos', 30));

        if ($medicoId) {
            $salaFijaId = config("salas.medico_sala_fija.{$medicoId}");
            if ($salaFijaId && $this->salaEstaDisponible($salaFijaId, $servicio, $fecha, $hora, $excluirFichaId)) {
                return Sala::find($salaFijaId);
            }
        }

        foreach ($this->obtenerSalasCompatibles($servicio) as $sala) {
            if ($this->salaEstaDisponible($sala->id, $servicio, $fecha, $hora, $excluirFichaId)) {
                return $sala;
            }
        }

        return null;
    }

    public function salaEstaDisponible(
        string $salaId,
        Servicio $servicio,
        string $fecha,
        string $hora,
        ?string $excluirFichaId = null
    ): bool {
        $sala = Sala::find($salaId);
        if (! $sala) {
            return false;
        }

        $tiposPermitidos = $this->obtenerTiposSalaParaServicio($servicio);
        if (! in_array($sala->categoria, $tiposPermitidos, true)) {
            return false;
        }

        $estadosAsignables = config('salas.estados_asignables', ['DISPONIBLE', 'OCUPADA']);
        if (! in_array($sala->estado, $estadosAsignables, true)) {
            return false;
        }

        return ! $this->tieneConflictoHorario($salaId, $fecha, $hora, $servicio, $excluirFichaId);
    }

    public function tieneConflictoHorario(
        string $salaId,
        string $fecha,
        string $hora,
        Servicio $servicio,
        ?string $excluirFichaId = null
    ): bool {
        $duracion = (int) ($servicio->duracion_minutos ?? config('salas.duracion_default_minutos', 30));
        $inicio = Carbon::parse("{$fecha} {$hora}");
        $fin = $inicio->copy()->addMinutes($duracion);

        $fichas = Ficha::with('servicio')
            ->where('sala_id', $salaId)
            ->whereDate('fecha', $fecha)
            ->ocupanHorario()
            ->when($excluirFichaId, fn ($query) => $query->where('id', '!=', $excluirFichaId))
            ->get();

        foreach ($fichas as $ficha) {
            $horaFicha = $this->normalizarHora($ficha->hora);
            $fInicio = Carbon::parse($ficha->fecha->format('Y-m-d').' '.$horaFicha);
            $fDuracion = (int) ($ficha->servicio->duracion_minutos ?? config('salas.duracion_default_minutos', 30));
            $fFin = $fInicio->copy()->addMinutes($fDuracion);

            if ($inicio->lt($fFin) && $fin->gt($fInicio)) {
                return true;
            }
        }

        return false;
    }

    public function asignarSalaAFicha(Ficha $ficha, bool $forzar = false): bool
    {
        $ficha->loadMissing('servicio');

        if (! $ficha->servicio) {
            return false;
        }

        if ($ficha->sala_id && ! $forzar) {
            return true;
        }

        $sala = $this->buscarSalaDisponible(
            $ficha->servicio,
            $ficha->fecha->format('Y-m-d'),
            $this->normalizarHora($ficha->hora),
            $ficha->medico_id,
            $ficha->id
        );

        if (! $sala) {
            return false;
        }

        $ficha->update(['sala_id' => $sala->id]);

        return true;
    }

    public function sincronizarEstadoSala(string $salaId): void
    {
        $sala = Sala::find($salaId);
        if (! $sala || in_array($sala->estado, ['MANTENIMIENTO', 'INACTIVA'], true)) {
            return;
        }

        $hayAtencionActiva = Ficha::where('sala_id', $salaId)
            ->where('estado', 'EN_ATENCION')
            ->exists();

        $sala->update([
            'estado' => $hayAtencionActiva ? 'OCUPADA' : 'DISPONIBLE',
        ]);
    }

    private function normalizarHora(mixed $hora): string
    {
        if ($hora instanceof Carbon) {
            return $hora->format('H:i:s');
        }

        $texto = (string) $hora;
        if (preg_match('/^\d{2}:\d{2}$/', $texto)) {
            return $texto.':00';
        }

        if (strlen($texto) >= 8) {
            return substr($texto, 0, 8);
        }

        return $texto;
    }
}
