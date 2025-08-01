<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        //'participant_id',
        'inscription_id',
        'name',
        'last_name',
        'grade',
    ];

    /**
     * Obtener el módulo asociado a esta calificación.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // /**
    //  * Obtener el participante asociado a esta calificación.
    //  */
    // public function participant()
    // {
    //     return $this->belongsTo(Participant::class);
    // }

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    /**
     * Verificar si la calificación es aprobatoria.
     */
    public function getApprovedAttribute()
    {
        return $this->grade >= 71;
    }

    /**
     * Obtener la clase de color según la calificación.
     */
    public function getColorClass()
    {
        if ($this->grade >= 71) {
            return 'bg-green-100 text-green-800';
        } else {
            return 'bg-red-100 text-red-800';
        }
    }

    /**
     * Obtener el seguimiento asociado a esta calificación.
     */
    public function followup()
    {
        return $this->hasOne(GradeFollowup::class);
    }
}
