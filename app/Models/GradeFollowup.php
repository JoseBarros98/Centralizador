<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'status',
        'creator_id',
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
     * Obtener el usuario creador del seguimiento.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
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

    /**
     * Verificar si el seguimiento está cerrado.
     */
    public function isClosed()
    {
        return $this->status === 'closed';
    }

    /**
     * Verificar si el seguimiento está abierto.
     */
    public function isOpen()
    {
        return $this->status === 'open';
    }

    /**
     * Cerrar el seguimiento.
     */
    public function close()
    {
        $this->status = 'closed';
        return $this->save();
    }

    /**
     * Abrir el seguimiento.
     */
    public function open()
    {
        $this->status = 'open';
        return $this->save();
    }
}
