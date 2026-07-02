<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seguimiento extends Model
{
    use HasFactory;

    protected $table = 'seguimientos';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'ficha_id',
        'medico_id',
        'tipo',
        'estado',
        'fecha',
        'signos_vitales',
        'motivo_consulta',
        'nivel_urgencia',
        'diagnostico',
        'codigo_cie10',
        'observaciones',
        'tratamiento_prescrito',
        'medicamentos',
        'examenes_solicitados',
        'interconsultas',
        'proxima_cita',
        'indicaciones_proxima_cita',
        'firma_digital',
        'fecha_firma',
        'ip_registro',
        'navegador',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'fecha_firma' => 'datetime',
            'proxima_cita' => 'date',
            'signos_vitales' => 'array',
            'medicamentos' => 'array',
            'examenes_solicitados' => 'array',
            'interconsultas' => 'array',
        ];
    }

    /**
     * Relación con Médico
     */
    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id', 'usuario_id');
    }

    /**
     * Relación con Ficha
     */
    public function ficha()
    {
        return $this->belongsTo(Ficha::class, 'ficha_id', 'id');
    }
}

