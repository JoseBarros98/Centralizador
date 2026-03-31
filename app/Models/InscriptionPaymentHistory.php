<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InscriptionPaymentHistory extends Model
{
    protected $table = 'inscription_payment_history';

    protected $fillable = [
        'inscription_id',
        'ci',
        'old_status',
        'new_status',
        'amount_paid',
        'status_date',
        'notes',
        'changed_by',
    ];

    protected $casts = [
        'status_date' => 'date',
        'amount_paid' => 'decimal:2',
    ];

    public function inscription(): BelongsTo
    {
        return $this->belongsTo(Inscription::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
