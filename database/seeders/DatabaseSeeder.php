<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@cloudstorage.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create storage quota for admin (100GB)
        $admin->storageQuota()->create([
            'quota_bytes' => 107374182400, // 100GB
            'used_bytes' => 0,
        ]);

        // Create test user
        $user = User::create([
            'name' => 'Test User',
            'username' => 'usuario',
            'email' => 'user@cloudstorage.local',
            'password' => Hash::make('password'),
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create storage quota for test user (10GB)
        $user->storageQuota()->create([
            'quota_bytes' => 10737418240, // 10GB
            'used_bytes' => 0,
        ]);

        $this->command->info('Seeder completed successfully!');
        $this->command->info('Admin: admin / password');
        $this->command->info('Usuario: usuario / password');
    }
}
