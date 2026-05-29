<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MonthlyAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'mes',
        'gestion',
        'generado_por',
        'responsable_id',
        'estado',
    ];

    protected $casts = [
        'mes' => 'integer',
        'gestion' => 'integer',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generado_por');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function details()
    {
        return $this->hasMany(AssignmentDetail::class);
    }

    public function getMesNombreAttribute(): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
        return $meses[$this->mes] ?? $this->mes;
    }
}
