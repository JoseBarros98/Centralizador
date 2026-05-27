<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtRequestComment extends Model
{
    protected $fillable = ['art_request_id', 'user_id', 'body', 'is_internal', 'attachment_name', 'attachment_drive_id', 'attachment_type'];

    protected $casts = ['is_internal' => 'boolean'];

    public function artRequest()
    {
        return $this->belongsTo(ArtRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
