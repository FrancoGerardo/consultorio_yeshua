<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HistorialClinico extends Model
{
    use HasFactory;

    protected $table = 'historiales_clinicos';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'cliente_id',
        'grupo_sanguineo',
        'factor_rh',
        'alergias',
        'enfermedades_cronicas',
        'antecedentes_quirurgicos',
        'antecedentes_familiares',
        'antecedentes_personales',
        'peso_habitual',
        'estatura',
        'habitos',
        'vacunas',
        'transfusiones_previas',
        'hospitalizaciones_previas',
        'notas_importantes',
        'medicamentos_habituales',
    ];

    protected function casts(): array
    {
        return [
            'habitos' => 'array',
            'vacunas' => 'array',
            'peso_habitual' => 'decimal:2',
            'estatura' => 'decimal:2',
        ];
    }

    /**
     * Relación con Cliente
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'usuario_id');
    }

    /**
     * Garantiza un expediente clínico permanente por paciente (1:1 con cliente).
     */
    public static function asegurarParaCliente(string $clienteId): self
    {
        return self::firstOrCreate(
            ['cliente_id' => $clienteId],
            ['id' => Str::uuid()->toString()]
        );
    }

    /**
     * Crea expedientes vacíos para clientes existentes sin historial (migración suave).
     */
    public static function sincronizarHistorialesFaltantes(): int
    {
        $creados = 0;

        Cliente::whereDoesntHave('historialClinico')
            ->pluck('usuario_id')
            ->each(function (string $clienteId) use (&$creados) {
                self::asegurarParaCliente($clienteId);
                $creados++;
            });

        return $creados;
    }
}

