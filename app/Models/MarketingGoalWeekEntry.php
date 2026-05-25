<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingGoalWeekEntry extends Model
{
    protected $fillable = ['week_id', 'course_type_id', 'completo', 'adelanto', 'completando'];

    public function week(): BelongsTo
    {
        return $this->belongsTo(MarketingGoalWeek::class, 'week_id');
    }

    public function courseType(): BelongsTo
    {
        return $this->belongsTo(MarketingCourseType::class, 'course_type_id');
    }

    public function national(): int
    {
        return $this->completo + $this->adelanto;
    }

    public function total(): int
    {
        return $this->completo + $this->adelanto + $this->completando;
    }
}
