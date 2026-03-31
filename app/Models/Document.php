<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inscription_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'description',
        'document_type',
        'google_drive_id',
        'google_drive_link',
        'stored_in_drive',
        'created_by',
    ];

    protected $casts = [
        'stored_in_drive' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Obtener la inscripción asociada al documento.
     */
    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    /**
     * Obtener el usuario que creó el documento.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
