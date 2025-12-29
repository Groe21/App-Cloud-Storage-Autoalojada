<?php

namespace App\Services;

use App\Models\File;
use App\Models\ActivityLog;
use App\Repositories\FileRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    public function __construct(
        private FileRepository $fileRepository
    ) {}

    /**
     * Upload a file
     */
    public function upload(UploadedFile $file, int $userId, ?int $folderId = null): File
    {
        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $userPath = "user_{$userId}";
        
        // Add folder path if specified
        if ($folderId) {
            $folder = app(FolderRepository::class)->findById($folderId);
            if ($folder) {
                $userPath .= '/' . $folder->full_path;
            }
        }
        
        $path = $userPath . '/' . $filename;
        
        // Store file
        Storage::disk('users')->put($path, file_get_contents($file->getRealPath()));
        
        // Calculate file hash
        $hash = hash_file('sha256', $file->getRealPath());
        
        // Create file record
        $fileModel = $this->fileRepository->create([
            'user_id' => $userId,
            'folder_id' => $folderId,
            'name' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'extension' => $extension,
            'size_bytes' => $file->getSize(),
            'hash' => $hash,
        ]);
        
        // Log activity
        ActivityLog::log(
            'upload',
            'File',
            $fileModel->id,
            "Uploaded file: {$file->getClientOriginalName()}",
            ['size' => $file->getSize(), 'mime_type' => $file->getMimeType()]
        );
        
        return $fileModel;
    }

    /**
     * Download a file
     */
    public function download(File $file): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Log activity
        ActivityLog::log(
            'download',
            'File',
            $file->id,
            "Downloaded file: {$file->original_name}"
        );
        
        return Storage::disk('users')->download($file->path, $file->original_name);
    }

    /**
     * Delete a file
     */
    public function delete(File $file): bool
    {
        $originalName = $file->original_name;
        $deleted = $this->fileRepository->delete($file);
        
        if ($deleted) {
            // Log activity
            ActivityLog::log(
                'delete',
                'File',
                $file->id,
                "Deleted file: {$originalName}"
            );
        }
        
        return $deleted;
    }

    /**
     * Get files by folder
     */
    public function getFilesByFolder(int $userId, ?int $folderId = null, int $perPage = 20)
    {
        return $this->fileRepository->getByUser($userId, $folderId, $perPage);
    }

    /**
     * Search files
     */
    public function search(int $userId, string $query, int $perPage = 20)
    {
        return $this->fileRepository->search($userId, $query, $perPage);
    }

    /**
     * Get recent files
     */
    public function getRecent(int $userId, int $limit = 10)
    {
        return $this->fileRepository->getRecent($userId, $limit);
    }

    /**
     * Validate file upload
     */
    public function validateUpload(UploadedFile $file, int $userId): array
    {
        $errors = [];
        
        // Check file size
        $maxSize = config('storage.max_file_size');
        if ($file->getSize() > $maxSize) {
            $errors[] = 'El archivo excede el tamaño máximo permitido de ' . 
                        round($maxSize / (1024 * 1024), 2) . ' MB';
        }
        
        // Check file type
        $allowedTypes = config('storage.allowed_file_types');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedTypes)) {
            $errors[] = 'Tipo de archivo no permitido. Tipos permitidos: ' . 
                        implode(', ', $allowedTypes);
        }
        
        // Check user storage quota
        $user = app(UserRepository::class)->findById($userId);
        if (!$user->hasAvailableStorage($file->getSize())) {
            $errors[] = 'No tienes suficiente espacio disponible para subir este archivo';
        }
        
        return $errors;
    }

    /**
     * Get file statistics for user
     */
    public function getUserStatistics(int $userId): array
    {
        return [
            'total_files' => $this->fileRepository->getTotalFilesByUser($userId),
            'total_size' => $this->fileRepository->getTotalSizeByUser($userId),
        ];
    }
}
