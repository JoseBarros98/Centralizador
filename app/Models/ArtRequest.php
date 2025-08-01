<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
            'ESPERANDO APROBACIÓN' => 'yellow',
            'ESPERANDO INFORMACIÓN' => 'orange',
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
        return $this->delivery_date < now() && $this->status !== 'COMPLETO';
    }
}
