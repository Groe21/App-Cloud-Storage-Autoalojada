<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServerMetric extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cpu_usage',
        'memory_total',
        'memory_used',
        'memory_free',
        'memory_usage_percent',
        'disk_total',
        'disk_used',
        'disk_free',
        'disk_usage_percent',
        'load_average_1',
        'load_average_5',
        'load_average_15',
        'recorded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cpu_usage' => 'decimal:2',
        'memory_total' => 'integer',
        'memory_used' => 'integer',
        'memory_free' => 'integer',
        'memory_usage_percent' => 'decimal:2',
        'disk_total' => 'integer',
        'disk_used' => 'integer',
        'disk_free' => 'integer',
        'disk_usage_percent' => 'decimal:2',
        'load_average_1' => 'decimal:2',
        'load_average_5' => 'decimal:2',
        'load_average_15' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    /**
     * Get memory used in human readable format
     */
    public function getMemoryUsedHumanAttribute(): string
    {
        return $this->formatBytes($this->memory_used);
    }

    /**
     * Get memory total in human readable format
     */
    public function getMemoryTotalHumanAttribute(): string
    {
        return $this->formatBytes($this->memory_total);
    }

    /**
     * Get disk used in human readable format
     */
    public function getDiskUsedHumanAttribute(): string
    {
        return $this->formatBytes($this->disk_used);
    }

    /**
     * Get disk total in human readable format
     */
    public function getDiskTotalHumanAttribute(): string
    {
        return $this->formatBytes($this->disk_total);
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(?int $bytes): string
    {
        if ($bytes === null) {
            return 'N/A';
        }
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
