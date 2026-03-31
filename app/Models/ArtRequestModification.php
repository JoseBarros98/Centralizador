<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtRequestModification extends Model
{
    use HasFactory;

    protected $fillable = [
        'art_request_id',
        'modification_type',
        'description',
        'old_value',
        'new_value',
        'details',
        'created_by',
        'is_completed',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'old_value' => 'json',
        'new_value' => 'json',
        'details' => 'json',
        'is_completed' => 'boolean',
    ];

    // Relaciones
    public function artRequest()
    {
        return $this->belongsTo(ArtRequest::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('modification_type', $type);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'DESC');
    }

    // Métodos auxiliares
    public function getTypeColorAttribute()
    {
        return match($this->modification_type) {
            'COLOR' => 'blue',
            'TAMAÑO' => 'purple',
            'TEXTO' => 'green',
            'CONTENIDO' => 'orange',
            'POSICIÓN' => 'cyan',
            'ESTILO' => 'pink',
            'FUENTE' => 'indigo',
            'IMAGEN' => 'red',
            'OTRO' => 'gray',
            default => 'gray'
        };
    }

    public function getTypeIconAttribute()
    {
        return match($this->modification_type) {
            'COLOR' => '🎨',
            'TAMAÑO' => '📏',
            'TEXTO' => '✍️',
            'CONTENIDO' => '📝',
            'POSICIÓN' => '↔️',
            'ESTILO' => '🎭',
            'FUENTE' => '🔤',
            'IMAGEN' => '🖼️',
            'OTRO' => '⚙️',
            default => '•'
        };
    }
}
