<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowupContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_followups_id',
        'type',
        'contact_date',
        'got_response',
        'response_date',
        'notes',
    ];

    protected $casts = [
        'contact_date' => 'date',
        'response_date' => 'date',
        'got_response' => 'boolean',
    ];

    /**
     * Obtener el seguimiento asociado a este contacto.
     */
    public function followup()
    {
        return $this->belongsTo(GradeFollowup::class, 'grade_followups_id');
    }
    
    /**
     * Verificar si es una llamada.
     */
    public function isCall()
    {
        return $this->type === 'call';
    }
    
    /**
     * Verificar si es un mensaje.
     */
    public function isMessage()
    {
        return $this->type === 'message';
    }
}
