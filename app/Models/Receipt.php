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
        'file_name',
        'file_type', 
        'file_size',
        'google_drive_id',
        'google_drive_link',
        'stored_in_drive',
        'created_by'
    ];

    protected $casts = [
        'stored_in_drive' => 'boolean',
        'file_size' => 'integer',
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
