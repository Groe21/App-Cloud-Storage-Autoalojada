<?php

use App\Http\Controllers\Api\FileApiController;
use App\Http\Controllers\Api\FolderApiController;
use App\Http\Controllers\Api\MetricsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authenticated API routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Files
    Route::prefix('files')->group(function () {
        Route::get('/', [FileApiController::class, 'index']);
        Route::post('/', [FileApiController::class, 'store'])->middleware('check.storage.quota');
        Route::get('/{file}', [FileApiController::class, 'show']);
        Route::get('/{file}/download', [FileApiController::class, 'download']);
        Route::delete('/{file}', [FileApiController::class, 'destroy']);
        Route::get('/search', [FileApiController::class, 'search']);
    });
    
    // Folders
    Route::prefix('folders')->group(function () {
        Route::get('/', [FolderApiController::class, 'index']);
        Route::post('/', [FolderApiController::class, 'store']);
        Route::get('/tree', [FolderApiController::class, 'tree']);
        Route::get('/{folder}', [FolderApiController::class, 'show']);
        Route::put('/{folder}', [FolderApiController::class, 'update']);
        Route::delete('/{folder}', [FolderApiController::class, 'destroy']);
    });
    
    // Metrics (admin only)
    Route::prefix('metrics')->middleware('admin')->group(function () {
        Route::get('/server', [MetricsApiController::class, 'server']);
        Route::get('/storage', [MetricsApiController::class, 'storage']);
        Route::get('/historical', [MetricsApiController::class, 'historical']);
    });
});
