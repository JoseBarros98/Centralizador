<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgramAllocation extends Model
{
    use HasFactory;

    protected $table = 'program_allocations';

    protected $fillable = [
        'user_id',
        'payment_request_id',
        'program_id',
        'mes',
        'module_id',
        'gestion',
        'categoria',
        'etapa',
        'cobro_titulacion',
        'asignacion_programa',
        'responsable_cartera',
        'fecha_pago',
        'monto_al_5',
        'porcentaje_al_5',
        'monto_al_10',
        'porcentaje_al_10',
        'monto_al_15',
        'porcentaje_al_15',
        'monto_al_20',
        'porcentaje_al_20',
        'monto_al_25',
        'porcentaje_al_25',
        'monto_al_30',
        'porcentaje_al_30',
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'cobro_titulacion' => 'decimal:2',
        'asignacion_programa' => 'decimal:2',
        'monto_al_5' => 'decimal:2',
        'porcentaje_al_5' => 'decimal:2',
        'monto_al_10' => 'decimal:2',
        'porcentaje_al_10' => 'decimal:2',
        'monto_al_15' => 'decimal:2',
        'porcentaje_al_15' => 'decimal:2',
        'monto_al_20' => 'decimal:2',
        'porcentaje_al_20' => 'decimal:2',
        'monto_al_25' => 'decimal:2',
        'porcentaje_al_25' => 'decimal:2',
        'monto_al_30' => 'decimal:2',
        'porcentaje_al_30' => 'decimal:2',
    ];

    /**
     * Relación con Program
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Relación con Module
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Relación con PaymentRequest
     */
    public function paymentRequest()
    {
        return $this->belongsTo(PaymentRequest::class);
    }

    /**
     * Relación con User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
