<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'entity_id' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the entity (polymorphic relation)
     */
    public function entity()
    {
        if ($this->entity_type && $this->entity_id) {
            $class = 'App\\Models\\' . $this->entity_type;
            if (class_exists($class)) {
                return $class::find($this->entity_id);
            }
        }
        return null;
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'upload' => 'bi-upload',
            'download' => 'bi-download',
            'delete' => 'bi-trash',
            'create_folder' => 'bi-folder-plus',
            'delete_folder' => 'bi-folder-minus',
            'login' => 'bi-box-arrow-in-right',
            'logout' => 'bi-box-arrow-right',
            'create_user' => 'bi-person-plus',
            'update_user' => 'bi-person-check',
            'delete_user' => 'bi-person-dash',
            'update_quota' => 'bi-hdd',
            default => 'bi-circle',
        };
    }

    /**
     * Get action color class
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'upload', 'create_folder', 'login', 'create_user' => 'success',
            'download' => 'info',
            'delete', 'delete_folder', 'logout', 'delete_user' => 'danger',
            'update_user', 'update_quota' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Static method to log activity
     */
    public static function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
