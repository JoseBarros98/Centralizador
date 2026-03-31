<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ArtRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requester_id',
        'request_date',
        'delivery_date',
        'designer_id',
        'content_pillar_id',
        'type_of_art_id',
        'description',
        'title',
        'content',
        'details',
        'status',
        'priority',
        'observations',
        'active',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'request_date' => 'date',
        'delivery_date' => 'date',
        'active' => 'boolean',
    ];

    // Relaciones
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function designer()
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function contentPillar()
    {
        return $this->belongsTo(ContentPillar::class);
    }

    public function typeOfArt()
    {
        return $this->belongsTo(TypeOfArt::class);
    }

    public function files()
    {
        return $this->hasMany(ArtRequestFile::class);
    }

    public function modifications()
    {
        return $this->hasMany(ArtRequestModification::class)->orderBy('created_at', 'DESC');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Métodos auxiliares
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'COMPLETO' => 'green',
            'NO INICIADO' => 'gray',
            'EN CURSO' => 'blue',
            'RETRASADO' => 'red',
            'ESPERANDO APROBACION' => 'yellow',
            'ESPERANDO INFORMACION' => 'orange',
            'CANCELADO' => 'red',
            'EN PAUSA' => 'purple',
            default => 'gray'
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'ALTA' => 'red',
            'MEDIA' => 'yellow',
            'BAJA' => 'green',
            default => 'gray'
        };
    }

    public function isOverdue()
    {
        if (!$this->delivery_date || $this->status === 'COMPLETO') {
            return false;
        }

        // Una fecha de entrega sin hora debe vencer al final del día (23:59:59)
        return $this->delivery_date->copy()->endOfDay()->lt(now());
    }

    /**
     * Registrar una modificación en el arte
     */
    public function recordModification($type, $description, $oldValue = null, $newValue = null, $details = null)
    {
        return $this->modifications()->create([
            'modification_type' => $type,
            'description' => $description,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'details' => $details,
            'created_by' => Auth::id() ?? $this->updated_by,
        ]);
    }

    /**
     * Contar modificaciones de un tipo específico
     */
    public function countModificationsByType($type)
    {
        return $this->modifications()->where('modification_type', $type)->count();
    }

    /**
     * Obtener todas las modificaciones agrupadas por tipo
     */
    public function getModificationsSummary()
    {
        $types = ['COLOR', 'TAMAÑO', 'TEXTO', 'CONTENIDO', 'POSICIÓN', 'ESTILO', 'FUENTE', 'IMAGEN', 'OTRO'];
        $summary = [];
        
        foreach ($types as $type) {
            $count = $this->countModificationsByType($type);
            if ($count > 0) {
                $summary[$type] = $count;
            }
        }
        
        return $summary;
    }

    /**
     * Obtener el total de modificaciones
     */
    public function getTotalModifications()
    {
        return $this->modifications()->count();
    }
}
