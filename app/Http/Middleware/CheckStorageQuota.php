<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckStorageQuota
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasFile('file')) {
            return $next($request);
        }

        $user = auth()->user();
        $file = $request->file('file');
        
        if (!$user->hasAvailableStorage($file->getSize())) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'No tienes suficiente espacio de almacenamiento disponible'
                ], 422);
            }
            
            return back()->with('error', 'No tienes suficiente espacio de almacenamiento disponible');
        }

        return $next($request);
    }
}
