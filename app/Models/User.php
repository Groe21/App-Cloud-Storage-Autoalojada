<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Campo usado para autenticación
     */
    public function username()
    {
        return 'username';
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Get the storage quota for the user.
     */
    public function storageQuota()
    {
        return $this->hasOne(StorageQuota::class);
    }

    /**
     * Get the files for the user.
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }

    /**
     * Get the folders for the user.
     */
    public function folders()
    {
        return $this->hasMany(Folder::class);
    }

    /**
     * Get the activity logs for the user.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get available storage space in bytes
     */
    public function getAvailableStorageAttribute(): int
    {
        $quota = $this->storageQuota;
        if (!$quota) {
            return 0;
        }
        return max(0, $quota->quota_bytes - $quota->used_bytes);
    }

    /**
     * Get storage usage percentage
     */
    public function getStorageUsagePercentageAttribute(): float
    {
        $quota = $this->storageQuota;
        if (!$quota || $quota->quota_bytes == 0) {
            return 0;
        }
        return round(($quota->used_bytes / $quota->quota_bytes) * 100, 2);
    }

    /**
     * Check if user has available storage
     */
    public function hasAvailableStorage(int $requiredBytes): bool
    {
        return $this->availableStorage >= $requiredBytes;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Create storage quota when user is created
        static::created(function ($user) {
            if (!$user->storageQuota) {
                $defaultQuotaGB = config('storage.default_user_quota', 10);
                $user->storageQuota()->create([
                    'quota_bytes' => $defaultQuotaGB * 1024 * 1024 * 1024,
                    'used_bytes' => 0,
                ]);
            }
        });
    }
}
