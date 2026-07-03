<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ficha;
use App\Models\HistorialClinico;
use App\Models\Seguimiento;
use App\Models\Medico;
use App\Services\SalaAsignacionService;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsultorioController extends Controller
{
    public function __construct(
        protected SalaAsignacionService $salaAsignacionService
    ) {}

    /**
     * Página principal del consultorio - Cola de pacientes del médico
     */
    public function colaPacientes()
    {
        $this->authorize('gestionar-seguimientos');

        // Obtener médico logueado
        $usuario = auth()->user();
        $medico = Medico::where('usuario_id', $usuario->id)->first();

        if (!$medico) {
            return redirect()->route('dashboard')
                ->with('error', 'Solo los médicos pueden acceder al consultorio.');
        }

        // Obtener pacientes del día en cola (programados + en espera/atención)
        $baseQuery = Ficha::with([
            'cliente.usuario.persona',
            'servicio',
            'sala',
        ])
            ->delDia()
            ->delMedico($medico->usuario_id);

        $fichasProgramadas = (clone $baseQuery)
            ->programadas()
            ->orderBy('hora')
            ->get();

        $fichasAtendibles = (clone $baseQuery)
            ->whereIn('estado', ['EN_ESPERA', 'EN_ATENCION'])
            ->orderByRaw("CASE WHEN estado = 'EN_ATENCION' THEN 1 WHEN estado = 'EN_ESPERA' THEN 2 ELSE 3 END")
            ->orderBy('hora')
            ->get();

        // Estadísticas del día
        $statsQuery = Ficha::delDia()->delMedico($medico->usuario_id);
        $estadisticas = [
            'total_citas_dia' => (clone $statsQuery)->count(),
            'programadas' => (clone $statsQuery)->programadas()->count(),
            'en_espera' => (clone $statsQuery)->enEspera()->count(),
            'atendidas' => (clone $statsQuery)->where('estado', 'ATENDIDA')->count(),
            'en_atencion' => (clone $statsQuery)->enAtencion()->first(),
        ];

        return Inertia::render('Consultorio/ColaPacientes', [
            'fichas_programadas' => $fichasProgramadas,
            'fichas_atendibles' => $fichasAtendibles,
            'estadisticas' => $estadisticas,
            'medico' => $medico->load('usuario.persona'),
        ]);
    }

    /**
     * Obtener historial completo del paciente
     */
    public function obtenerHistorialCompleto(string $fichaId)
    {
        $this->authorize('gestionar-seguimientos');

        $ficha = Ficha::with([
            'cliente.usuario.persona',
            'cliente.historialClinico',
            'servicio',
            'medico.usuario.persona',
            'sala'
        ])->findOrFail($fichaId);

        // Verificar que el médico logueado sea el asignado a la ficha
        if ($ficha->medico_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para ver este paciente.',
            ], 403);
        }

        // Obtener todos los seguimientos previos del paciente
        $seguimientosPrevios = Seguimiento::with([
            'ficha.servicio',
            'ficha.medico.usuario.persona'
        ])
            ->whereHas('ficha', function($query) use ($ficha) {
                $query->where('cliente_id', $ficha->cliente_id);
            })
            ->where('ficha_id', '!=', $fichaId)
            ->orderBy('fecha', 'desc')
            ->limit(10)
            ->get();

        $historialClinico = HistorialClinico::asegurarParaCliente($ficha->cliente_id);

        return response()->json([
            'success' => true,
            'ficha' => $ficha,
            'historial_clinico' => $historialClinico,
            'seguimientos_previos' => $seguimientosPrevios,
        ]);
    }

    /**
     * Iniciar atención de un paciente
     */
    public function iniciarAtencion(string $fichaId)
    {
        $this->authorize('gestionar-seguimientos');

        $ficha = Ficha::findOrFail($fichaId);

        // Verificar que el médico logueado sea el asignado
        if ($ficha->medico_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No puede atender pacientes de otro médico.',
            ], 403);
        }

        // Verificar que no haya otra ficha en atención
        $otraEnAtencion = Ficha::delDia()
            ->delMedico(auth()->id())
            ->enAtencion()
            ->where('id', '!=', $fichaId)
            ->first();

        if ($otraEnAtencion) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tiene un paciente en atención. Finalice la consulta actual primero.',
            ], 400);
        }

        if ($ficha->estado === 'EN_ATENCION') {
            return response()->json([
                'success' => true,
                'message' => 'Atención ya iniciada.',
                'ficha' => $ficha,
            ]);
        }

        if (!$ficha->puedeIniciarAtencionMedica()) {
            return response()->json([
                'success' => false,
                'message' => 'El paciente debe estar en sala de espera. Solicite check-in en recepción.',
            ], 400);
        }

        // Iniciar atención
        $ficha->iniciarAtencion();

        if ($ficha->sala_id) {
            $this->salaAsignacionService->sincronizarEstadoSala($ficha->sala_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Atención iniciada correctamente.',
            'ficha' => $ficha->fresh(),
        ]);
    }

    /**
     * Guardar nuevo seguimiento (consulta médica)
     */
    public function guardarConsulta(Request $request, string $fichaId)
    {
        $this->authorize('gestionar-seguimientos');

        $ficha = Ficha::findOrFail($fichaId);

        // Verificar permisos
        if ($ficha->medico_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para registrar consulta de este paciente.',
            ], 403);
        }

        // Validar datos
        $datos = $request->validate([
            'tipo' => 'required|in:TRIAGE,CONSULTA,TRATAMIENTO',
            'signos_vitales' => 'nullable|array',
            'motivo_consulta' => 'nullable|string',
            'nivel_urgencia' => 'nullable|in:BAJA,MEDIA,ALTA,URGENTE',
            'diagnostico' => 'required|string',
            'codigo_cie10' => 'nullable|string|max:20',
            'observaciones' => 'nullable|string',
            'tratamiento_prescrito' => 'nullable|string',
            'medicamentos' => 'nullable|array',
            'examenes_solicitados' => 'nullable|array',
            'interconsultas' => 'nullable|array',
            'proxima_cita' => 'nullable|date',
            'indicaciones_proxima_cita' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Crear seguimiento
            $seguimiento = Seguimiento::create([
                'id' => Str::uuid()->toString(),
                'ficha_id' => $fichaId,
                'medico_id' => auth()->id(),
                'tipo' => $datos['tipo'],
                'estado' => 'ACTIVO',
                'fecha' => now(),
                'signos_vitales' => $datos['signos_vitales'] ?? null,
                'motivo_consulta' => $datos['motivo_consulta'] ?? null,
                'nivel_urgencia' => $datos['nivel_urgencia'] ?? null,
                'diagnostico' => $datos['diagnostico'],
                'codigo_cie10' => $datos['codigo_cie10'] ?? null,
                'observaciones' => $datos['observaciones'] ?? null,
                'tratamiento_prescrito' => $datos['tratamiento_prescrito'] ?? null,
                'medicamentos' => $datos['medicamentos'] ?? null,
                'examenes_solicitados' => $datos['examenes_solicitados'] ?? null,
                'interconsultas' => $datos['interconsultas'] ?? null,
                'proxima_cita' => $datos['proxima_cita'] ?? null,
                'indicaciones_proxima_cita' => $datos['indicaciones_proxima_cita'] ?? null,
                'ip_registro' => $request->ip(),
                'navegador' => $request->userAgent(),
            ]);

            // Finalizar atención
            $ficha->finalizarAtencion();

            if ($ficha->sala_id) {
                $this->salaAsignacionService->sincronizarEstadoSala($ficha->sala_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Consulta registrada exitosamente.',
                'seguimiento' => $seguimiento,
                'ficha' => $ficha->fresh(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al guardar consulta: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la consulta: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar historial clínico del paciente
     */
    public function actualizarHistorialClinico(Request $request, string $clienteId)
    {
        $usuario = auth()->user();
        if (!$usuario->can('gestionar-historiales-clinicos') && !$usuario->can('editar-historiales-clinicos')) {
            abort(403);
        }

        $datos = $request->validate([
            'grupo_sanguineo' => 'nullable|string|max:5',
            'factor_rh' => 'nullable|string|max:10',
            'alergias' => 'nullable|string',
            'enfermedades_cronicas' => 'nullable|string',
            'antecedentes_quirurgicos' => 'nullable|string',
            'antecedentes_familiares' => 'nullable|string',
            'antecedentes_personales' => 'nullable|string',
            'peso_habitual' => 'nullable|numeric',
            'estatura' => 'nullable|numeric',
            'habitos' => 'nullable|array',
            'vacunas' => 'nullable|array',
            'transfusiones_previas' => 'nullable|string',
            'hospitalizaciones_previas' => 'nullable|string',
            'notas_importantes' => 'nullable|string',
            'medicamentos_habituales' => 'nullable|string',
        ]);

        try {
            $historial = HistorialClinico::asegurarParaCliente($clienteId);
            $historial->update($datos);

            return response()->json([
                'success' => true,
                'message' => 'Historial clínico actualizado exitosamente.',
                'historial' => $historial,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al actualizar historial: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el historial clínico.',
            ], 500);
        }
    }
}

