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
        'attendance_file',
        'attendance_processed'
    ];

    protected $casts = [
        'class_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
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
