<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileService;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FileApiController extends Controller
{
    public function __construct(
        private FileService $fileService
    ) {}

    /**
     * List files
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $folderId = $request->get('folder_id');
        
        $files = $this->fileService->getFilesByFolder($user->id, $folderId);

        return response()->json($files);
    }

    /**
     * Upload file
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $user = auth()->user();
        $file = $request->file('file');
        
        // Validate upload
        $errors = $this->fileService->validateUpload($file, $user->id);
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        try {
            $uploadedFile = $this->fileService->upload(
                $file,
                $user->id,
                $request->folder_id
            );

            return response()->json([
                'message' => 'File uploaded successfully',
                'file' => $uploadedFile,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file details
     */
    public function show(File $file)
    {
        Gate::authorize('view', $file);

        return response()->json($file);
    }

    /**
     * Download file
     */
    public function download(File $file)
    {
        Gate::authorize('view', $file);

        return $this->fileService->download($file);
    }

    /**
     * Delete file
     */
    public function destroy(File $file)
    {
        Gate::authorize('delete', $file);

        try {
            $this->fileService->delete($file);
            return response()->json(['message' => 'File deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search files
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $user = auth()->user();
        $files = $this->fileService->search($user->id, $request->q);

        return response()->json($files);
    }
}
