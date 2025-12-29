<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    /**
     * Get all users
     */
    public function getAll(int $perPage = 20): LengthAwarePaginator
    {
        return User::with('storageQuota')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get user by ID
     */
    public function findById(int $id): ?User
    {
        return User::with('storageQuota')->find($id);
    }

    /**
     * Get user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create a new user
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update a user
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * Delete a user
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Get total users count
     */
    public function getTotalCount(): int
    {
        return User::count();
    }

    /**
     * Get active users count
     */
    public function getActiveCount(): int
    {
        return User::where('is_active', true)->count();
    }

    /**
     * Get admin users
     */
    public function getAdmins(): Collection
    {
        return User::where('role', 'admin')->get();
    }

    /**
     * Get regular users
     */
    public function getRegularUsers(): Collection
    {
        return User::where('role', 'user')->get();
    }

    /**
     * Update user quota
     */
    public function updateQuota(User $user, int $quotaBytes): bool
    {
        return $user->storageQuota->update(['quota_bytes' => $quotaBytes]);
    }

    /**
     * Get total storage used by all users
     */
    public function getTotalStorageUsed(): int
    {
        return User::join('storage_quotas', 'users.id', '=', 'storage_quotas.user_id')
            ->sum('storage_quotas.used_bytes');
    }

    /**
     * Get total storage quota assigned
     */
    public function getTotalStorageQuota(): int
    {
        return User::join('storage_quotas', 'users.id', '=', 'storage_quotas.user_id')
            ->sum('storage_quotas.quota_bytes');
    }
}
