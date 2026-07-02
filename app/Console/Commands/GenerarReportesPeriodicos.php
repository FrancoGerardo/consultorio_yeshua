<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\GenerarReporteAutomaticoJob;
use App\Models\User;
use Carbon\Carbon;

class GenerarReportesPeriodicos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reportes:periodicos 
                            {tipo=diario : Tipo de reporte (diario|semanal|mensual)}
                            {--email : Enviar reporte por email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera reportes periódicos automáticos (diario, semanal, mensual)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tipo = $this->argument('tipo');
        $enviarEmail = $this->option('email');

        $this->info("🚀 Iniciando generación de reportes {$tipo}...");

        // Determinar rango de fechas según tipo
        $filtros = $this->obtenerFiltrosPorTipo($tipo);

        if (!$filtros) {
            $this->error('❌ Tipo de reporte no válido. Use: diario, semanal o mensual');
            return 1;
        }

        // Obtener administradores para enviar reportes
        $administradores = User::permission('gestionar-reportes')->get();

        if ($administradores->isEmpty()) {
            $this->error('❌ No se encontraron usuarios con permisos para recibir reportes');
            return 1;
        }

        $this->info("📧 Se generarán reportes para {$administradores->count()} usuario(s)");

        // Tipos de reportes a generar
        $tiposReportes = ['citas', 'ingresos', 'pacientes_medico'];
        $formatos = ['pdf', 'excel'];

        $totalJobs = 0;

        foreach ($administradores as $admin) {
            foreach ($tiposReportes as $tipoReporte) {
                foreach ($formatos as $formato) {
                    GenerarReporteAutomaticoJob::dispatch(
                        $tipoReporte,
                        $formato,
                        $filtros,
                        $admin->id,
                        $enviarEmail
                    );
                    
                    $totalJobs++;
                }
            }
        }

        $this->info("✅ Se han programado {$totalJobs} reportes en la cola");
        
        if ($enviarEmail) {
            $this->info("📧 Los reportes serán enviados por email cuando estén listos");
        } else {
            $this->info("💾 Los reportes estarán disponibles en el sistema");
        }

        return 0;
    }

    /**
     * Obtener filtros según tipo de reporte
     */
    private function obtenerFiltrosPorTipo($tipo)
    {
        $hoy = Carbon::today();

        return match($tipo) {
            'diario' => [
                'fecha_inicio' => $hoy->format('Y-m-d'),
                'fecha_fin' => $hoy->format('Y-m-d'),
            ],
            'semanal' => [
                'fecha_inicio' => $hoy->startOfWeek()->format('Y-m-d'),
                'fecha_fin' => $hoy->endOfWeek()->format('Y-m-d'),
            ],
            'mensual' => [
                'fecha_inicio' => $hoy->startOfMonth()->format('Y-m-d'),
                'fecha_fin' => $hoy->endOfMonth()->format('Y-m-d'),
            ],
            default => null,
        };
    }
}

