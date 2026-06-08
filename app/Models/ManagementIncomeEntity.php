<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementIncomeEntity extends Model
{
    protected $fillable = ['gestion', 'name', 'display_order'];

    public function amounts()
    {
        return $this->hasMany(ManagementIncomeEntityAmount::class, 'entity_id');
    }
}
