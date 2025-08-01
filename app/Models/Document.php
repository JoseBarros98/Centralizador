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
        'description',
        'created_by',
        'document_type',
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
