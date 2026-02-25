<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class StaffUserController extends Controller
{
    /**
     * Menampilkan daftar user (Staf/Dosen) dengan filter pencarian dan pengecualian mahasiswa.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        Log::info("SYNC_PULL: Mengakses daftar Manajemen User Staf");

        $query = User::with(['roles', 'dosen'])
            ->whereDoesntHave('mahasiswa'); // Sesuai aturan: kecualikan mahasiswa

        // Fitur Pencarian (Search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhereHas('dosen', function ($dq) use ($search) {
                        $dq->where('nama', 'like', "%{$search}%");
                    });
            });
        }

        // Filter By Role jika dipilih
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->paginate(15)->withQueryString();
        $allRoles = Role::where('name', '!=', 'Mahasiswa')->pluck('name', 'id');

        return view('admin.manajemen_dosen.index', compact('users', 'allRoles'));
    }

    /**
     * Memperbarui jabatan (Role) user.
     * 
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'required|array'
        ]);

        $oldRoles = $user->getRoleNames()->toArray();

        try {
            // Spatie otomatis melakukan sinkronisasi
            $user->syncRoles($request->roles);

            $newRoles = $user->getRoleNames()->toArray();

            Log::info("CRUD_UPDATE: Berhasil memperbarui jabatan user", [
                'id' => $user->id,
                'username' => $user->username,
                'old_roles' => $oldRoles,
                'new_roles' => $newRoles
            ]);

            return back()->with('success', 'Jabatan (Role) berhasil diperbarui untuk user: ' . $user->name);
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal memperbarui jabatan user", [
                'id' => $user->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Terjadi kesalahan sistem saat memperbarui jabatan.');
        }
    }
}
