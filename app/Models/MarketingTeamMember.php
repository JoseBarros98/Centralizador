<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MarketingTeamMember extends Model
{
    protected $fillable = [
        'team_id',
        'user_id',
        'active',
        'joined_at',
        'left_at'
    ];

    protected $casts = [
        'active' => 'boolean',
        'joined_at' => 'datetime',
        'left_at' => 'datetime'
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(MarketingTeam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Verificar si el miembro está activo en el equipo
    public function isActive(): bool
    {
        return $this->active && !$this->left_at;
    }

    // Calcular días en el equipo
    public function getDaysInTeam(): int
    {
        $endDate = $this->left_at ?? now();
        return $this->joined_at->diffInDays($endDate);
    }

    //Mostrar las inscripciones del miembro en el equipo
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class, 'user_id', 'user_id')
                    ->where('team_id', $this->team_id);
    }
}
