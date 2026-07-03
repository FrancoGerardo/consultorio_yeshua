<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    use HasFactory;

    protected $table = 'salas';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'numero',
        'categoria',
        'equipamiento',
        'estado',
        'capacidad',
    ];

    public function fichas()
    {
        return $this->hasMany(Ficha::class, 'sala_id', 'id');
    }
}
