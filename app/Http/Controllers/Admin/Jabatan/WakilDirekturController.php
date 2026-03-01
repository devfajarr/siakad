<?php

namespace App\Http\Controllers\Admin\Jabatan;

use App\Http\Controllers\Controller;
use App\Models\WakilDirektur;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WakilDirekturController extends Controller
{
    public function index()
    {
        Log::info("SYNC_PULL: Mengakses daftar Manajemen Wakil Direktur", ['endpoint' => route('admin.wakil-direktur.index')]);

        $wakil_direkturs = WakilDirektur::with(['dosen'])->latest()->get();
        $dosens = Dosen::orderBy('nama_lengkap')->get();

        return view('admin.jabatan.wakil_direktur.index', compact('wakil_direkturs', 'dosens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_dosen' => 'required|exists:dosens,id',
            'tipe_wadir' => 'required|integer|in:1,2,3'
        ]);

        $exists = WakilDirektur::where('id_dosen', $request->id_dosen)->where('tipe_wadir', $request->tipe_wadir)->exists();
        if ($exists) {
            return back()->with('error', 'Dosen ini sudah menjabat sebagai Wakil Direktur bidang tersebut.');
        }

        try {
            $data = [
                'is_active' => true,
                'id_dosen' => $request->id_dosen,
                'tipe_wadir' => $request->tipe_wadir,
            ];
            $model = WakilDirektur::create($data);

            Log::info("CRUD_CREATE: User dicalonkan menjadi Jabatan Wakil Direktur", ['id' => $model->id, 'data' => $data]);
            return back()->with('success', 'Jabatan berhasil ditunjuk.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menambah Jabatan", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function update(Request $request, WakilDirektur $wakil_direktur)
    {
        $request->validate(['is_active' => 'required|boolean']);
        try {
            $wakil_direktur->update(['is_active' => $request->is_active]);
            Log::info("CRUD_UPDATE: Status Jabatan ID {$wakil_direktur->id} diubah", ['id' => $wakil_direktur->id, 'changes' => $request->all()]);
            return back()->with('success', 'Status berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal mengupdate status", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function destroy(WakilDirektur $wakil_direktur)
    {
        try {
            $wakil_direktur->delete();
            Log::warning("CRUD_DELETE: Jabatan dihapus/soft-delete", ['id' => $wakil_direktur->id]);
            return back()->with('success', 'Jabatan berhasil dicabut.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menghapus Jabatan", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}
