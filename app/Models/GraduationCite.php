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
            ->withPivot(['participant_full_name', 'participant_ci', 'participant_program'])
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