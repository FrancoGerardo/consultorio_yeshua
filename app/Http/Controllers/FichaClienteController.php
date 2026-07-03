<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ficha;
use App\Models\Servicio;
use App\Models\Especialidad;
use App\Models\Medico;
use App\Models\HorarioMedico;
use App\Models\Cliente;
use App\Services\SalaAsignacionService;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FichaClienteController extends Controller
{
    public function __construct(
        protected SalaAsignacionService $salaAsignacionService
    ) {}

    /**
     * Mostrar página principal de fichas para cliente
     */
    public function paginaPrincipal()
    {
        $usuario = auth()->user();
        $cliente = Cliente::where('usuario_id', $usuario->id)->first();

        if (!$cliente) {
            return redirect()->route('dashboard')
                ->with('error', 'No se encontró tu perfil de cliente.');
        }

        $fichas = Ficha::where('cliente_id', $usuario->id)
            ->with(['servicio', 'medico.usuario.persona', 'sala', 'pagos'])
            ->withExists(['pagos as tiene_pago_pendiente' => function ($query) {
                $query->where('estado', 'PENDIENTE');
            }])
            ->orderBy('fecha', 'desc')
            ->paginate(10);

        $fichas->getCollection()->transform(function (Ficha $ficha) {
            if ($ficha->servicio && in_array($ficha->estado, Ficha::estadosGestionadosPorPago(), true)) {
                $ficha->actualizarEstadoPorPago();
            }

            $ficha->total_pagado = $ficha->calcularTotalPagado();
            $ficha->saldo_pendiente = $ficha->calcularSaldoPendiente();
            $ficha->costo_total = $ficha->obtenerCostoNetoAcordado();

            return $ficha;
        });

        return Inertia::render('FichasCliente/Index', [
            'fichas' => $fichas,
        ]);
    }

    /**
     * Mostrar formulario para crear ficha
     */
    public function crearFicha()
    {
        $servicios = Servicio::where('estado', true)
            ->with(['especialidad', 'medicos.usuario.persona'])
            ->get();
        
        $especialidades = Especialidad::where('estado', 'ACTIVA')
            ->whereHas('medicos')
            ->with('medicos.usuario.persona')
            ->get();

        return Inertia::render('FichasCliente/Crear', [
            'servicios' => $servicios,
            'especialidades' => $especialidades,
        ]);
    }

    /**
     * Obtener médicos según servicio o especialidad seleccionada
     */
    public function obtenerMedicos(Request $request)
    {
        $request->validate([
            'servicio_id' => 'nullable|string|exists:servicios,id',
            'especialidad_id' => 'nullable|string|exists:especialidades,id',
        ]);

        $medicos = collect();
        $servicioId = null;

        if ($request->servicio_id) {
            $servicio = Servicio::with('medicos.usuario.persona')->findOrFail($request->servicio_id);
            $medicos = $servicio->medicos;
            $servicioId = $servicio->id;
        } elseif ($request->especialidad_id) {
            // Si viene especialidad, obtener médicos que tienen esa especialidad
            $especialidad = Especialidad::with('medicos.usuario.persona')->findOrFail($request->especialidad_id);
            $medicos = $especialidad->medicos;
            
            // Buscar el servicio asociado a esa especialidad
            $servicio = Servicio::where('especialidad_id', $request->especialidad_id)
                ->where('estado', true)
                ->first();
            
            if ($servicio) {
                $servicioId = $servicio->id;
            }
        }

        return response()->json([
            'medicos' => $medicos->map(function ($medico) {
                return [
                    'usuario_id' => $medico->usuario_id,
                    'nombre_completo' => $medico->usuario->persona->nombre_completo ?? 'Sin nombre',
                    'especialidades' => $medico->especialidades->pluck('nombre'),
                ];
            }),
            'servicio_id' => $servicioId, // Devolver el servicio_id encontrado
        ]);
    }

    /**
     * Obtener horarios disponibles de un médico
     */
    public function obtenerHorariosDisponibles(Request $request)
    {
        $request->validate([
            'medico_id' => 'required|string|exists:medicos,usuario_id',
            'fecha' => 'required|date_format:Y-m-d',
        ]);

        $medicoId = $request->medico_id;
        $fecha = Carbon::parse($request->fecha);
        $diaSemana = strtoupper($fecha->format('l')); // Monday -> LUNES
        $diasMap = [
            'MONDAY' => 'LUNES',
            'TUESDAY' => 'MARTES',
            'WEDNESDAY' => 'MIERCOLES',
            'THURSDAY' => 'JUEVES',
            'FRIDAY' => 'VIERNES',
            'SATURDAY' => 'SABADO',
            'SUNDAY' => 'DOMINGO',
        ];
        $diaSemana = $diasMap[$diaSemana] ?? $diaSemana;

        // Obtener horarios del médico para ese día
        $horariosMedico = HorarioMedico::where('medico_id', $medicoId)
            ->where('dia_semana', $diaSemana)
            ->where('activo', true)
            ->get();

        $horasDisponibles = [];

        foreach ($horariosMedico as $horario) {
            $inicioBloque = Carbon::parse($horario->hora_inicio);
            $finBloque = Carbon::parse($horario->hora_fin);

            // Generar slots de 30 minutos
            while ($inicioBloque->copy()->addMinutes(30)->lte($finBloque)) {
                $horaInicioSlot = $inicioBloque->copy();
                $horaFinSlot = $inicioBloque->copy()->addMinutes(30);

                // Verificar si este slot está ocupado (excluye fichas canceladas)
                $slotOcupado = Ficha::where('medico_id', $medicoId)
                    ->where('fecha', $fecha->toDateString())
                    ->ocupanHorario()
                    ->where(function ($query) use ($horaInicioSlot, $horaFinSlot) {
                        $query->whereBetween('hora', [
                            $horaInicioSlot->format('H:i:s'),
                            $horaFinSlot->format('H:i:s')
                        ]);
                    })
                    ->exists();

                if (!$slotOcupado) {
                    $horasDisponibles[] = $horaInicioSlot->format('H:i');
                }

                $inicioBloque->addMinutes(30);
            }
        }

        return response()->json([
            'horas' => array_unique($horasDisponibles),
        ]);
    }

    /**
     * Guardar nueva ficha
     */
    public function guardarFicha(Request $request)
    {
        $usuario = auth()->user();

        $request->validate([
            'servicio_id' => 'required|string|exists:servicios,id',
            'medico_id' => 'required|string|exists:medicos,usuario_id',
            'fecha' => 'required|date',
            'hora' => 'required|string',
            'motivo_consulta' => 'nullable|string|max:500',
        ]);

        $fichaId = Str::uuid()->toString();
        $ficha = Ficha::create([
            'id' => $fichaId,
            'cliente_id' => $usuario->id,
            'servicio_id' => $request->servicio_id,
            'medico_id' => $request->medico_id,
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'estado' => 'PENDIENTE_PAGO',
            'motivo_consulta' => $request->motivo_consulta,
        ]);

        $ficha->intentarAsignarSalaAutomaticamente();

        // Redirigir a pantalla de selección de plan de pago
        return redirect()->route('cliente.pagos.seleccionar-plan', $fichaId)
            ->with('success', 'Ficha creada exitosamente. Selecciona tu plan de pago.');
    }

    /**
     * Cancelar ficha pendiente de pago (solo el cliente dueño).
     */
    public function cancelarFicha(string $fichaId)
    {
        $usuario = auth()->user();
        $ficha = Ficha::with('pagos')->findOrFail($fichaId);

        if ($ficha->cliente_id !== $usuario->id) {
            return redirect()->route('cliente.fichas.index')
                ->with('error', 'No tienes permiso para cancelar esta ficha.');
        }

        if (! $ficha->puedeCancelarsePorCliente()) {
            if ($ficha->estaEsperandoConfirmacionPago()) {
                return redirect()->route('cliente.fichas.index')
                    ->with('error', 'No puedes cancelar mientras hay un pago en proceso de confirmación.');
            }

            return redirect()->route('cliente.fichas.index')
                ->with('error', 'Solo puedes cancelar fichas pendientes de pago sin pagos registrados.');
        }

        DB::transaction(function () use ($ficha) {
            $ficha->pagos()
                ->where('estado', 'PENDIENTE')
                ->update(['estado' => 'ANULADO']);

            $ficha->update(['estado' => 'CANCELADA']);
        });

        if ($ficha->sala_id) {
            $this->salaAsignacionService->sincronizarEstadoSala($ficha->sala_id);
        }

        return redirect()->route('cliente.fichas.index')
            ->with('success', 'Ficha cancelada correctamente. El horario quedó disponible.');
    }
}

