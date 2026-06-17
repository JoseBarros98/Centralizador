<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InscriptionAlias extends Model
{
    protected $fillable = [
        'inscription_id',
        'program_id',
        'alias_name',
        'created_by',
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }
}
