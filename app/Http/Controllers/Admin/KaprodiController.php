<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kaprodi;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KaprodiController extends Controller
{
    /**
     * Menampilkan daftar Kaprodi Aktif.
     */
    public function index(Request $request)
    {
        Log::info("SYNC_PULL: Mengakses daftar Manajemen Kaprodi");

        $query = Kaprodi::with(['dosen', 'prodi']);

        $kaprodis = $query->latest()->get();

        // Data Prodi yang belum memiliki Kaprodi (untuk modal tambah)
        $availableProdis = ProgramStudi::whereDoesntHave('kaprodi')
            ->orderBy('nama_program_studi')
            ->get();

        return view('admin.kaprodi.index', compact('kaprodis', 'availableProdis'));
    }

    /**
     * Menyimpan data Kaprodi baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dosen_id' => 'required|exists:dosens,id',
            'id_prodi' => 'required|uuid|exists:program_studis,id_prodi|unique:kaprodis,id_prodi',
        ], [
            'id_prodi.unique' => 'Program Studi ini sudah memiliki Kaprodi aktif.'
        ]);

        try {
            $kaprodi = Kaprodi::create($request->all());

            Log::info("CRUD_CREATE: Dosen ID {$request->dosen_id} diangkat menjadi Kaprodi di Prodi ID {$request->id_prodi}", [
                'id' => $kaprodi->id
            ]);

            return back()->with('success', 'Kaprodi berhasil ditunjuk.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menambah Kaprodi", [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Memperbarui data Kaprodi (Pergantian Dosen).
     */
    public function update(Request $request, Kaprodi $kaprodi)
    {
        $request->validate([
            'dosen_id' => 'required|exists:dosens,id',
        ]);

        $oldDosenId = $kaprodi->dosen_id;

        try {
            $kaprodi->update($request->only('dosen_id'));

            Log::info("CRUD_UPDATE: Perubahan Kaprodi pada Prodi {$kaprodi->id_prodi}", [
                'old_dosen' => $oldDosenId,
                'new_dosen' => $request->dosen_id
            ]);

            return back()->with('success', 'Data Kaprodi berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal mengupdate Kaprodi", [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * Menghapus jabatan Kaprodi.
     */
    public function destroy(Kaprodi $kaprodi)
    {
        try {
            $prodiName = $kaprodi->prodi->nama_program_studi;
            $kaprodi->delete();

            Log::warning("CRUD_DELETE: Jabatan Kaprodi dihapus untuk Prodi {$prodiName}");

            return back()->with('success', 'Jabatan Kaprodi berhasil dicabut.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menghapus Kaprodi", [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    /**
     * API untuk pencarian dosen (Select2 AJAX).
     */
    public function searchDosen(Request $request)
    {
        $search = $request->q;

        $dosens = Dosen::where('nama', 'ilike', "%{$search}%")
            ->orWhere('nidn', 'ilike', "%{$search}%")
            ->orWhere('nip', 'ilike', "%{$search}%")
            ->limit(10)
            ->get()
            ->map(function ($dosen) {
                return [
                    'id' => $dosen->id,
                    'text' => $dosen->nama_admin_display . " - " . ($dosen->nidn ?? $dosen->nip ?? 'No NIDN')
                ];
            });

        return response()->json($dosens);
    }
}
