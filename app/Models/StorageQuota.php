<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageQuota extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'quota_bytes',
        'used_bytes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quota_bytes' => 'integer',
        'used_bytes' => 'integer',
    ];

    /**
     * Get the user that owns the storage quota.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get available bytes
     */
    public function getAvailableBytesAttribute(): int
    {
        return max(0, $this->quota_bytes - $this->used_bytes);
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->quota_bytes == 0) {
            return 0;
        }
        return round(($this->used_bytes / $this->quota_bytes) * 100, 2);
    }

    /**
     * Get quota in GB
     */
    public function getQuotaGbAttribute(): float
    {
        return round($this->quota_bytes / (1024 * 1024 * 1024), 2);
    }

    /**
     * Get used in GB
     */
    public function getUsedGbAttribute(): float
    {
        return round($this->used_bytes / (1024 * 1024 * 1024), 2);
    }

    /**
     * Get available in GB
     */
    public function getAvailableGbAttribute(): float
    {
        return round($this->available_bytes / (1024 * 1024 * 1024), 2);
    }

    /**
     * Check if has available space
     */
    public function hasAvailableSpace(int $requiredBytes): bool
    {
        return $this->available_bytes >= $requiredBytes;
    }

    /**
     * Increment used bytes
     */
    public function incrementUsedBytes(int $bytes): bool
    {
        return $this->increment('used_bytes', $bytes);
    }

    /**
     * Decrement used bytes
     */
    public function decrementUsedBytes(int $bytes): bool
    {
        return $this->decrement('used_bytes', max(0, $bytes));
    }
}
