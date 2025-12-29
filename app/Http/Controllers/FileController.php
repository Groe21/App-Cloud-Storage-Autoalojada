<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FileController extends Controller
{
    public function __construct(
        private FileService $fileService
    ) {}

    /**
     * Upload file
     */
    public function upload(Request $request)
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
            return back()->with('error', implode(' ', $errors));
        }

        try {
            $uploadedFile = $this->fileService->upload(
                $file,
                $user->id,
                $request->folder_id
            );

            return back()->with('success', 'Archivo subido exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al subir el archivo: ' . $e->getMessage());
        }
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
            return back()->with('success', 'Archivo eliminado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el archivo: ' . $e->getMessage());
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

        return view('files.search', compact('files'));
    }
}
