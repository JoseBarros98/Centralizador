<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementIncomeEntityAmount extends Model
{
    protected $fillable = ['entity_id', 'item', 'mes', 'dia', 'gestion', 'amount', 'observation'];

    public function entity()
    {
        return $this->belongsTo(ManagementIncomeEntity::class, 'entity_id');
    }
}
