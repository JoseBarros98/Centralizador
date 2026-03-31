<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
    ];

    public function contentPillarFiles(): HasMany
    {
        return $this->hasMany(ContentPillarFile::class);
    }

    // Relaciones con el módulo de metas de marketing
    public function leadTeams(): HasMany
    {
        return $this->hasMany(MarketingTeam::class, 'leader_id');
    }

    public function leadsActiveMarketingTeam(): bool
    {
        return $this->leadTeams()->where('active', true)->exists();
    }

    public function marketingTeamMemberships(): HasMany
    {
        return $this->hasMany(MarketingTeamMember::class);
    }

    public function marketingTeams(): BelongsToMany
    {
        return $this->belongsToMany(MarketingTeam::class, 'marketing_team_members', 'user_id', 'team_id')
                   ->withPivot(['active', 'joined_at', 'left_at'])
                   ->withTimestamps();
    }

    public function updatedInscriptions()
    {
        return $this->hasMany(Inscription::class, 'updated_by');
    }
    
    public function advisedInscriptions()
    {
        return $this->hasMany(Inscription::class, 'user_advisor_id');
    }
    
    // Scope para filtrar usuarios activos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Relación polimórfica para notificaciones
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /**
     * Obtener notificaciones no leídas
     */
    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }

    /**
     * Obtener el conteo de notificaciones no leídas
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin()
    {
        return $this->hasRole(['admin', 'administrador', 'Administrator']);
    }
}
