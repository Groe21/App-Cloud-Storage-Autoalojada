<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'folder_id',
        'name',
        'original_name',
        'path',
        'mime_type',
        'extension',
        'size_bytes',
        'hash',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'folder_id' => 'integer',
        'size_bytes' => 'integer',
    ];

    /**
     * Get the user that owns the file.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the folder that contains the file.
     */
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get file size in human readable format
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file icon based on mime type
     */
    public function getIconAttribute(): string
    {
        $mimeType = $this->mime_type;
        
        if (str_starts_with($mimeType, 'image/')) {
            return 'bi-file-image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            return 'bi-file-play';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return 'bi-file-music';
        } elseif (str_contains($mimeType, 'pdf')) {
            return 'bi-file-pdf';
        } elseif (str_contains($mimeType, 'word') || str_contains($mimeType, 'document')) {
            return 'bi-file-word';
        } elseif (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) {
            return 'bi-file-excel';
        } elseif (str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation')) {
            return 'bi-file-ppt';
        } elseif (str_contains($mimeType, 'zip') || str_contains($mimeType, 'compressed')) {
            return 'bi-file-zip';
        } elseif (str_contains($mimeType, 'text')) {
            return 'bi-file-text';
        }
        
        return 'bi-file-earmark';
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a document
     */
    public function isDocument(): bool
    {
        $documentTypes = ['pdf', 'word', 'document', 'excel', 'spreadsheet', 'powerpoint', 'presentation'];
        foreach ($documentTypes as $type) {
            if (str_contains($this->mime_type, $type)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get download URL
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('files.download', $this->id);
    }

    /**
     * Get full storage path
     */
    public function getFullPathAttribute(): string
    {
        return $this->path;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Update user storage quota when file is created
        static::created(function ($file) {
            $file->user->storageQuota->incrementUsedBytes($file->size_bytes);
        });

        // Update user storage quota when file is deleted
        static::deleted(function ($file) {
            // Delete physical file from storage
            if (Storage::disk('users')->exists($file->path)) {
                Storage::disk('users')->delete($file->path);
            }
            
            // Update storage quota
            $file->user->storageQuota->decrementUsedBytes($file->size_bytes);
        });
    }
}
