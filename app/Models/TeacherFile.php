<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherFile extends Model
{
    use HasFactory;
    protected $table = 'teachers_files';

    protected $fillable = [
        'teacher_id',
        'file_path',
        'file_name',
        'file_type',
        'updated_by',
        'description',
    ];
    
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
