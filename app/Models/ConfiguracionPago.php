<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionPago extends Model
{
    use HasFactory;

    protected $table = 'configuracion_pagos';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'servicio_id',
        'porcentaje_anticipo_minimo',
        'permite_pago_total',
        'descuento_pago_total',
        'permite_plan_cuotas',
        'monto_minimo_cuotas',
        'porcentaje_anticipo_cuotas',
        'max_cuotas',
        'intervalo_dias_cuota',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje_anticipo_minimo' => 'integer',
            'permite_pago_total' => 'boolean',
            'descuento_pago_total' => 'decimal:2',
            'permite_plan_cuotas' => 'boolean',
            'monto_minimo_cuotas' => 'decimal:2',
            'porcentaje_anticipo_cuotas' => 'integer',
            'max_cuotas' => 'integer',
            'intervalo_dias_cuota' => 'integer',
        ];
    }

    /**
     * Relación con Servicio
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id', 'id');
    }

    /**
     * Calcular monto de anticipo para un costo dado
     */
    public function calcularMontoAnticipo(float $costoTotal, bool $usarPlanCuotas = false): float
    {
        $porcentaje = $usarPlanCuotas 
            ? $this->porcentaje_anticipo_cuotas 
            : $this->porcentaje_anticipo_minimo;
        
        return round(($costoTotal * $porcentaje) / 100, 2);
    }

    /**
     * Calcular descuento por pago total
     */
    public function calcularDescuentoPagoTotal(float $costoTotal): float
    {
        if (!$this->permite_pago_total || $this->descuento_pago_total <= 0) {
            return 0;
        }

        return round(($costoTotal * $this->descuento_pago_total) / 100, 2);
    }

    /**
     * Verificar si el servicio califica para plan de cuotas
     */
    public function calificaParaCuotas(float $costoTotal): bool
    {
        if (!$this->permite_plan_cuotas) {
            return false;
        }

        if ($this->monto_minimo_cuotas && $costoTotal < $this->monto_minimo_cuotas) {
            return false;
        }

        return true;
    }

    /**
     * Obtener configuración por defecto (si no existe configuración específica)
     */
    public static function obtenerConfiguracionPorDefecto(): array
    {
        return [
            'porcentaje_anticipo_minimo' => 50,
            'permite_pago_total' => true,
            'descuento_pago_total' => 5.00,
            'permite_plan_cuotas' => false,
            'monto_minimo_cuotas' => 300.00,
            'porcentaje_anticipo_cuotas' => 30,
            'max_cuotas' => 12,
            'intervalo_dias_cuota' => 30,
        ];
    }

    /**
     * Obtener o crear configuración para un servicio
     */
    public static function obtenerOCrearParaServicio(string $servicioId): ConfiguracionPago
    {
        $config = self::where('servicio_id', $servicioId)->first();

        if (!$config) {
            $config = self::create([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'servicio_id' => $servicioId,
                ...self::obtenerConfiguracionPorDefecto()
            ]);
        }

        return $config;
    }
}

