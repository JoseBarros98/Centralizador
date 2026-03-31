<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'item',
        'expense_amount',
        'mes',
        'gestion',
        'observation',
        'user_id',
    ];

    protected $casts = [
        'expense_amount' => 'decimal:2',
        'mes' => 'integer',
        'gestion' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
