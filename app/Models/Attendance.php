<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_class_id',
        'inscription_id',
        'name',
        'email',
        'join_time',
        'leave_time',
        'duration',
        'is_registered_inscription',
        'attendance_percentage',
        'status',
        'has_license',
        'license_type',
        'license_notes',
        'license_granted_by',
        'license_granted_at',
        'drive_file_id',
        'drive_folder_id'
    ];

    protected $casts = [
        'has_license' => 'boolean',
        'license_granted_at' => 'datetime',
        'join_time' => 'datetime',
        'leave_time' => 'datetime'
    ];

    public const STATUS_PRESENT = 'present';
    public const STATUS_LATE = 'late';
    public const STATUS_ABSENT = 'absent';

    public function moduleClass()
    {
        return $this->belongsTo(ModuleClass::class);
    }

    // public function participant()
    // {
    //     return $this->belongsTo(Participant::class);
    // }

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function getStatusTextAttribute()
    {
        return [
            self::STATUS_PRESENT => 'Presente',
            self::STATUS_LATE => 'Tarde',
            self::STATUS_ABSENT => 'Ausente',
        ][$this->status] ?? 'Desconocido';
    }

    public function getStatusClassAttribute()
    {
        return [
            self::STATUS_PRESENT => 'bg-green-100 text-green-800',
            self::STATUS_LATE => 'bg-yellow-100 text-yellow-800',
            self::STATUS_ABSENT => 'bg-red-100 text-red-800',
        ][$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getStatusIconAttribute()
    {
        return [
            self::STATUS_PRESENT => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>',
            self::STATUS_LATE => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
            self::STATUS_ABSENT => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>',
        ][$this->status] ?? '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
    }

    /**
     * Get the formatted attendance percentage.
     *
     * @return string
     */
    public function getFormattedPercentageAttribute()
    {
        return number_format($this->attendance_percentage, 1) . '%';
    }

    /**
     * Get the color class for the progress bar based on attendance percentage.
     *
     * @return string
     */
    public function getProgressColorAttribute()
    {
        if ($this->attendance_percentage >= 70) {
            return 'bg-green-600';
        } elseif ($this->attendance_percentage >= 45) {
            return 'bg-yellow-500';
        } else {
            return 'bg-red-500';
        }
    }

    /**
     * Get the human-readable text for the license type.
     *
     * @return string
     */
    public function getLicenseTypeTextAttribute()
    {
        $types = [
            'permiso' => 'Permiso General',
            'licencia_medica' => 'Licencia Médica',
            'licencia_laboral' => 'Licencia Laboral',
            'emergencia_familiar' => 'Emergencia Familiar',
            'otro' => 'Otro'
        ];

        return $types[$this->license_type] ?? $this->license_type;
    }

    /**
     * Get the formatted join time.
     *
     * @return string
     */
    public function getFormattedJoinTimeAttribute()
    {
        if (!$this->join_time) {
            return '-';
        }

        try {
            if (is_string($this->join_time)) {
                return date('H:i', strtotime($this->join_time));
            } else {
                return $this->join_time->format('H:i');
            }
        } catch (\Exception $e) {
            return $this->join_time;
        }
    }
}
