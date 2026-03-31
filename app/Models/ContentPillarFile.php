<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentPillarFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_pillar_id',
        'file_path',
        'file_name',
        'file_type',
        'description',
        'google_drive_id',
        'google_drive_link',
        'file_size',
        'stored_in_drive',
    ];

    protected $casts = [
        'stored_in_drive' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Get the content pillar that owns the file.
     */
    public function contentPillar()
    {
        return $this->belongsTo(ContentPillar::class);
    }

    /**
     * Get the user who created the file.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the file.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
