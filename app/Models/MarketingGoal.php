<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingGoal extends Model
{
    protected $fillable = [
        'user_id',
        'team_id',
        'month',
        'year',
        'monthly_goal',
        'debt_from_previous',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(MarketingTeam::class);
    }

    public function weeks(): HasMany
    {
        return $this->hasMany(MarketingGoalWeek::class, 'goal_id')->orderBy('week_number');
    }

    public function metaTotal(): int
    {
        return $this->monthly_goal + $this->debt_from_previous;
    }

    public function nationalTotal(): int
    {
        return $this->weeks->sum(fn($w) => $w->entries->sum(fn($e) => $e->national()));
    }

    public function totalAllTypes(): int
    {
        return $this->weeks->sum(fn($w) => $w->entries->sum(fn($e) => $e->total()));
    }

    public function diferencia(): int
    {
        return $this->metaTotal() - $this->nationalTotal();
    }

    public function totalForType(int $courseTypeId, string $field): int
    {
        return $this->weeks->sum(function ($week) use ($courseTypeId, $field) {
            $entry = $week->entries->firstWhere('course_type_id', $courseTypeId);
            return $entry ? $entry->$field : 0;
        });
    }
}
