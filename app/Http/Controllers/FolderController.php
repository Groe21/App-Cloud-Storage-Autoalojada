<?php

namespace App\Http\Controllers;

use App\Services\FolderService;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FolderController extends Controller
{
    public function __construct(
        private FolderService $folderService
    ) {}

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

            return back()->with('success', 'Carpeta creada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
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
            return back()->with('success', 'Carpeta eliminada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la carpeta: ' . $e->getMessage());
        }
    }

    /**
     * Rename folder
     */
    public function update(Request $request, Folder $folder)
    {
        Gate::authorize('update', $folder);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $this->folderService->rename($folder, $request->name);
            return back()->with('success', 'Carpeta renombrada exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
