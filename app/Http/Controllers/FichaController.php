<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ficha;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Medico;
use App\Models\Sala;
use App\Models\HorarioMedico;
use App\Models\Pago;
use Inertia\Inertia;
use App\Http\Requests\StoreFichaRequest;
use App\Http\Requests\UpdateFichaRequest;
use App\Services\SalaAsignacionService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FichaController extends Controller
{
    public function __construct(
        protected SalaAsignacionService $salaAsignacionService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function paginaPrincipalFicha()
    {
        $usuario = auth()->user();

        if (!$usuario->can('gestionar-fichas') && !$usuario->can('ver-fichas')) {
            abort(403);
        }

        $query = Ficha::with(['cliente.usuario.persona', 'servicio', 'medico.usuario.persona', 'sala']);

        if (!$usuario->can('gestionar-fichas') && $usuario->medico) {
            $query->where('medico_id', $usuario->medico->usuario_id);
        }

        $fichas = $query->orderByDesc('fecha')->orderByDesc('hora')->paginate(10);
        $contadorVisitas = DB::table('visitas_paginas')
            ->where('ruta', 'fichas')
            ->count();

        return Inertia::render('Fichas/Index', [
            'fichas' => $fichas,
            'contadorVisitas' => $contadorVisitas,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearFicha()
    {
        $this->authorize('crear-fichas');

        return response()->json($this->obtenerDatosFormularioFicha());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function guardarFicha(StoreFichaRequest $datos)
    {
        $servicio = Servicio::findOrFail($datos->servicio_id);

        if ($datos->sala_id && ! $this->salaAsignacionService->salaEstaDisponible(
            $datos->sala_id,
            $servicio,
            $datos->fecha,
            $datos->hora
        )) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['sala_id' => 'La sala seleccionada no está disponible en ese horario.']);
        }

        $fichaId = Str::uuid()->toString();
        $ficha = Ficha::create([
            'id' => $fichaId,
            'cliente_id' => $datos->cliente_id,
            'servicio_id' => $datos->servicio_id,
            'medico_id' => $datos->medico_id,
            'sala_id' => $datos->sala_id,
            'fecha' => $datos->fecha,
            'hora' => $datos->hora,
            'estado' => 'PENDIENTE_PAGO',
            'motivo_consulta' => $datos->motivo_consulta,
        ]);

        if (! $ficha->sala_id) {
            $ficha->intentarAsignarSalaAutomaticamente();
        }

        return redirect()->route('fichas.pago.plan', $fichaId)
            ->with('success', 'Ficha registrada. Complete el pago para confirmar la cita.');
    }

    /**
     * Display the specified resource.
     */
    public function mostrarFicha(string $id)
    {
        $this->authorize('mostrar-fichas');

        $ficha = Ficha::with(['cliente.usuario.persona', 'servicio', 'medico.usuario.persona', 'sala'])
            ->findOrFail($id);

        return response()->json([
            'ficha' => $ficha,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editarFicha(string $id)
    {
        $this->authorize('editar-fichas');

        $ficha = Ficha::with([
                'cliente.usuario.persona',
                'servicio.especialidad',
                'servicio.medicos.usuario.persona',
                'medico.usuario.persona',
                'sala',
            ])
            ->findOrFail($id);

        return response()->json(array_merge(
            ['ficha' => $ficha],
            $this->obtenerDatosFormularioFicha()
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function actualizarFicha(UpdateFichaRequest $datos, string $id)
    {
        $ficha = Ficha::findOrFail($id);
        $servicio = Servicio::findOrFail($datos->servicio_id);

        if ($datos->sala_id && ! $this->salaAsignacionService->salaEstaDisponible(
            $datos->sala_id,
            $servicio,
            $datos->fecha,
            $datos->hora,
            $ficha->id
        )) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['sala_id' => 'La sala seleccionada no está disponible en ese horario.']);
        }

        $salaAnteriorId = $ficha->sala_id;

        $ficha->update([
            'cliente_id' => $datos->cliente_id,
            'servicio_id' => $datos->servicio_id,
            'medico_id' => $datos->medico_id,
            'sala_id' => $datos->sala_id,
            'fecha' => $datos->fecha,
            'hora' => $datos->hora,
            'motivo_consulta' => $datos->motivo_consulta,
        ]);

        if (! $ficha->sala_id) {
            $ficha->intentarAsignarSalaAutomaticamente();
        }

        if ($salaAnteriorId && $salaAnteriorId !== $ficha->sala_id) {
            $this->salaAsignacionService->sincronizarEstadoSala($salaAnteriorId);
        }

        return redirect()->route('fichas.index')
            ->with('success', 'Ficha actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function eliminarFicha(string $id)
    {
        $this->authorize('eliminar-fichas');

        $ficha = Ficha::findOrFail($id);

        $salaAnteriorId = $ficha->sala_id;

        $ficha->delete();

        if ($salaAnteriorId) {
            $this->salaAsignacionService->sincronizarEstadoSala($salaAnteriorId);
        }

        return redirect()->route('fichas.index')
            ->with('success', 'Ficha eliminada exitosamente.');
    }

    /**
     * ✅ Flujo profesional (Secretaria/Admin):
     * Registrar el pago de SALDO en EFECTIVO sin reprogramar campos de agenda.
     */
    public function registrarSaldoEfectivo(string $id)
    {
        // La secretaria gestiona fichas; el pago en ventanilla es parte de ese flujo operativo.
        $this->authorize('gestionar-fichas');

        $ficha = Ficha::with(['servicio', 'pagos'])->findOrFail($id);

        // Solo aplica si ya hubo anticipo pagado (flujo anticipo+saldo)
        if ($ficha->estado !== 'ANTICIPO_PAGADO') {
            return redirect()->route('fichas.index')
                ->with('error', 'Esta ficha no está en estado ANTICIPO_PAGADO. No corresponde registrar saldo.');
        }

        $saldoPendiente = (float) $ficha->calcularSaldoPendiente();

        if ($saldoPendiente <= 0) {
            $ficha->actualizarEstadoPorPago();
            return redirect()->route('fichas.index')
                ->with('success', 'La ficha ya no tiene saldo pendiente.');
        }

        Pago::create([
            'id' => Str::uuid()->toString(),
            'ficha_id' => $ficha->id,
            'monto' => $saldoPendiente,
            'tipo' => 'CONTADO',
            'concepto' => 'SALDO',
            'fecha_pago' => now(),
            'metodo_pago' => 'EFECTIVO',
            'estado' => 'PAGADO',
        ]);

        $ficha->refresh();
        $ficha->actualizarEstadoPorPago();

        return redirect()->route('fichas.index')
            ->with('success', 'Saldo registrado en EFECTIVO. La ficha fue actualizada.');
    }

    /**
     * Devuelve los horarios disponibles para un médico/servicio en una fecha concreta.
     */
    public function horariosDisponibles(Request $request)
    {
        $this->authorize('crear-fichas');

        $datos = $request->validate([
            'medico_id' => 'required|string|exists:medicos,usuario_id',
            'servicio_id' => 'required|string|exists:servicios,id',
            'fecha' => 'required|date',
        ]);

        $servicio = Servicio::findOrFail($datos['servicio_id']);
        $duracion = $servicio->duracion_minutos ?? 30;

        $fecha = Carbon::parse($datos['fecha']);
        $diaSemana = $this->obtenerDiaSemana($fecha);

        $horariosConfigurados = HorarioMedico::where('medico_id', $datos['medico_id'])
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        if ($horariosConfigurados->isEmpty()) {
            return response()->json(['horas' => []]);
        }

        $horasOcupadas = Ficha::where('medico_id', $datos['medico_id'])
            ->where('fecha', $fecha->toDateString())
            ->ocupanHorario()
            ->pluck('hora')
            ->map(fn ($hora) => $this->normalizarHoraTexto($hora))
            ->toArray();

        $slots = [];

        foreach ($horariosConfigurados as $configuracion) {
            $inicio = $this->parseHoraDelDia($configuracion->hora_inicio);
            $fin = $this->parseHoraDelDia($configuracion->hora_fin);

            $cursor = $inicio->copy();
            while ($cursor->copy()->addMinutes($duracion)->lte($fin)) {
                $horaTexto = $cursor->format('H:i');
                if (!in_array($horaTexto, $horasOcupadas, true)) {
                    $slots[] = $horaTexto;
                }
                $cursor->addMinutes($duracion);
            }
        }

        $slots = array_values(array_unique($slots));

        return response()->json([
            'horas' => $slots,
        ]);
    }

    /**
     * Obtiene la data común que necesitan los formularios de fichas.
     */
    private function obtenerDatosFormularioFicha(): array
    {
        $clientes = Cliente::with('usuario.persona')
            ->orderBy('usuario_id')
            ->get();

        $servicios = Servicio::with([
                'especialidad',
                'medicos.usuario.persona',
            ])
            ->where('estado', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        $medicos = Medico::with(['usuario.persona', 'especialidades'])
            ->get();

        $salas = Sala::whereIn('estado', config('salas.estados_asignables', ['DISPONIBLE', 'OCUPADA']))
            ->orderBy('numero')
            ->get();

        return [
            'clientes' => $clientes,
            'servicios' => $servicios,
            'medicos' => $medicos,
            'salas' => $salas,
            'tipos_sala' => config('salas.tipos', []),
            'mapa_categoria_sala' => config('salas.categoria_servicio_a_tipos_sala', []),
        ];
    }

    /**
     * Convierte una fecha en el formato textual usado en horarios_medicos.
     */
    private function obtenerDiaSemana(Carbon $fecha): string
    {
        $mapa = [
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIERCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES',
            6 => 'SABADO',
            7 => 'DOMINGO',
        ];

        return $mapa[$fecha->dayOfWeekIso] ?? 'LUNES';
    }

    /**
     * Normaliza un valor TIME/datetime a texto HH:MM.
     */
    private function normalizarHoraTexto(mixed $hora): string
    {
        return $this->parseHoraDelDia($hora)->format('H:i');
    }

    /**
     * Convierte hora de BD (TIME, string o Carbon) a Carbon solo con componente horario.
     */
    private function parseHoraDelDia(mixed $hora): Carbon
    {
        if ($hora instanceof Carbon) {
            return Carbon::createFromTime($hora->hour, $hora->minute, $hora->second);
        }

        $texto = trim((string) $hora);

        if (preg_match('/(\d{1,2}:\d{2}(?::\d{2})?)/', $texto, $coincidencia)) {
            $fragmento = $coincidencia[1];
            $formato = strlen($fragmento) === 5 ? 'H:i' : 'H:i:s';

            return Carbon::createFromFormat($formato, $fragmento);
        }

        $parseada = Carbon::parse($texto);

        return Carbon::createFromTime($parseada->hour, $parseada->minute, $parseada->second);
    }
}

