<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TypeOfArtFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_of_art_id',
        'file_path',
        'file_name',
        'stored_in_drive',
        'google_drive_id',
        'file_type',
        'description',
    ];

    /**
     * Get the content pillar that owns the file.
     */
    public function typeOfArt()
    {
        return $this->belongsTo(TypeOfArt::class);
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
