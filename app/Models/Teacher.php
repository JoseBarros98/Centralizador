<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'paternal_surname',
        'maternal_surname',
        'email',
        'phone',
        'address',
        'birth_date',
        'profession',
        'ci',
        'academic_degree',
        'created_by',
        'updated_by',
    ];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files()
    {
        return $this->hasMany(TeacherFile::class);
    }

    public function modules()
    {
        return $this->hasMAny(Module::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getFullNameAttribute()
    {
        return trim($this->name . ' ' . $this->paternal_surname . ' ' . $this->maternal_surname);
    }
}
