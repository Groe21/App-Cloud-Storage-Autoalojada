<?php

namespace App\Console\Commands;

use App\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:cleanup {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned files that exist in storage but not in database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('Running in DRY RUN mode - no files will be deleted');
        }

        $disk = Storage::disk('users');
        $allFiles = $disk->allFiles();
        $orphanedFiles = [];
        $orphanedSize = 0;

        $this->info("Scanning {count($allFiles)} files...");
        $bar = $this->output->createProgressBar(count($allFiles));
        $bar->start();

        foreach ($allFiles as $filePath) {
            $fileExists = File::where('path', $filePath)->exists();
            
            if (!$fileExists) {
                $orphanedFiles[] = $filePath;
                $orphanedSize += $disk->size($filePath);
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if (empty($orphanedFiles)) {
            $this->info('No orphaned files found!');
            return self::SUCCESS;
        }

        $this->warn("Found " . count($orphanedFiles) . " orphaned files");
        $this->warn("Total size: " . $this->formatBytes($orphanedSize));

        if ($dryRun) {
            $this->info("\nFiles that would be deleted:");
            foreach (array_slice($orphanedFiles, 0, 10) as $file) {
                $this->line("  - {$file}");
            }
            if (count($orphanedFiles) > 10) {
                $this->line("  ... and " . (count($orphanedFiles) - 10) . " more");
            }
        } else {
            if (!$this->confirm('Do you want to delete these files?')) {
                $this->info('Cleanup cancelled');
                return self::SUCCESS;
            }

            $deleted = 0;
            foreach ($orphanedFiles as $file) {
                if ($disk->delete($file)) {
                    $deleted++;
                }
            }

            $this->info("Deleted {$deleted} orphaned files");
        }

        return self::SUCCESS;
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
