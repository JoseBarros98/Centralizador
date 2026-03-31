<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'email',
        'phone',
        'address',
        'birth_date',
        'bank',
        'account_number',
        'bill',
        'esam_worker',
        'profession',
        'ci',
        'academic_degree',
        'is_external',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_external' => 'boolean',
        'birth_date' => 'date',
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
        return $this->hasMany(Module::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getFullNameAttribute()
    {
        return trim($this->name);
    }

    public function paymentRequest()
    {
        return $this->hasMany(PaymentRequest::class);
    }

    /**
     * Buscar docente por external_id
     */
    public static function findByExternalId($externalId)
    {
        return self::where('external_id', $externalId)->first();
    }

    /**
     * Crear o actualizar docente desde datos externos
     */
    public static function createOrUpdateFromExternal($externalId, $fullName)
    {
        $teacher = self::findByExternalId($externalId);
        
        if (!$teacher) {
            $teacher = self::create([
                'external_id' => $externalId,
                'name' => $fullName,
                'is_external' => true,
            ]);
        }
        
        return $teacher;
    }
}
