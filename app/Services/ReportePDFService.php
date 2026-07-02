<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportePDFService
{
    protected $reporteService;

    public function __construct(ReporteService $reporteService)
    {
        $this->reporteService = $reporteService;
    }

    /**
     * Generar PDF de reporte de citas
     */
    public function generarReporteCitas($filtros)
    {
        $datos = $this->reporteService->obtenerDatosCitas($filtros);
        
        $pdf = Pdf::loadView('reportes.citas-pdf', $datos);
        
        $nombreArchivo = 'reporte_citas_' . now()->format('Y-m-d_His') . '.pdf';
        $rutaArchivo = storage_path('app/public/reportes/' . $nombreArchivo);
        
        // Crear directorio si no existe
        if (!file_exists(storage_path('app/public/reportes'))) {
            mkdir(storage_path('app/public/reportes'), 0755, true);
        }
        
        $pdf->save($rutaArchivo);
        
        return [
            'nombre_archivo' => $nombreArchivo,
            'ruta_completa' => $rutaArchivo,
            'ruta_publica' => 'reportes/' . $nombreArchivo,
        ];
    }

    /**
     * Generar PDF de reporte de ingresos
     */
    public function generarReporteIngresos($filtros)
    {
        $datos = $this->reporteService->obtenerDatosIngresos($filtros);
        
        $pdf = Pdf::loadView('reportes.ingresos-pdf', $datos);
        
        $nombreArchivo = 'reporte_ingresos_' . now()->format('Y-m-d_His') . '.pdf';
        $rutaArchivo = storage_path('app/public/reportes/' . $nombreArchivo);
        
        if (!file_exists(storage_path('app/public/reportes'))) {
            mkdir(storage_path('app/public/reportes'), 0755, true);
        }
        
        $pdf->save($rutaArchivo);
        
        return [
            'nombre_archivo' => $nombreArchivo,
            'ruta_completa' => $rutaArchivo,
            'ruta_publica' => 'reportes/' . $nombreArchivo,
        ];
    }

    /**
     * Generar PDF de reporte de pacientes por médico
     */
    public function generarReportePacientesPorMedico($filtros)
    {
        $datos = $this->reporteService->obtenerDatosPacientesPorMedico($filtros);
        
        $pdf = Pdf::loadView('reportes.pacientes-medico-pdf', $datos);
        
        $nombreArchivo = 'reporte_pacientes_medico_' . now()->format('Y-m-d_His') . '.pdf';
        $rutaArchivo = storage_path('app/public/reportes/' . $nombreArchivo);
        
        if (!file_exists(storage_path('app/public/reportes'))) {
            mkdir(storage_path('app/public/reportes'), 0755, true);
        }
        
        $pdf->save($rutaArchivo);
        
        return [
            'nombre_archivo' => $nombreArchivo,
            'ruta_completa' => $rutaArchivo,
            'ruta_publica' => 'reportes/' . $nombreArchivo,
        ];
    }
}

