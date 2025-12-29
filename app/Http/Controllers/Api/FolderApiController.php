<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FolderService;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FolderApiController extends Controller
{
    public function __construct(
        private FolderService $folderService
    ) {}

    /**
     * List folders
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $parentId = $request->get('parent_id');
        
        $folders = $this->folderService->getFoldersByUser($user->id, $parentId);

        return response()->json($folders);
    }

    /**
     * Create folder
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $user = auth()->user();

        try {
            $folder = $this->folderService->create(
                $user->id,
                $request->name,
                $request->parent_id
            );

            return response()->json([
                'message' => 'Folder created successfully',
                'folder' => $folder,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get folder details
     */
    public function show(Folder $folder)
    {
        Gate::authorize('view', $folder);

        return response()->json($folder->load(['children', 'files']));
    }

    /**
     * Update folder
     */
    public function update(Request $request, Folder $folder)
    {
        Gate::authorize('update', $folder);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $this->folderService->rename($folder, $request->name);
            return response()->json([
                'message' => 'Folder renamed successfully',
                'folder' => $folder->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete folder
     */
    public function destroy(Folder $folder)
    {
        Gate::authorize('delete', $folder);

        try {
            $this->folderService->delete($folder);
            return response()->json(['message' => 'Folder deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get folder tree
     */
    public function tree()
    {
        $user = auth()->user();
        $tree = $this->folderService->getFolderTree($user->id);

        return response()->json($tree);
    }
}
