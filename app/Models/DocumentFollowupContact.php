<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFollowupContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_followup_id',
        'type',
        'contact_date',
        'response_status',
        'response_date',
        'notes'
    ];

    protected $casts = [
        'contact_date' => 'date',
        'response_date' => 'date',
    ];

    public function documentFollowup()
    {
        return $this->belongsTo(DocumentFollowup::class);
    }
}
