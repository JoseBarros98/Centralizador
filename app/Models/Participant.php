<?php

namespace App\Models;

use App\Helpers\NameMatcher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'name',
        'document',
        'profession',
        'phone',
        'university',
    ];

    /**
     * Obtener el programa al que pertenece el participante.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Obtener las asistencias del participante.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Normalizar el nombre para comparaciones.
     */
    public static function normalizeName($name)
    {
        return NameMatcher::normalizeName($name);
    }
}
