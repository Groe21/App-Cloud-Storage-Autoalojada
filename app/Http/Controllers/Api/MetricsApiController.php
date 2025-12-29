<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ServerMetricsService;
use App\Repositories\UserRepository;

class MetricsApiController extends Controller
{
    public function __construct(
        private ServerMetricsService $metricsService,
        private UserRepository $userRepository
    ) {
        $this->middleware('admin');
    }

    /**
     * Get server metrics
     */
    public function server()
    {
        $metrics = $this->metricsService->getLatest();

        return response()->json([
            'cpu_usage' => $metrics->cpu_usage,
            'memory' => [
                'total' => $metrics->memory_total,
                'used' => $metrics->memory_used,
                'free' => $metrics->memory_free,
                'usage_percent' => $metrics->memory_usage_percent,
                'total_human' => $metrics->memory_total_human,
                'used_human' => $metrics->memory_used_human,
            ],
            'disk' => [
                'total' => $metrics->disk_total,
                'used' => $metrics->disk_used,
                'free' => $metrics->disk_free,
                'usage_percent' => $metrics->disk_usage_percent,
                'total_human' => $metrics->disk_total_human,
                'used_human' => $metrics->disk_used_human,
            ],
            'load_average' => [
                '1min' => $metrics->load_average_1,
                '5min' => $metrics->load_average_5,
                '15min' => $metrics->load_average_15,
            ],
            'recorded_at' => $metrics->recorded_at,
        ]);
    }

    /**
     * Get storage metrics
     */
    public function storage()
    {
        $totalStorageUsed = $this->userRepository->getTotalStorageUsed();
        $totalStorageQuota = $this->userRepository->getTotalStorageQuota();
        $totalUsers = $this->userRepository->getTotalCount();

        return response()->json([
            'total_users' => $totalUsers,
            'total_storage_used' => $totalStorageUsed,
            'total_storage_quota' => $totalStorageQuota,
            'storage_available' => $totalStorageQuota - $totalStorageUsed,
            'usage_percent' => $totalStorageQuota > 0 
                ? round(($totalStorageUsed / $totalStorageQuota) * 100, 2) 
                : 0,
        ]);
    }

    /**
     * Get historical metrics
     */
    public function historical()
    {
        $hours = request()->get('hours', 24);
        $metrics = $this->metricsService->getHistorical($hours);

        return response()->json($metrics);
    }
}
