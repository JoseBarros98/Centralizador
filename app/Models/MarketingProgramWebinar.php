<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingProgramWebinar extends Model
{
    protected $fillable = [
        'marketing_program_id',
        'label',
        'date',
        'link',
        'drive_file_id',
        'drive_file_link',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function marketingProgram()
    {
        return $this->belongsTo(MarketingProgram::class);
    }
}
