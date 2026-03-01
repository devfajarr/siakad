<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bpmi;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BpmiController extends Controller
{
    /**
     * Menampilkan daftar anggota BPMI Aktif.
     */
    public function index(Request $request)
    {
        Log::info("SYNC_PULL: Mengakses daftar Manajemen Anggota BPMI");

        $query = Bpmi::with(['dosen.akun']);

        $bpmis = $query->latest()->get();

        return view('admin.bpmi.index', compact('bpmis'));
    }

    /**
     * Menyimpan data anggota BPMI baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_dosen' => 'required|integer|exists:dosens,id|unique:bpmis,id_dosen',
            'sk_tugas' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ], [
            'id_dosen.unique' => 'Dosen tersebut sudah terdaftar sebagai anggota BPMI.'
        ]);

        try {
            $bpmi = Bpmi::create([
                'id_dosen' => $request->id_dosen,
                'sk_tugas' => $request->sk_tugas,
                'is_active' => $request->is_active ?? true
            ]);

            Log::info("CRUD_CREATE: Dosen ID {$request->id_dosen} ditunjuk menjadi anggota BPMI", [
                'id' => $bpmi->id,
                'sk' => $bpmi->sk_tugas
            ]);

            return back()->with('success', 'Anggota BPMI berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menambah anggota BPMI", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan data.');
        }
    }

    /**
     * Memberhentikan anggota BPMI (Delete Record).
     */
    public function destroy(Bpmi $bpmi)
    {
        try {
            $dosenName = $bpmi->dosen->nama_admin_display ?? 'Unknown';
            $bpmi->delete();

            Log::warning("CRUD_DELETE: Keanggotaan BPMI dicabut untuk dosen {$dosenName}");

            return back()->with('success', 'Keanggotaan BPMI berhasil diberhentikan.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menghapus anggota BPMI", [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem saat menghapus.');
        }
    }

    /**
     * API untuk pencarian dosen via Select2 khusus BPMI Addition
     */
    public function searchDosen(Request $request)
    {
        $search = $request->q;

        // Cari dosen yang BELUM ada di tabel bpmis
        $dosens = Dosen::whereDoesntHave('bpmi') // Relasinya belum ada kita query sub-exist
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('nama', 'ilike', "%{$search}%")
                        ->orWhere('nidn', 'ilike', "%{$search}%")
                        ->orWhere('nip', 'ilike', "%{$search}%");
                });
            })
            ->limit(15)
            ->get()
            ->map(function ($dosen) {
                return [
                    'id' => $dosen->id,
                    'text' => $dosen->nama_admin_display . " - " . ($dosen->nidn ?? $dosen->nip ?? 'Tanpa NIDN')
                ];
            });

        return response()->json($dosens);
    }
}
