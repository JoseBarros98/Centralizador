<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tipo',
        'numero_cuotas',
        'importe_base_cuota',
        'matricula',
        'certificacion',
        'descripcion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'importe_base_cuota' => 'decimal:2',
        'matricula' => 'decimal:2',
        'certificacion' => 'decimal:2',
    ];

    public function quotas()
    {
        return $this->hasMany(ParticipantQuota::class);
    }

    public function getTipoLabelAttribute(): string
    {
        return $this->tipo === 'contado' ? 'Plan Contado' : 'Plan a Crédito';
    }
}
