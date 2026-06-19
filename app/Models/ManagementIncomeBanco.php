<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementIncomeBanco extends Model
{
    protected $table = 'management_income_banco';

    protected $fillable = ['entity_id', 'mes', 'gestion', 'amount'];

    public function entity()
    {
        return $this->belongsTo(ManagementIncomeEntity::class, 'entity_id');
    }
}
