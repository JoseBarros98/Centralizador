<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingProgram extends Model
{
    protected $fillable = [
        'status',
        'program_id',
        'name',
        'certification',
        'marketing_team_id',
        'class_days',
        'has_teacher_panel',
        'teacher_panel_drive_id',
        'teacher_panel_drive_link',
        'campaign_launch_date',
        'inauguration_date',
        'module_0_date',
        'module_1_date',
        'gestion',
    ];

    protected $casts = [
        'class_days'        => 'array',
        'has_teacher_panel' => 'boolean',
        'campaign_launch_date' => 'date',
        'inauguration_date'    => 'date',
        'module_0_date'        => 'date',
        'module_1_date'        => 'date',
        'gestion'              => 'integer',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function marketingTeam()
    {
        return $this->belongsTo(MarketingTeam::class);
    }

    public function webinars()
    {
        return $this->hasMany(MarketingProgramWebinar::class)->orderBy('date');
    }
}
