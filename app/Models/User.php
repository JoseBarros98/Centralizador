<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
    ];

    public function createdInscriptions()
    {
        return $this->hasMany(Inscription::class, 'created_by');
    }

    public function updatedInscriptions()
    {
        return $this->hasMany(Inscription::class, 'updated_by');
    }
    
    public function advisedInscriptions()
    {
        return $this->hasMany(Inscription::class, 'user_advisor_id');
    }
    
    // Scope para filtrar usuarios activos
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    
}
