<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCourseType extends Model
{
    protected $fillable = ['name', 'sort_order', 'active'];

    public function entries(): HasMany
    {
        return $this->hasMany(MarketingGoalWeekEntry::class, 'course_type_id');
    }

    public function hiddenMonths(): HasMany
    {
        return $this->hasMany(MarketingCourseTypeHiddenMonth::class, 'course_type_id');
    }
}
