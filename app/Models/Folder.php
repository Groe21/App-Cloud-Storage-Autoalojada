<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
        'path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * Get the user that owns the folder.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent folder.
     */
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    /**
     * Get the child folders.
     */
    public function children()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    /**
     * Get the files in this folder.
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }

    /**
     * Get all descendant folders recursively
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the full path of the folder
     */
    public function getFullPathAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_path . '/' . $this->name;
        }
        return $this->name;
    }

    /**
     * Get breadcrumb trail
     */
    public function getBreadcrumbsAttribute(): array
    {
        $breadcrumbs = [];
        $folder = $this;
        
        while ($folder) {
            array_unshift($breadcrumbs, [
                'id' => $folder->id,
                'name' => $folder->name,
            ]);
            $folder = $folder->parent;
        }
        
        return $breadcrumbs;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Update path when created
        static::creating(function ($folder) {
            if ($folder->parent) {
                $folder->path = $folder->parent->full_path;
            } else {
                $folder->path = '';
            }
        });

        // Cascade delete files when folder is deleted
        static::deleting(function ($folder) {
            // Delete all files in this folder
            $folder->files()->delete();
            
            // Delete all child folders recursively
            $folder->children()->each(function ($child) {
                $child->delete();
            });
        });
    }
}
