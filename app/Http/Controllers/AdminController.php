<?php

namespace App\Http\Controllers;

use App\Services\ServerMetricsService;
use App\Repositories\UserRepository;
use App\Repositories\FileRepository;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct(
        private ServerMetricsService $metricsService,
        private UserRepository $userRepository,
        private FileRepository $fileRepository
    ) {
    }

    /**
     * Show admin dashboard
     */
    public function index()
    {
        // Get server metrics
        $metrics = $this->metricsService->getLatest();
        
        // Get user statistics
        $totalUsers = $this->userRepository->getTotalCount();
        $activeUsers = $this->userRepository->getActiveCount();
        $totalStorageUsed = $this->userRepository->getTotalStorageUsed();
        $totalStorageQuota = $this->userRepository->getTotalStorageQuota();
        
        // Get recent activity
        $recentActivity = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'metrics',
            'totalUsers',
            'activeUsers',
            'totalStorageUsed',
            'totalStorageQuota',
            'recentActivity'
        ));
    }

    /**
     * Show users list
     */
    public function users()
    {
        $users = $this->userRepository->getAll();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show create user form
     */
    public function createUser()
    {
        return view('admin.users.create');
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,user',
            'quota_gb' => 'required|numeric|min:1',
        ]);

        try {
            $user = $this->userRepository->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Update quota
            $quotaBytes = $request->quota_gb * 1024 * 1024 * 1024;
            $this->userRepository->updateQuota($user, $quotaBytes);

            ActivityLog::log('create_user', 'User', $user->id, "Created user: {$user->email}");

            return redirect()->route('admin.users')->with('success', 'Usuario creado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear usuario: ' . $e->getMessage());
        }
    }

    /**
     * Show edit user form
     */
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user',
            'quota_gb' => 'required|numeric|min:1',
            'is_active' => 'required|boolean',
        ]);

        try {
            $this->userRepository->update($user, [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'is_active' => $request->is_active,
            ]);

            // Update quota
            $quotaBytes = $request->quota_gb * 1024 * 1024 * 1024;
            $this->userRepository->updateQuota($user, $quotaBytes);

            ActivityLog::log('update_user', 'User', $user->id, "Updated user: {$user->email}");

            return redirect()->route('admin.users')->with('success', 'Usuario actualizado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Delete user
     */
    public function destroyUser(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta');
        }

        try {
            $email = $user->email;
            $this->userRepository->delete($user);

            ActivityLog::log('delete_user', 'User', $user->id, "Deleted user: {$email}");

            return redirect()->route('admin.users')->with('success', 'Usuario eliminado exitosamente');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Show activity logs
     */
    public function activityLogs()
    {
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.activity-logs', compact('logs'));
    }

    /**
     * Show server metrics
     */
    public function metrics()
    {
        $currentMetrics = $this->metricsService->getLatest();
        $historicalMetrics = $this->metricsService->getHistorical(24);

        return view('admin.metrics', compact('currentMetrics', 'historicalMetrics'));
    }
}
