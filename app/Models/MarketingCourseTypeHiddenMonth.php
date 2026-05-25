<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingCourseTypeHiddenMonth extends Model
{
    protected $fillable = ['course_type_id', 'month', 'year'];

    public function courseType(): BelongsTo
    {
        return $this->belongsTo(MarketingCourseType::class, 'course_type_id');
    }
}
