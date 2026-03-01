<?php

namespace App\Http\Controllers\Admin\Jabatan;

use App\Http\Controllers\Controller;
use App\Models\Direktur;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DirekturController extends Controller
{
    public function index()
    {
        Log::info("SYNC_PULL: Mengakses daftar Manajemen Direktur", ['endpoint' => route('admin.direktur.index')]);

        $direkturs = Direktur::with(['dosen'])->latest()->get();
        $dosens = Dosen::orderBy('nama_lengkap')->get();

        return view('admin.jabatan.direktur.index', compact('direkturs', 'dosens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_dosen' => 'required|exists:dosens,id|unique:direkturs,id_dosen',
        ], [
            'id_dosen.unique' => 'Dosen ini sudah menjabat sebagai Direktur.'
        ]);

        try {
            $data = [
                'is_active' => true,
                'id_dosen' => $request->id_dosen,
            ];
            $model = Direktur::create($data);

            Log::info("CRUD_CREATE: User dicalonkan menjadi Jabatan Direktur", ['id' => $model->id, 'data' => $data]);
            return back()->with('success', 'Jabatan berhasil ditunjuk.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menambah Jabatan", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function update(Request $request, Direktur $direktur)
    {
        $request->validate(['is_active' => 'required|boolean']);
        try {
            $direktur->update(['is_active' => $request->is_active]);
            Log::info("CRUD_UPDATE: Status Jabatan ID {$direktur->id} diubah", ['id' => $direktur->id, 'changes' => $request->all()]);
            return back()->with('success', 'Status berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal mengupdate status", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function destroy(Direktur $direktur)
    {
        try {
            $direktur->delete();
            Log::warning("CRUD_DELETE: Jabatan dihapus/soft-delete", ['id' => $direktur->id]);
            return back()->with('success', 'Jabatan berhasil dicabut.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menghapus Jabatan", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}
