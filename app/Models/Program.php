<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    public function inscriptions()
{
    return $this->hasMany(Inscription::class);
}

    public function modules()
    {
        return $this->hasMAny(Module::class);
    }

}
