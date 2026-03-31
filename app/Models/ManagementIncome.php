<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementIncome extends Model
{
    use HasFactory;

    protected $fillable = [
        'item',
        'income_amount',
        'mes',
        'gestion',
        'observation',
        'user_id',
    ];

    protected $casts = [
        'income_amount' => 'decimal:2',
        'mes' => 'integer',
        'gestion' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
