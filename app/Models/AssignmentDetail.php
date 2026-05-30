<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_assignment_id',
        'inscription_id',
        'nombre_completo',
        'plan_pago_nombre',
        'telefono',
        'cuotas_retrasadas_numeros',
        'cuotas_retrasadas_importe',
        'cuotas_retrasadas_cobrado',
        'cuota_vigente_numero',
        'cuota_vigente_importe',
        'cuota_vigente_cobrado',
        'adelanto_numero_cuota',
        'adelanto_importe',
        'matricula_importe',
        'matricula_cobrado',
        'certificacion_importe',
        'certificacion_cobrado',
        'total_asignacion',
        'observaciones',
        'excluido',
        'oculto',
    ];

    protected $casts = [
        'cuotas_retrasadas_numeros' => 'array',
        'cuotas_retrasadas_importe' => 'decimal:2',
        'cuotas_retrasadas_cobrado' => 'decimal:2',
        'cuota_vigente_importe' => 'decimal:2',
        'cuota_vigente_cobrado' => 'decimal:2',
        'adelanto_importe' => 'decimal:2',
        'matricula_importe' => 'decimal:2',
        'matricula_cobrado' => 'decimal:2',
        'certificacion_importe' => 'decimal:2',
        'certificacion_cobrado' => 'decimal:2',
        'total_asignacion' => 'decimal:2',
        'excluido'         => 'boolean',
        'oculto'           => 'boolean',
        'cuota_vigente_numero'  => 'integer',
        'adelanto_numero_cuota' => 'integer',
    ];

    public function monthlyAssignment()
    {
        return $this->belongsTo(MonthlyAssignment::class);
    }

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function getCuotasRetrasadasSaldoAttribute(): float
    {
        return max(0, (float)$this->cuotas_retrasadas_importe - (float)$this->cuotas_retrasadas_cobrado);
    }

    public function getCuotaVigenteSaldoAttribute(): float
    {
        return max(0, (float)$this->cuota_vigente_importe - (float)$this->cuota_vigente_cobrado);
    }

    public function getMatriculaSaldoAttribute(): float
    {
        return max(0, (float)$this->matricula_importe - (float)$this->matricula_cobrado);
    }

    public function getCertificacionSaldoAttribute(): float
    {
        return max(0, (float)$this->certificacion_importe - (float)$this->certificacion_cobrado);
    }

    public function recalcularTotal(): void
    {
        $this->total_asignacion = (float)$this->cuotas_retrasadas_importe
            + (float)$this->cuota_vigente_importe
            + (float)$this->matricula_importe
            + (float)$this->certificacion_importe;
        $this->saveQuietly();
    }

    public function getCuotasRetrasadasNumerosTextoAttribute(): string
    {
        $nums = $this->cuotas_retrasadas_numeros;
        if (empty($nums)) return '—';
        return implode(', ', $nums);
    }
}
