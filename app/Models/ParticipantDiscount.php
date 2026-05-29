<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParticipantDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'inscription_id',
        'program_id',
        'tipo',
        'valor',
        'descripcion',
        'activo',
        'created_by',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'valor' => 'decimal:2',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calcularDescuento(float $importeBase): float
    {
        if ($this->tipo === 'porcentaje') {
            return round($importeBase * ($this->valor / 100), 2);
        }
        return (float) $this->valor;
    }
}
