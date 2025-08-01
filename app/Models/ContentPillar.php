<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentPillar extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Get the files for the content pillar.
     */
    public function files()
    {
        return $this->hasMany(ContentPillarFile::class);
    }

    /**
     * Get the user who created the content pillar.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the content pillar.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
