<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {--admin : Create an admin user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user interactively';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Creating a new user...');
        $this->newLine();

        // Get user input
        $name = $this->ask('Name');
        $email = $this->ask('Email');
        $password = $this->secret('Password');
        $passwordConfirmation = $this->secret('Confirm Password');
        
        $isAdmin = $this->option('admin');
        if (!$isAdmin) {
            $isAdmin = $this->confirm('Create as admin?', false);
        }

        $quotaGb = $this->ask('Storage quota (GB)', 10);

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'quota_gb' => $quotaGb,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'quota_gb' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        if ($password !== $passwordConfirmation) {
            $this->error('Passwords do not match');
            return self::FAILURE;
        }

        // Create user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $isAdmin ? 'admin' : 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Update quota
        $quotaBytes = $quotaGb * 1024 * 1024 * 1024;
        $user->storageQuota->update(['quota_bytes' => $quotaBytes]);

        $this->newLine();
        $this->info('User created successfully!');
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $user->name],
                ['Email', $user->email],
                ['Role', $user->role],
                ['Quota', $quotaGb . ' GB'],
            ]
        );

        return self::SUCCESS;
    }
}
