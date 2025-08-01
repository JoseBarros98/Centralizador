<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'inscription_id',
        'observations',
        'created_by'
    ];

    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contacts()
    {
        return $this->hasMany(DocumentFollowupContact::class);
    }

    public function getCalls()
    {
        return $this->contacts()->where('type', 'call')->get();
    }

    public function getMessages()
    {
        return $this->contacts()->where('type', 'message')->get();
    }

    // Accesores para facilitar el acceso a las llamadas y mensajes
    public function getCallsAttribute()
    {
        return $this->getCalls();
    }

    public function getMessagesAttribute()
    {
        return $this->getMessages();
    }
}
