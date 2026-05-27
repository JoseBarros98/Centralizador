<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementExpenseEntity extends Model
{
    protected $fillable = ['gestion', 'name', 'display_order'];

    public function amounts()
    {
        return $this->hasMany(ManagementExpenseEntityAmount::class, 'entity_id');
    }
}
