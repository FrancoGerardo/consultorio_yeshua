<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ficha;
use App\Models\HistorialClinico;
use App\Models\Seguimiento;
use App\Models\Medico;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsultorioController extends Controller
{
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

        // Obtener pacientes del día en cola
        $fichasDelDia = Ficha::with([
            'cliente.usuario.persona',
            'servicio',
            'sala'
        ])
            ->delDia()
            ->delMedico($medico->usuario_id)
            ->whereIn('estado', ['CONFIRMADA', 'EN_ESPERA', 'EN_ATENCION'])
            ->orderByRaw("CASE 
                WHEN estado = 'EN_ATENCION' THEN 1
                WHEN estado = 'EN_ESPERA' THEN 2
                WHEN estado = 'CONFIRMADA' THEN 3
                ELSE 4 END")
            ->orderBy('hora')
            ->get();

        // Estadísticas del día
        $estadisticas = [
            'total_citas_dia' => Ficha::delDia()->delMedico($medico->usuario_id)->count(),
            'en_espera' => Ficha::delDia()->delMedico($medico->usuario_id)->enEspera()->count(),
            'atendidas' => Ficha::delDia()->delMedico($medico->usuario_id)->where('estado', 'ATENDIDA')->count(),
            'en_atencion' => Ficha::delDia()->delMedico($medico->usuario_id)->enAtencion()->first(),
        ];

        return Inertia::render('Consultorio/ColaPacientes', [
            'fichas' => $fichasDelDia,
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

        return response()->json([
            'success' => true,
            'ficha' => $ficha,
            'historial_clinico' => $ficha->cliente->historialClinico,
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

        // Iniciar atención
        $ficha->iniciarAtencion();

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
        $this->authorize('gestionar-historiales-clinicos');

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
            $historial = HistorialClinico::where('cliente_id', $clienteId)->firstOrFail();
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

    /**
     * Marcar llegada de paciente (check-in)
     */
    public function marcarLlegada(string $fichaId)
    {
        $this->authorize('gestionar-seguimientos');

        $ficha = Ficha::findOrFail($fichaId);

        if ($ficha->medico_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para esta acción.',
            ], 403);
        }

        $ficha->marcarLlegada();

        return response()->json([
            'success' => true,
            'message' => 'Llegada registrada correctamente.',
            'ficha' => $ficha->fresh(),
        ]);
    }
}

