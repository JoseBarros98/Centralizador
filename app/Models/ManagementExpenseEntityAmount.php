<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementExpenseEntityAmount extends Model
{
    protected $fillable = ['entity_id', 'item', 'category', 'mes', 'gestion', 'dia', 'amount', 'observation'];

    public function entity()
    {
        return $this->belongsTo(ManagementExpenseEntity::class, 'entity_id');
    }
}
