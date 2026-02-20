<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::with(['profileable', 'roles']);

        // Search by username or name (including profile name)
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        if ($request->has('role')) {
            $query->role($request->role);
        }

        $users = $query->paginate($request->per_page ?? 15);

        return UserResource::collection($users);
    }

    /**
     * Display the specified user.
     */
    public function show($id)
    {
        $user = User::with(['profileable', 'roles', 'permissions'])->findOrFail($id);

        return new UserResource($user);
    }

    /**
     * Update the user's roles.
     */
    public function updateRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::findOrFail($id);

        // Sync roles (replace existing)
        $user->syncRoles($request->roles);

        return response()->json([
            'message' => 'User roles updated successfully.',
            'user' => new UserResource($user->load('roles')),
        ]);
    }

    /**
     * Trigger manual user sync.
     */
    public function triggerSync(Request $request, UserSyncService $syncService)
    {
        $type = $request->query('type', 'all');

        try {
            if ($type === 'mahasiswa' || $type === 'all') {
                $syncService->syncMahasiswa();
            }

            if ($type === 'dosen' || $type === 'all') {
                $syncService->syncDosen();
            }

            return response()->json([
                'message' => 'User sync triggered successfully for type: ' . $type
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
