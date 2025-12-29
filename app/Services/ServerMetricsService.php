<?php

namespace App\Services;

use App\Models\ServerMetric;
use Illuminate\Support\Facades\Cache;

class ServerMetricsService
{
    /**
     * Collect current server metrics
     */
    public function collect(): ServerMetric
    {
        $metrics = [
            'cpu_usage' => $this->getCpuUsage(),
            'memory_total' => $this->getMemoryTotal(),
            'memory_used' => $this->getMemoryUsed(),
            'memory_free' => $this->getMemoryFree(),
            'memory_usage_percent' => $this->getMemoryUsagePercent(),
            'disk_total' => $this->getDiskTotal(),
            'disk_used' => $this->getDiskUsed(),
            'disk_free' => $this->getDiskFree(),
            'disk_usage_percent' => $this->getDiskUsagePercent(),
            'load_average_1' => $this->getLoadAverage1(),
            'load_average_5' => $this->getLoadAverage5(),
            'load_average_15' => $this->getLoadAverage15(),
            'recorded_at' => now(),
        ];

        return ServerMetric::create($metrics);
    }

    /**
     * Get latest metrics (with cache)
     */
    public function getLatest(): ?ServerMetric
    {
        try {
            return cache()->remember('server_metrics_latest', 300, function () {
                return ServerMetric::latest('recorded_at')->first() ?? $this->collect();
            });
        } catch (\Exception $e) {
            // Si falla el cache, obtener directamente
            return ServerMetric::latest('recorded_at')->first() ?? $this->collect();
        }
    }

    /**
     * Get historical metrics
     */
    public function getHistorical(int $hours = 24)
    {
        $since = now()->subHours($hours);
        return ServerMetric::where('recorded_at', '>=', $since)
            ->orderBy('recorded_at')
            ->get();
    }

    /**
     * Clean old metrics (keep last 7 days)
     */
    public function cleanOldMetrics(): int
    {
        $threshold = now()->subDays(7);
        return ServerMetric::where('recorded_at', '<', $threshold)->delete();
    }

    /**
     * Get CPU usage
     */
    private function getCpuUsage(): ?float
    {
        if (!function_exists('sys_getloadavg')) {
            return null;
        }

        $load = sys_getloadavg();
        $cpuCount = $this->getCpuCount();
        
        if ($cpuCount > 0 && isset($load[0])) {
            return round(($load[0] / $cpuCount) * 100, 2);
        }

        return null;
    }

    /**
     * Get CPU count
     */
    private function getCpuCount(): int
    {
        if (is_file('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            return count($matches[0]);
        }
        return 1;
    }

    /**
     * Get memory information
     */
    private function getMemoryInfo(): array
    {
        $memInfo = [];
        
        if (is_file('/proc/meminfo')) {
            $content = file_get_contents('/proc/meminfo');
            preg_match_all('/^(\w+):\s+(\d+)/m', $content, $matches);
            
            foreach ($matches[1] as $i => $key) {
                $memInfo[$key] = (int)$matches[2][$i] * 1024; // Convert to bytes
            }
        }
        
        return $memInfo;
    }

    /**
     * Get total memory
     */
    private function getMemoryTotal(): ?int
    {
        $memInfo = $this->getMemoryInfo();
        return $memInfo['MemTotal'] ?? null;
    }

    /**
     * Get used memory
     */
    private function getMemoryUsed(): ?int
    {
        $memInfo = $this->getMemoryInfo();
        if (isset($memInfo['MemTotal'], $memInfo['MemAvailable'])) {
            return $memInfo['MemTotal'] - $memInfo['MemAvailable'];
        }
        return null;
    }

    /**
     * Get free memory
     */
    private function getMemoryFree(): ?int
    {
        $memInfo = $this->getMemoryInfo();
        return $memInfo['MemAvailable'] ?? null;
    }

    /**
     * Get memory usage percentage
     */
    private function getMemoryUsagePercent(): ?float
    {
        $total = $this->getMemoryTotal();
        $used = $this->getMemoryUsed();
        
        if ($total && $used) {
            return round(($used / $total) * 100, 2);
        }
        
        return null;
    }

    /**
     * Get disk information
     */
    private function getDiskInfo(): array
    {
        $path = storage_path();
        
        return [
            'total' => disk_total_space($path),
            'free' => disk_free_space($path),
        ];
    }

    /**
     * Get total disk space
     */
    private function getDiskTotal(): ?int
    {
        $info = $this->getDiskInfo();
        return $info['total'] ?? null;
    }

    /**
     * Get used disk space
     */
    private function getDiskUsed(): ?int
    {
        $info = $this->getDiskInfo();
        if (isset($info['total'], $info['free'])) {
            return $info['total'] - $info['free'];
        }
        return null;
    }

    /**
     * Get free disk space
     */
    private function getDiskFree(): ?int
    {
        $info = $this->getDiskInfo();
        return $info['free'] ?? null;
    }

    /**
     * Get disk usage percentage
     */
    private function getDiskUsagePercent(): ?float
    {
        $total = $this->getDiskTotal();
        $used = $this->getDiskUsed();
        
        if ($total && $used) {
            return round(($used / $total) * 100, 2);
        }
        
        return null;
    }

    /**
     * Get load averages
     */
    private function getLoadAverages(): array
    {
        if (function_exists('sys_getloadavg')) {
            return sys_getloadavg();
        }
        return [null, null, null];
    }

    private function getLoadAverage1(): ?float
    {
        $load = $this->getLoadAverages();
        return $load[0] ?? null;
    }

    private function getLoadAverage5(): ?float
    {
        $load = $this->getLoadAverages();
        return $load[1] ?? null;
    }

    private function getLoadAverage15(): ?float
    {
        $load = $this->getLoadAverages();
        return $load[2] ?? null;
    }
}
