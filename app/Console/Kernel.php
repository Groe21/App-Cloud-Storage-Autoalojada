<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\CollectServerMetricsJob;
use App\Services\ServerMetricsService;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Collect server metrics every 5 minutes
        $schedule->job(new CollectServerMetricsJob())->everyFiveMinutes();

        // Clean old metrics (keep last 7 days) - daily at 2am
        $schedule->call(function () {
            app(ServerMetricsService::class)->cleanOldMetrics();
        })->dailyAt('02:00');

        // Recalculate storage for all users - daily at 3am
        $schedule->command('storage:recalculate')->dailyAt('03:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
