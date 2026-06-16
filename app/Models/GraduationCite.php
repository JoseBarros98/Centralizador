<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GraduationCite extends Model
{
    use HasFactory;

    protected $fillable = [
        'cite_number',
        'cite_date',
        'payment_type',
        'payment_status',
        'amount_per_participant',
        'total_amount',
        'observations',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'cite_date' => 'date',
        'amount_per_participant' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function participants()
    {
        return $this->belongsToMany(Inscription::class, 'graduation_cite_inscription')
            ->withPivot(['participant_full_name', 'participant_ci', 'participant_program', 'amount'])
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'pendiente' => 'Pendiente',
            'pagado'    => 'Pagado',
            'cancelado' => 'Cancelado',
            default     => ucfirst((string) $this->payment_status),
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'pagado'    => 'bg-green-100 text-green-800',
            'cancelado' => 'bg-red-100 text-red-800',
            default     => 'bg-yellow-100 text-yellow-800',
        };
    }

    public function getPaymentTypeLabelAttribute()
    {
        return match ($this->payment_type) {
            'inscripcion' => 'Inscripción',
            'matricula' => 'Matrícula',
            'colegiatura' => 'Colegiatura',
            'certificacion' => 'Certificación',
            default => ucfirst((string) $this->payment_type),
        };
    }
}