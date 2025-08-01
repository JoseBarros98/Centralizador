<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'name',
        'description',
        'teacher_id',
        'monitor_id',
        'class_count',
        'active',
        'recovery_start_date',
        'recovery_end_date',
        'recovery_notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'recovery_start_date' => 'date',
        'recovery_end_date' => 'date',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'monitor_id');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(ModuleClass::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Determina si un estudiante necesita recuperatorio basado en su calificación.
     *
     * @param float $grade La calificación del estudiante
     * @return bool
     */
    public function needsRecovery(float $grade): bool
    {
        return $grade < 71;
    }

    /**
     * Verifica si el módulo tiene un recuperatorio programado.
     *
     * @return bool
     */
    public function hasRecoveryScheduled(): bool
    {
        return $this->recovery_start_date !== null;
    }

    /**
     * Verifica si el recuperatorio está actualmente en progreso.
     *
     * @return bool
     */
    public function isRecoveryInProgress(): bool
    {
        if (!$this->hasRecoveryScheduled()) {
            return false;
        }

        $today = now()->startOfDay();
        return $today->between($this->recovery_start_date, $this->recovery_end_date);
    }

    /**
     * Obtiene todos los participantes que han asistido a alguna clase de este módulo.
     */
    public function getParticipantsWithAttendance()
    {
        return \App\Models\Participant::whereHas('attendances', function ($query) {
            $query->whereHas('moduleClass', function ($query) {
                $query->where('module_id', $this->id);
            });
        })->get();
    }
}
