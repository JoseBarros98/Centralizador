<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingGoalWeek extends Model
{
    protected $fillable = [
        'goal_id',
        'week_number',
        'weekly_goal',
        'week_start',
        'week_end',
        'meeting_date',
    ];

    protected $casts = [
        'week_start'   => 'date',
        'week_end'     => 'date',
        'meeting_date' => 'date',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(MarketingGoal::class, 'goal_id');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(MarketingGoalWeekEntry::class, 'week_id');
    }

    public function totalWeek(): int
    {
        return $this->entries->sum(fn($e) => $e->total());
    }

    public function nationalWeek(): int
    {
        return $this->entries->sum(fn($e) => $e->national());
    }
}
