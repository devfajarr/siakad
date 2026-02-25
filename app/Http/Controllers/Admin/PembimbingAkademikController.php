<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PembimbingAkademik;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\RiwayatPendidikan;
use App\Models\ProgramStudi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembimbingAkademikController extends Controller
{
    public function index(Request $request)
    {
        $activeSemesterId = getActiveSemesterId();
        $selectedSemesterId = $request->id_semester ?: $activeSemesterId;

        $mappings = PembimbingAkademik::with(['prodi', 'dosen', 'semester'])
            ->where('id_semester', $selectedSemesterId);

        if ($request->id_prodi) {
            $mappings->where('id_prodi', $request->id_prodi);
        }

        $mappings = $mappings->get();

        $semesters = \App\Models\Semester::orderBy('id_semester', 'desc')->get();
        $prodis = ProgramStudi::orderBy('nama_program_studi')->get();

        $statistics = [
            'total_mappings' => $mappings->count(),
            'total_prodi_mapped' => $mappings->unique('id_prodi')->count(),
            'total_dosen_involved' => $mappings->unique('id_dosen')->count(),
        ];

        return view('admin.pembimbing-akademik.index', compact('mappings', 'semesters', 'prodis', 'selectedSemesterId', 'statistics'));
    }

    /**
     * Get Dosen by Prodi (Smart Filter).
     */
    public function getDosenByProdi($id_prodi)
    {
        // Tampilkan semua dosen aktif (abaikan id_prodi penugasan agar dosen lokal bisa dipilih)
        $dosens = Dosen::aktif()->get(['id', 'nama', 'nidn', 'nama_alias']);

        return response()->json($dosens);
    }

    /**
     * Store mapping PA (Collective).
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_prodi' => 'required|exists:program_studis,id_prodi',
            'id_semester' => 'required|exists:semesters,id_semester',
            'id_dosen' => 'required|exists:dosens,id',
        ]);

        $dosen = Dosen::findOrFail($request->id_dosen);

        try {
            // Check if this prodi/semester/dosen combo already exists
            $exists = PembimbingAkademik::where([
                'id_prodi' => $request->id_prodi,
                'id_semester' => $request->id_semester,
                'id_dosen' => $request->id_dosen
            ])->exists();

            if ($exists) {
                return response()->json(['message' => 'Data sudah ada.'], 422);
            }

            PembimbingAkademik::create([
                'id_prodi' => $request->id_prodi,
                'id_semester' => $request->id_semester,
                'id_dosen' => $request->id_dosen,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            Log::info("CRUD_CREATE: Pembimbing Akademik Prodi-Semester diatur", [
                'prodi' => $request->id_prodi,
                'semester' => $request->id_semester,
                'dosen' => $dosen->nama
            ]);

            return response()->json(['message' => 'Berhasil menambahkan Dosen PA Kolektif.']);
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal simpan Pembimbing Akademik Kolektif", ['message' => $e->getMessage()]);
            return response()->json(['message' => 'Kesalahan sistem alur database.'], 500);
        }
    }

    /**
     * Copy Data PA from Semester to Semester.
     */
    public function copySemester(Request $request)
    {
        $request->validate([
            'from_semester' => 'required|exists:semesters,id_semester',
            'to_semester' => 'required|exists:semesters,id_semester|different:from_semester',
        ]);

        $sourceMappings = PembimbingAkademik::where('id_semester', $request->from_semester)->get();

        if ($sourceMappings->isEmpty()) {
            return back()->with('error', 'Tidak ada data di semester asal.');
        }

        DB::beginTransaction();
        try {
            foreach ($sourceMappings as $mapping) {
                PembimbingAkademik::updateOrCreate(
                    [
                        'id_prodi' => $mapping->id_prodi,
                        'id_semester' => $request->to_semester,
                        'id_dosen' => $mapping->id_dosen
                    ],
                    [
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]
                );
            }
            DB::commit();
            Log::info("SYNC_PUSH: Salin Data PA Semester", ['from' => $request->from_semester, 'to' => $request->to_semester]);
            return back()->with('success', "Berhasil menyalin {$sourceMappings->count()} data PA.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SYSTEM_ERROR: Gagal Salin PA Semester", ['message' => $e->getMessage()]);
            return back()->with('error', 'Gagal menyalin data.');
        }
    }

    public function destroy($id)
    {
        try {
            $mapping = PembimbingAkademik::findOrFail($id);
            Log::warning("CRUD_DELETE: Mapping PA dihapus", ['id' => $id]);
            $mapping->delete();
            return back()->with('success', 'Berhasil menghapus mapping PA.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data.');
        }
    }
}
