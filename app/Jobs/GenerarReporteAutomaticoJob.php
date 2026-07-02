<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\ReportePDFService;
use App\Services\ReporteExcelService;
use App\Models\ReporteGenerado;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReporteGeneradoMail;
use Illuminate\Support\Facades\Log;

class GenerarReporteAutomaticoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tipoReporte;
    protected $formato;
    protected $filtros;
    protected $usuarioId;
    protected $enviarEmail;

    /**
     * Create a new job instance.
     */
    public function __construct($tipoReporte, $formato, $filtros, $usuarioId, $enviarEmail = false)
    {
        $this->tipoReporte = $tipoReporte;
        $this->formato = $formato;
        $this->filtros = $filtros;
        $this->usuarioId = $usuarioId;
        $this->enviarEmail = $enviarEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(ReportePDFService $pdfService, ReporteExcelService $excelService): void
    {
        try {
            Log::info("Iniciando generación de reporte automático: {$this->tipoReporte} - {$this->formato}");

            // Seleccionar servicio según formato
            $service = $this->formato === 'pdf' ? $pdfService : $excelService;

            // Generar reporte según tipo
            $resultado = match($this->tipoReporte) {
                'citas' => $this->formato === 'pdf' 
                    ? $service->generarReporteCitas($this->filtros)
                    : $service->generarReporteCitas($this->filtros),
                'ingresos' => $this->formato === 'pdf'
                    ? $service->generarReporteIngresos($this->filtros)
                    : $service->generarReporteIngresos($this->filtros),
                'pacientes_medico' => $this->formato === 'pdf'
                    ? $service->generarReportePacientesPorMedico($this->filtros)
                    : $service->generarReportePacientesPorMedico($this->filtros),
            };

            // Nombre del reporte
            $nombreReporte = match($this->tipoReporte) {
                'citas' => 'Reporte de Citas',
                'ingresos' => 'Reporte de Ingresos',
                'pacientes_medico' => 'Reporte de Pacientes por Médico',
            };

            // Guardar registro del reporte
            $reporteGenerado = ReporteGenerado::create([
                'nombre' => $nombreReporte . ' (Automático)',
                'tipo' => $this->tipoReporte,
                'filtros' => $this->filtros,
                'formato' => $this->formato,
                'archivo_path' => $resultado['ruta_publica'],
                'estado' => 'listo',
                'usuario_id' => $this->usuarioId,
            ]);

            Log::info("Reporte generado exitosamente: {$reporteGenerado->id}");

            // Enviar por email si se solicitó
            if ($this->enviarEmail) {
                $usuario = User::find($this->usuarioId);
                
                if ($usuario && $usuario->email) {
                    Mail::to($usuario->email)->send(
                        new ReporteGeneradoMail($reporteGenerado, $resultado['ruta_completa'])
                    );
                    
                    Log::info("Email enviado exitosamente a: {$usuario->email}");
                } else {
                    Log::warning("No se pudo enviar email - Usuario sin email: {$this->usuarioId}");
                }
            }

        } catch (\Exception $e) {
            Log::error("Error al generar reporte automático: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            // Registrar fallo
            ReporteGenerado::create([
                'nombre' => 'Reporte Fallido',
                'tipo' => $this->tipoReporte,
                'filtros' => $this->filtros,
                'formato' => $this->formato,
                'estado' => 'error',
                'usuario_id' => $this->usuarioId,
            ]);
            
            throw $e;
        }
    }
}

