<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReporteGenerado;
use App\Models\Medico;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Services\ReportePDFService;
use App\Services\ReporteExcelService;
use App\Jobs\GenerarReporteAutomaticoJob;

class ReporteController extends Controller
{
    /**
     * Página principal para generar reportes
     */
    public function index()
    {
        $this->authorize('gestionar-reportes');

        return Inertia::render('Reportes/Generar');
    }

    /**
     * Obtener datos para generar reportes
     */
    public function obtenerDatosGeneracion()
    {
        $this->authorize('gestionar-reportes');

        // Obtener lista de médicos para filtros
        $medicos = Medico::with('usuario.persona')
            ->get()
            ->map(function($medico) {
                return [
                    'id' => $medico->usuario_id,
                    'nombre' => $medico->usuario->persona->nombre_completo ?? 'N/A',
                ];
            });

        return response()->json([
            'medicos' => $medicos,
        ]);
    }

    /**
     * Generar reporte en PDF
     */
    public function generarPDF(Request $request, ReportePDFService $pdfService)
    {
        $this->authorize('crear-reportes');

        $datos = $request->validate([
            'tipo' => 'required|in:citas,ingresos,pacientes_medico',
            'filtros' => 'required|array',
        ]);

        try {
            // Generar PDF según tipo
            switch ($datos['tipo']) {
                case 'citas':
                    $resultado = $pdfService->generarReporteCitas($datos['filtros']);
                    $nombreReporte = 'Reporte de Citas';
                    break;
                case 'ingresos':
                    $resultado = $pdfService->generarReporteIngresos($datos['filtros']);
                    $nombreReporte = 'Reporte de Ingresos';
                    break;
                case 'pacientes_medico':
                    $resultado = $pdfService->generarReportePacientesPorMedico($datos['filtros']);
                    $nombreReporte = 'Reporte de Pacientes por Médico';
                    break;
            }

            // Guardar registro del reporte generado
            $reporteGenerado = ReporteGenerado::create([
                'nombre' => $nombreReporte,
                'tipo' => $datos['tipo'],
                'filtros' => $datos['filtros'],
                'formato' => 'pdf',
                'archivo_path' => $resultado['ruta_publica'],
                'estado' => 'listo',
                'usuario_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'reporte' => $reporteGenerado,
                'url_descarga' => Storage::url($resultado['ruta_publica']),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar PDF: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generar reporte en Excel
     */
    public function generarExcel(Request $request, ReporteExcelService $excelService)
    {
        $this->authorize('crear-reportes');

        $datos = $request->validate([
            'tipo' => 'required|in:citas,ingresos,pacientes_medico',
            'filtros' => 'required|array',
        ]);

        try {
            // Generar Excel según tipo
            switch ($datos['tipo']) {
                case 'citas':
                    $resultado = $excelService->generarReporteCitas($datos['filtros']);
                    $nombreReporte = 'Reporte de Citas';
                    break;
                case 'ingresos':
                    $resultado = $excelService->generarReporteIngresos($datos['filtros']);
                    $nombreReporte = 'Reporte de Ingresos';
                    break;
                case 'pacientes_medico':
                    $resultado = $excelService->generarReportePacientesPorMedico($datos['filtros']);
                    $nombreReporte = 'Reporte de Pacientes por Médico';
                    break;
            }

            // Guardar registro del reporte generado
            $reporteGenerado = ReporteGenerado::create([
                'nombre' => $nombreReporte,
                'tipo' => $datos['tipo'],
                'filtros' => $datos['filtros'],
                'formato' => 'excel',
                'archivo_path' => $resultado['ruta_publica'],
                'estado' => 'listo',
                'usuario_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'reporte' => $reporteGenerado,
                'url_descarga' => Storage::url($resultado['ruta_publica']),
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al generar Excel: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar reportes generados
     */
    public function listarGenerados()
    {
        $this->authorize('ver-reportes');

        $reportes = ReporteGenerado::with('usuario.persona')
            ->orderBy('fecha_generacion', 'desc')
            ->paginate(10);

        return response()->json([
            'reportes' => $reportes,
        ]);
    }

    /**
     * Descargar reporte generado
     */
    public function descargarGenerado(string $id)
    {
        $this->authorize('ver-reportes');

        $reporte = ReporteGenerado::findOrFail($id);

        // Verificar que el archivo existe
        if (!Storage::disk('public')->exists($reporte->archivo_path)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo del reporte no existe.',
            ], 404);
        }

        return Storage::disk('public')->download($reporte->archivo_path);
    }

    /**
     * Eliminar reporte generado
     */
    public function eliminarGenerado(string $id)
    {
        $this->authorize('eliminar-reportes');

        $reporte = ReporteGenerado::findOrFail($id);

        // Eliminar archivo físico
        if (Storage::disk('public')->exists($reporte->archivo_path)) {
            Storage::disk('public')->delete($reporte->archivo_path);
        }

        // Eliminar registro
        $reporte->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reporte eliminado exitosamente.',
        ]);
    }

    /**
     * Programar reporte automático
     */
    public function programarReporteAutomatico(Request $request)
    {
        $this->authorize('crear-reportes');

        $datos = $request->validate([
            'tipo' => 'required|in:citas,ingresos,pacientes_medico',
            'formato' => 'required|in:pdf,excel',
            'filtros' => 'required|array',
            'enviar_email' => 'boolean',
        ]);

        try {
            // Encolar el Job para generación
            GenerarReporteAutomaticoJob::dispatch(
                $datos['tipo'],
                $datos['formato'],
                $datos['filtros'],
                auth()->id(),
                $datos['enviar_email'] ?? false
            );

            return response()->json([
                'success' => true,
                'message' => 'Reporte programado exitosamente. Será procesado en breve.',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al programar reporte: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al programar el reporte: ' . $e->getMessage(),
            ], 500);
        }
    }
}
