<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Obtener el modelo al que pertenece la notificación.
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Marcar la notificación como leída.
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Verificar si la notificación ha sido leída.
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Verificar si la notificación no ha sido leída.
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Scope para obtener solo notificaciones no leídas.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope para obtener solo notificaciones leídas.
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
}
