<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagementExpenseBanco extends Model
{
    protected $table = 'management_expense_banco';

    protected $fillable = ['entity_id', 'mes', 'gestion', 'amount'];

    public function entity()
    {
        return $this->belongsTo(ManagementExpenseEntity::class, 'entity_id');
    }
}
