<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArtRequestFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'art_request_id',
        'file_path',
        'file_name',
        'file_type',
        'file_category',
        'description',
        'created_by',
        'updated_by'
    ];

    // Relaciones
    public function artRequest()
    {
        return $this->belongsTo(ArtRequest::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Métodos auxiliares
    public function getFileSizeAttribute()
    {
        $path = storage_path('app/public/' . $this->file_path);
        return file_exists($path) ? filesize($path) : 0;
    }

    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getCategoryColorAttribute()
    {
        return match($this->file_category) {
            'VIDEO' => 'purple',
            'IMAGEN' => 'green',
            'DOCUMENTO' => 'blue',
            default => 'gray'
        };
    }
}
