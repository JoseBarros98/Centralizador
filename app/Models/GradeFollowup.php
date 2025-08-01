<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'observations',
        'has_recovery',
        'recovery_start_date',
        'recovery_end_date',
    ];

    protected $casts = [
        'has_recovery' => 'boolean',
        'recovery_start_date' => 'date',
        'recovery_end_date' => 'date',
    ];

    /**
     * Obtener la calificación asociada a este seguimiento.
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * Obtener todos los contactos asociados a este seguimiento.
     */
    public function contacts()
    {
        return $this->hasMany(FollowupContact::class, 'grade_followups_id');
    }

    /**
     * Obtener las llamadas asociadas a este seguimiento.
     */
    public function calls()
    {
        return $this->hasMany(FollowupContact::class, 'grade_followups_id')
            ->where('type', 'call');
    }

    /**
     * Obtener los mensajes asociados a este seguimiento.
     */
    public function messages()
    {
        return $this->hasMany(FollowupContact::class, 'grade_followups_id')
            ->where('type', 'message');
    }
}
