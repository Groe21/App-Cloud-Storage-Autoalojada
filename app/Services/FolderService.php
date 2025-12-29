<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\ActivityLog;
use App\Repositories\FolderRepository;

class FolderService
{
    public function __construct(
        private FolderRepository $folderRepository
    ) {}

    /**
     * Create a folder
     */
    public function create(int $userId, string $name, ?int $parentId = null): Folder
    {
        // Check if folder already exists
        if ($this->folderRepository->exists($userId, $name, $parentId)) {
            throw new \Exception('Ya existe una carpeta con ese nombre en esta ubicación');
        }
        
        $folder = $this->folderRepository->create([
            'user_id' => $userId,
            'parent_id' => $parentId,
            'name' => $name,
        ]);
        
        // Log activity
        ActivityLog::log(
            'create_folder',
            'Folder',
            $folder->id,
            "Created folder: {$name}"
        );
        
        return $folder;
    }

    /**
     * Delete a folder
     */
    public function delete(Folder $folder): bool
    {
        $folderName = $folder->name;
        $deleted = $this->folderRepository->delete($folder);
        
        if ($deleted) {
            // Log activity
            ActivityLog::log(
                'delete_folder',
                'Folder',
                $folder->id,
                "Deleted folder: {$folderName}"
            );
        }
        
        return $deleted;
    }

    /**
     * Get folders by user
     */
    public function getFoldersByUser(int $userId, ?int $parentId = null)
    {
        return $this->folderRepository->getByUser($userId, $parentId);
    }

    /**
     * Get folder tree
     */
    public function getFolderTree(int $userId)
    {
        return $this->folderRepository->getTree($userId);
    }

    /**
     * Get breadcrumbs
     */
    public function getBreadcrumbs(?int $folderId): array
    {
        return $this->folderRepository->getBreadcrumbs($folderId);
    }

    /**
     * Rename folder
     */
    public function rename(Folder $folder, string $newName): bool
    {
        // Check if folder with new name already exists
        if ($this->folderRepository->exists($folder->user_id, $newName, $folder->parent_id)) {
            throw new \Exception('Ya existe una carpeta con ese nombre en esta ubicación');
        }
        
        return $this->folderRepository->update($folder, ['name' => $newName]);
    }
}
