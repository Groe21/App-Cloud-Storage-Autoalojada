<?php

namespace App\Http\Controllers;

use App\Services\FileService;
use App\Services\FolderService;
use App\Repositories\FileRepository;
use App\Repositories\FolderRepository;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private FileService $fileService,
        private FolderService $folderService,
        private FileRepository $fileRepository,
        private FolderRepository $folderRepository
    ) {}

    /**
     * Show the dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $folderId = $request->get('folder');
        
        // Get folders
        $folders = $this->folderService->getFoldersByUser($user->id, $folderId);
        
        // Get files
        $files = $this->fileService->getFilesByFolder($user->id, $folderId);
        
        // Get breadcrumbs
        $breadcrumbs = $this->folderService->getBreadcrumbs($folderId);
        
        // Get storage info
        $storageQuota = $user->storageQuota;
        
        // Get recent files
        $recentFiles = $this->fileService->getRecent($user->id, 5);
        
        return view('dashboard', compact(
            'folders',
            'files',
            'breadcrumbs',
            'storageQuota',
            'recentFiles',
            'folderId'
        ));
    }
}
