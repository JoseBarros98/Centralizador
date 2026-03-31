<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MarketingTeam extends Model
{
    protected $fillable = [
        'name',
        'description',
        'active',
        'leader_id'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'marketing_team_members', 'team_id', 'user_id')
                    ->withPivot(['joined_at', 'left_at', 'active'])
                    ->wherePivot('active', true)
                    ->withTimestamps();
    }

    public function allMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'marketing_team_members', 'team_id', 'user_id')
                    ->withPivot(['joined_at', 'left_at', 'active'])
                    ->withTimestamps();
    }

}
