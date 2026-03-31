<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $fillable = [
        'initials',
        'name',
    ];

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }
}
