<?php

namespace App\Repositories;

use App\Models\Folder;
use Illuminate\Database\Eloquent\Collection;

class FolderRepository
{
    /**
     * Get folders by user
     */
    public function getByUser(int $userId, ?int $parentId = null): Collection
    {
        return Folder::where('user_id', $userId)
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get folder by ID
     */
    public function findById(int $id): ?Folder
    {
        return Folder::find($id);
    }

    /**
     * Get folder by ID and user
     */
    public function findByIdAndUser(int $id, int $userId): ?Folder
    {
        return Folder::where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Create a new folder
     */
    public function create(array $data): Folder
    {
        return Folder::create($data);
    }

    /**
     * Update a folder
     */
    public function update(Folder $folder, array $data): bool
    {
        return $folder->update($data);
    }

    /**
     * Delete a folder
     */
    public function delete(Folder $folder): bool
    {
        return $folder->delete();
    }

    /**
     * Check if folder exists
     */
    public function exists(int $userId, string $name, ?int $parentId = null): bool
    {
        return Folder::where('user_id', $userId)
            ->where('name', $name)
            ->where('parent_id', $parentId)
            ->exists();
    }

    /**
     * Get folder tree
     */
    public function getTree(int $userId): Collection
    {
        return Folder::where('user_id', $userId)
            ->whereNull('parent_id')
            ->with('descendants')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get breadcrumb trail
     */
    public function getBreadcrumbs(?int $folderId): array
    {
        if (!$folderId) {
            return [];
        }

        $folder = $this->findById($folderId);
        return $folder ? $folder->breadcrumbs : [];
    }
}
