<?php

namespace App\Services;

use App\Exports\CitasExport;
use App\Exports\IngresosExport;
use App\Exports\PacientesMedicoExport;
use Maatwebsite\Excel\Facades\Excel;

class ReporteExcelService
{
    protected $reporteService;

    public function __construct(ReporteService $reporteService)
    {
        $this->reporteService = $reporteService;
    }

    /**
     * Generar Excel de reporte de citas
     */
    public function generarReporteCitas($filtros)
    {
        $datos = $this->reporteService->obtenerDatosCitas($filtros);
        
        $nombreArchivo = 'reporte_citas_' . now()->format('Y-m-d_His') . '.xlsx';
        $rutaArchivo = 'reportes/' . $nombreArchivo;
        
        // Crear directorio si no existe
        if (!file_exists(storage_path('app/public/reportes'))) {
            mkdir(storage_path('app/public/reportes'), 0755, true);
        }
        
        Excel::store(
            new CitasExport($datos['fichas'], $datos['estadisticas']),
            $rutaArchivo,
            'public'
        );
        
        return [
            'nombre_archivo' => $nombreArchivo,
            'ruta_completa' => storage_path('app/public/' . $rutaArchivo),
            'ruta_publica' => $rutaArchivo,
        ];
    }

    /**
     * Generar Excel de reporte de ingresos
     */
    public function generarReporteIngresos($filtros)
    {
        $datos = $this->reporteService->obtenerDatosIngresos($filtros);
        
        $nombreArchivo = 'reporte_ingresos_' . now()->format('Y-m-d_His') . '.xlsx';
        $rutaArchivo = 'reportes/' . $nombreArchivo;
        
        if (!file_exists(storage_path('app/public/reportes'))) {
            mkdir(storage_path('app/public/reportes'), 0755, true);
        }
        
        Excel::store(
            new IngresosExport($datos['pagos'], $datos['estadisticas']),
            $rutaArchivo,
            'public'
        );
        
        return [
            'nombre_archivo' => $nombreArchivo,
            'ruta_completa' => storage_path('app/public/' . $rutaArchivo),
            'ruta_publica' => $rutaArchivo,
        ];
    }

    /**
     * Generar Excel de reporte de pacientes por médico
     */
    public function generarReportePacientesPorMedico($filtros)
    {
        $datos = $this->reporteService->obtenerDatosPacientesPorMedico($filtros);
        
        $nombreArchivo = 'reporte_pacientes_medico_' . now()->format('Y-m-d_His') . '.xlsx';
        $rutaArchivo = 'reportes/' . $nombreArchivo;
        
        if (!file_exists(storage_path('app/public/reportes'))) {
            mkdir(storage_path('app/public/reportes'), 0755, true);
        }
        
        Excel::store(
            new PacientesMedicoExport($datos['medicos'], $datos['estadisticas']),
            $rutaArchivo,
            'public'
        );
        
        return [
            'nombre_archivo' => $nombreArchivo,
            'ruta_completa' => storage_path('app/public/' . $rutaArchivo),
            'ruta_publica' => $rutaArchivo,
        ];
    }
}

