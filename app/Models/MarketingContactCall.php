<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingContactCall extends Model
{
    protected $fillable = ['marketing_contact_id', 'respuesta', 'fecha_llamada', 'created_by'];

    protected $casts = ['fecha_llamada' => 'datetime'];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(MarketingContact::class, 'marketing_contact_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
