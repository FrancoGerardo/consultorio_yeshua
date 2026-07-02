<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConfiguracionPago;
use App\Models\Servicio;
use Illuminate\Support\Str;

class ConfiguracionPagoSeeder extends Seeder
{
    /**
     * Ejecutar el seeder para configuraciones de pago
     */
    public function run(): void
    {
        // Obtener todos los servicios activos
        $servicios = Servicio::where('estado', true)->get();

        foreach ($servicios as $servicio) {
            // Verificar si ya existe configuración para este servicio
            $existe = ConfiguracionPago::where('servicio_id', $servicio->id)->exists();

            if (!$existe) {
                // Determinar si el servicio califica para plan de cuotas (costo >= 300)
                $costo = $servicio->costo ?? 0;
                $permiteCuotas = $costo >= 300;

                ConfiguracionPago::create([
                    'id' => Str::uuid()->toString(),
                    'servicio_id' => $servicio->id,
                    'porcentaje_anticipo_minimo' => 50, // 50% de anticipo
                    'permite_pago_total' => true,
                    'descuento_pago_total' => 5.00, // 5% de descuento
                    'permite_plan_cuotas' => $permiteCuotas,
                    'monto_minimo_cuotas' => 300.00,
                    'porcentaje_anticipo_cuotas' => 30, // 30% si elige cuotas
                    'max_cuotas' => 12, // Máximo 12 cuotas
                    'intervalo_dias_cuota' => 30, // Cuotas mensuales
                ]);
            }
        }

        $this->command->info('✅ Configuraciones de pago creadas exitosamente.');
    }
}

