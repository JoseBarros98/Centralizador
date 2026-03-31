<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementInvestment extends Model
{
    use HasFactory;

    protected $fillable = [
        'item',
        'investment_amount',
        'mes',
        'gestion',
        'observation',
        'user_id',
    ];

    protected $casts = [
        'investment_amount' => 'decimal:2',
        'mes' => 'integer',
        'gestion' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
