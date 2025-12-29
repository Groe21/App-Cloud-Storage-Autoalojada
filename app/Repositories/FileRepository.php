<?php

namespace App\Repositories;

use App\Models\File;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FileRepository
{
    /**
     * Get files by user
     */
    public function getByUser(int $userId, ?int $folderId = null, int $perPage = 20): LengthAwarePaginator
    {
        return File::where('user_id', $userId)
            ->where('folder_id', $folderId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get file by ID
     */
    public function findById(int $id): ?File
    {
        return File::find($id);
    }

    /**
     * Get file by ID and user
     */
    public function findByIdAndUser(int $id, int $userId): ?File
    {
        return File::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Create a new file
     */
    public function create(array $data): File
    {
        return File::create($data);
    }

    /**
     * Update a file
     */
    public function update(File $file, array $data): bool
    {
        return $file->update($data);
    }

    /**
     * Delete a file
     */
    public function delete(File $file): bool
    {
        return $file->delete();
    }

    /**
     * Get total files by user
     */
    public function getTotalFilesByUser(int $userId): int
    {
        return File::where('user_id', $userId)->count();
    }

    /**
     * Get total size by user
     */
    public function getTotalSizeByUser(int $userId): int
    {
        return File::where('user_id', $userId)->sum('size_bytes');
    }

    /**
     * Search files
     */
    public function search(int $userId, string $query, int $perPage = 20): LengthAwarePaginator
    {
        return File::where('user_id', $userId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'ILIKE', "%{$query}%")
                  ->orWhere('original_name', 'ILIKE', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get recent files
     */
    public function getRecent(int $userId, int $limit = 10): Collection
    {
        return File::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if file exists by hash
     */
    public function existsByHash(string $hash, int $userId): ?File
    {
        return File::where('hash', $hash)
            ->where('user_id', $userId)
            ->first();
    }
}
