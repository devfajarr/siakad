<?php

namespace App\Http\Controllers\Admin\Jabatan;

use App\Http\Controllers\Controller;
use App\Models\Personalia;
use App\Models\Dosen;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PersonaliaController extends Controller
{
    public function index()
    {
        Log::info("SYNC_PULL: Mengakses daftar Manajemen Personalia", ['endpoint' => route('admin.personalia.index')]);

        $personalias = Personalia::with(['dosen', 'pegawai'])->latest()->get();
        $dosens = Dosen::orderBy('nama_lengkap')->get();
        $pegawais = Pegawai::orderBy('nama_lengkap')->get();

        return view('admin.jabatan.personalia.index', compact('personalias', 'dosens', 'pegawais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipe_user' => 'required|in:dosen,pegawai',
            'id_dosen' => 'required_if:tipe_user,dosen|nullable|exists:dosens,id|unique:personalias,id_dosen',
            'id_pegawai' => 'required_if:tipe_user,pegawai|nullable|exists:pegawais,id|unique:personalias,id_pegawai',
        ], [
            'id_dosen.unique' => 'Dosen ini sudah menjabat.',
            'id_pegawai.unique' => 'Pegawai ini sudah menjabat.'
        ]);

        try {
            $data = [
                'is_active' => true,
                'id_dosen' => $request->tipe_user === 'dosen' ? $request->id_dosen : null,
                'id_pegawai' => $request->tipe_user === 'pegawai' ? $request->id_pegawai : null,
            ];
            $model = Personalia::create($data);

            Log::info("CRUD_CREATE: User dicalonkan menjadi Jabatan Personalia", ['id' => $model->id, 'data' => $data]);
            return back()->with('success', 'Jabatan berhasil ditunjuk.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menambah Jabatan", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function update(Request $request, Personalia $personalia)
    {
        $request->validate(['is_active' => 'required|boolean']);
        try {
            $personalia->update(['is_active' => $request->is_active]);
            Log::info("CRUD_UPDATE: Status Jabatan ID {$personalia->id} diubah", ['id' => $personalia->id, 'changes' => $request->all()]);
            return back()->with('success', 'Status berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal mengupdate status", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function destroy(Personalia $personalia)
    {
        try {
            $personalia->delete();
            Log::warning("CRUD_DELETE: Jabatan dihapus/soft-delete", ['id' => $personalia->id]);
            return back()->with('success', 'Jabatan berhasil dicabut.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menghapus Jabatan", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
}
