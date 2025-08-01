<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'inscription_id',
        'file_path',
        'created_by'
    ];

    /**
     * Obtener la inscripción asociada al recibo.
     */
    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    /**
     * Obtener el usuario que creó el recibo.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
