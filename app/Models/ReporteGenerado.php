<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReporteGenerado extends Model
{
    use HasFactory;

    protected $table = 'reportes_generados';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'nombre',
        'tipo',
        'filtros',
        'formato',
        'archivo_path',
        'estado',
        'usuario_id',
        'fecha_generacion',
    ];

    protected function casts(): array
    {
        return [
            'filtros' => 'array',
            'fecha_generacion' => 'datetime',
        ];
    }

    /**
     * Generar UUID automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }
}

