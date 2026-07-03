<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ficha extends Model
{
    use HasFactory;

    protected $table = 'fichas';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'cliente_id',
        'servicio_id',
        'medico_id',
        'sala_id',
        'fecha',
        'hora',
        'estado',
        'motivo_consulta',
        'observaciones_internas',
        'fecha_confirmacion',
        'fecha_llegada',
        'fecha_inicio_atencion',
        'fecha_fin_atencion',
        'tiempo_espera_minutos',
        'tiempo_atencion_minutos',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'hora' => 'datetime',
            'fecha_confirmacion' => 'datetime',
            'fecha_llegada' => 'datetime',
            'fecha_inicio_atencion' => 'datetime',
            'fecha_fin_atencion' => 'datetime',
        ];
    }

    /**
     * Scopes para filtrar por estado
     */
    public function scopeEnEspera($query)
    {
        return $query->where('estado', 'EN_ESPERA');
    }

    public function scopeEnAtencion($query)
    {
        return $query->where('estado', 'EN_ATENCION');
    }

    public function scopePendientePago($query)
    {
        return $query->where('estado', 'PENDIENTE_PAGO');
    }

    public function scopeAnticipoSaldo($query)
    {
        return $query->where('estado', 'ANTICIPO_PAGADO');
    }

    public function scopePagadaCompleta($query)
    {
        return $query->where('estado', 'PAGADA_COMPLETA');
    }

    public function scopeDelDia($query, $fecha = null)
    {
        $fecha = $fecha ?? now()->toDateString();
        return $query->whereDate('fecha', $fecha);
    }

    public function scopeDelMedico($query, $medicoId)
    {
        return $query->where('medico_id', $medicoId);
    }

    /**
     * Fichas que ocupan un horario (excluye canceladas y no asistió).
     */
    public function scopeOcupanHorario($query)
    {
        return $query->whereNotIn('estado', ['CANCELADA', 'NO_ASISTIO']);
    }

    /**
     * Estados en los que el cliente puede cancelar la ficha.
     */
    public static function estadosCancelablesPorCliente(): array
    {
        return ['PENDIENTE_PAGO'];
    }

    public function puedeCancelarsePorCliente(): bool
    {
        return in_array($this->estado, self::estadosCancelablesPorCliente(), true)
            && ! $this->pagos()->where('estado', 'PAGADO')->exists()
            && ! $this->estaEsperandoConfirmacionPago();
    }

    /**
     * Hay un pago iniciado (p. ej. QR) aún sin confirmar.
     */
    public function estaEsperandoConfirmacionPago(): bool
    {
        return $this->pagos()->where('estado', 'PENDIENTE')->exists();
    }

    /**
     * Métodos de control de flujo
     */
    public function marcarLlegada()
    {
        $this->update([
            'estado' => 'EN_ESPERA',
            'fecha_llegada' => now(),
        ]);
    }

    public function iniciarAtencion()
    {
        $tiempoEspera = $this->fecha_llegada 
            ? now()->diffInMinutes($this->fecha_llegada) 
            : null;

        $this->update([
            'estado' => 'EN_ATENCION',
            'fecha_inicio_atencion' => now(),
            'tiempo_espera_minutos' => $tiempoEspera,
        ]);
    }

    public function finalizarAtencion()
    {
        $tiempoAtencion = $this->fecha_inicio_atencion 
            ? now()->diffInMinutes($this->fecha_inicio_atencion) 
            : null;

        $this->update([
            'estado' => 'ATENDIDA',
            'fecha_fin_atencion' => now(),
            'tiempo_atencion_minutos' => $tiempoAtencion,
        ]);
    }

    /**
     * Métodos de control de pagos
     */
    public function calcularTotalPagado(): float
    {
        return round((float) $this->pagos()->where('estado', 'PAGADO')->sum('monto'), 2);
    }

    /**
     * Monto total acordado para la ficha (catálogo o total con descuento).
     */
    public function obtenerCostoNetoAcordado(): float
    {
        $costoCatalogo = round((float) ($this->servicio->costo ?? 0), 2);

        $pagoTotalPagado = (float) $this->pagos()
            ->where('estado', 'PAGADO')
            ->where('concepto', 'TOTAL')
            ->sum('monto');

        if ($pagoTotalPagado > 0) {
            return round($pagoTotalPagado, 2);
        }

        $pagoTotalPendiente = $this->pagos()
            ->where('estado', 'PENDIENTE')
            ->where('concepto', 'TOTAL')
            ->orderByDesc('created_at')
            ->first();

        if ($pagoTotalPendiente) {
            return round((float) $pagoTotalPendiente->monto, 2);
        }

        return $costoCatalogo;
    }

    public function calcularSaldoPendiente(): float
    {
        $totalPagado = $this->calcularTotalPagado();
        $costoNeto = $this->obtenerCostoNetoAcordado();

        return max(0, round($costoNeto - $totalPagado, 2));
    }

    public function calcularPorcentajePagado(): float
    {
        $costoNeto = $this->obtenerCostoNetoAcordado();
        if ($costoNeto <= 0) {
            return 0;
        }

        $totalPagado = $this->calcularTotalPagado();

        return min(100, round(($totalPagado / $costoNeto) * 100, 2));
    }

    public function tienePagoAnticipo(): bool
    {
        return $this->pagos()->where('concepto', 'ANTICIPO')->where('estado', 'PAGADO')->exists();
    }

    public function estaPagadaCompleta(): bool
    {
        return $this->calcularSaldoPendiente() <= 0.01;
    }

    /**
     * Estados que puede recalcular la lógica de pagos.
     */
    public static function estadosGestionadosPorPago(): array
    {
        return ['PENDIENTE_PAGO', 'ANTICIPO_PAGADO', 'PAGADA_COMPLETA'];
    }

    public function actualizarEstadoPorPago(): void
    {
        if (! in_array($this->estado, self::estadosGestionadosPorPago(), true)) {
            return;
        }

        $totalPagado = $this->calcularTotalPagado();

        if ($totalPagado <= 0) {
            $this->update(['estado' => 'PENDIENTE_PAGO']);
            return;
        }

        if ($this->pagos()->where('estado', 'PAGADO')->where('concepto', 'TOTAL')->exists()) {
            $this->update(['estado' => 'PAGADA_COMPLETA']);
            return;
        }

        if ($this->calcularSaldoPendiente() <= 0.01) {
            $this->update(['estado' => 'PAGADA_COMPLETA']);
            return;
        }

        $this->update(['estado' => 'ANTICIPO_PAGADO']);
    }

    /**
     * Relación con Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'usuario_id');
    }

    /**
     * Relación con Servicio
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id', 'id');
    }

    /**
     * Relación con Médico
     */
    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id', 'usuario_id');
    }

    /**
     * Relación con Sala
     */
    public function sala()
    {
        return $this->belongsTo(Sala::class, 'sala_id', 'id');
    }

    /**
     * Relación con Seguimientos
     */
    public function seguimientos()
    {
        return $this->hasMany(Seguimiento::class, 'ficha_id', 'id');
    }

    /**
     * Relación con Planes de Cuota
     */
    public function planesCuota()
    {
        return $this->hasMany(PlanCuota::class, 'ficha_id', 'id');
    }

    /**
     * Relación con Pagos
     */
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'ficha_id', 'id');
    }
}

