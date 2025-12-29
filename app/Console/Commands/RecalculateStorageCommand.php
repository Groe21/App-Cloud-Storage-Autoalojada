<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Jobs\RecalculateUserStorageJob;
use Illuminate\Console\Command;

class RecalculateStorageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:recalculate {user_id? : The ID of a specific user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate storage usage for all users or a specific user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user_id');

        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found");
                return self::FAILURE;
            }

            $this->info("Recalculating storage for user: {$user->email}");
            RecalculateUserStorageJob::dispatch($user);
            $this->info("Job dispatched successfully");
        } else {
            $users = User::all();
            $this->info("Recalculating storage for {$users->count()} users...");
            
            $bar = $this->output->createProgressBar($users->count());
            $bar->start();

            foreach ($users as $user) {
                RecalculateUserStorageJob::dispatch($user);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("All jobs dispatched successfully");
        }

        return self::SUCCESS;
    }
}
