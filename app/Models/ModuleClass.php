<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'class_date',
        'start_time',
        'end_time',
        'class_link',
        'google_calendar_event_id',
        'google_calendar_event_link',
        'google_meet_link',
        'google_meet_conference_id',
        'google_meet_co_organizers',
        'google_synced_at',
        'google_sync_error',
        'attendance_file',
        'attendance_processed'
    ];

    protected $casts = [
        'class_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'google_meet_co_organizers' => 'array',
        'google_synced_at' => 'datetime',
        'attendance_processed' => 'boolean',
    ];

    /**
     * Get the module that owns the class.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the attendance records for the class.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
