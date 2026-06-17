<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParticipantQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'inscription_id',
        'program_id',
        'payment_plan_id',
        'numero_cuota',
        'importe_base',
        'descuento_aplicado',
        'importe_final',
        'fecha_vencimiento',
        'pagada',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'importe_base' => 'decimal:2',
        'descuento_aplicado' => 'decimal:2',
        'importe_final' => 'decimal:2',
        'pagada' => 'boolean',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }
}
