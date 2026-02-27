<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use App\Models\Semester;
use App\Services\Akademik\GradeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InputNilaiController extends Controller
{
    protected $gradeService;

    public function __construct(GradeService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    /**
     * Tampilkan daftar kelas yang diampu dosen pada semester aktif.
     */
    public function index()
    {
        $dosen = auth()->user()->dosen;
        if (!$dosen) {
            return redirect()->route('dashboard')->with('error', 'Data dosen tidak tersedia.');
        }

        $activeSemester = Semester::where('a_periode_aktif', 1)->first();
        if (!$activeSemester) {
            return redirect()->route('dashboard')->with('error', 'Semester aktif belum ditentukan.');
        }

        $kelas = KelasKuliah::where('id_semester', $activeSemester->id_semester)
            ->whereHas('dosenPengajar', function ($q) use ($dosen) {
                $q->where(function ($query) use ($dosen) {
                    $query->where('id_dosen', $dosen->id)
                        ->orWhere('id_dosen_alias_lokal', $dosen->id);
                });
            })
            ->with([
                'mataKuliah',
                'programStudi',
                'dosenPengajar' => function ($q) use ($dosen) {
                    $q->where('id_dosen', $dosen->id)
                        ->orWhere('id_dosen_alias_lokal', $dosen->id);
                }
            ])
            ->withCount(['pesertaKelasKuliah as total_mahasiswa'])
            ->withCount([
                'pesertaKelasKuliah as terisi_count' => function ($q) {
                    $q->whereNotNull('nilai_angka');
                }
            ])
            ->get();

        return view('dosen.nilai.index', compact('kelas', 'activeSemester'));
    }

    /**
     * Halaman input nilai untuk mahasiswa di satu kelas.
     */
    public function show($id)
    {
        $dosen = auth()->user()->dosen;
        $kelas = KelasKuliah::where('id_kelas_kuliah', $id)
            ->whereHas('dosenPengajar', function ($q) use ($dosen) {
                $q->where(function ($query) use ($dosen) {
                    $query->where('id_dosen', $dosen->id)
                        ->orWhere('id_dosen_alias_lokal', $dosen->id);
                });
            })
            ->with(['mataKuliah', 'programStudi', 'semester'])
            ->firstOrFail();

        $peserta = PesertaKelasKuliah::where('id_kelas_kuliah', $id)
            ->with(['riwayatPendidikan.mahasiswa'])
            ->get()
            ->sortBy(function ($p) {
                return $p->riwayatPendidikan->mahasiswa->nama_mahasiswa ?? '';
            });

        return view('dosen.nilai.show', compact('kelas', 'peserta'));
    }

    /**
     * AJAX endpoint untuk konversi nilai secara real-time.
     */
    public function convert(Request $request)
    {
        $request->validate([
            'nilai_angka' => 'required|numeric|min:0|max:100',
            'id_prodi' => 'required',
        ]);

        $result = $this->gradeService->convertToGrade(
            $request->id_prodi,
            (float) $request->nilai_angka
        );

        return response()->json($result ?: [
            'nilai_huruf' => '-',
            'nilai_indeks' => '0.00'
        ]);
    }

    /**
     * Simpan nilai mahasiswa (Bulk).
     */
    public function store(Request $request, $id)
    {
        $dosen = auth()->user()->dosen;
        $kelas = KelasKuliah::where('id_kelas_kuliah', $id)
            ->whereHas('dosenPengajar', function ($q) use ($dosen) {
                $q->where(function ($query) use ($dosen) {
                    $query->where('id_dosen', $dosen->id)
                        ->orWhere('id_dosen_alias_lokal', $dosen->id);
                });
            })
            ->firstOrFail();

        $data = $request->validate([
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            foreach ($data['nilai'] as $pesertaId => $nilaiAngka) {
                if ($nilaiAngka === null)
                    continue;

                $peserta = PesertaKelasKuliah::with('riwayatPendidikan')
                    ->where('id_kelas_kuliah', $id)
                    ->where('id', $pesertaId)
                    ->first();

                if ($peserta) {
                    // Cari konversi grade (Prioritas pake Prodi Mhs buat Lintas Prodi)
                    $prodiId = $peserta->riwayatPendidikan->id_prodi ?? $kelas->id_prodi;
                    $grade = $this->gradeService->convertToGrade($prodiId, (float) $nilaiAngka);

                    if ($grade) {
                        $peserta->update([
                            'nilai_angka' => $nilaiAngka,
                            'nilai_huruf' => $grade['nilai_huruf'],
                            'nilai_indeks' => $grade['nilai_indeks'],
                            'status_sinkronisasi' => 'updated_local',
                        ]);

                        Log::info("CRUD_UPDATE: [Nilai Mahasiswa] - Berhasil update", [
                            'peserta_id' => $pesertaId,
                            'nama' => $peserta->riwayatPendidikan->mahasiswa->nama_mahasiswa ?? 'Unknown',
                            'nilai' => $nilaiAngka,
                            'huruf' => $grade['nilai_huruf']
                        ]);
                    } else {
                        Log::warning("CRUD_UPDATE_FAILED: [Nilai Mahasiswa] - Gagal konversi grade", [
                            'peserta_id' => $pesertaId,
                            'prodi_id' => $prodiId,
                            'nilai' => $nilaiAngka
                        ]);
                    }
                }
            }

            DB::commit();

            Log::info("CRUD_UPDATE: [Nilai Mahasiswa] diubah oleh dosen", [
                'kelas_id' => $id,
                'dosen_id' => $dosen->id
            ]);

            return redirect()->back()->with('success', 'Nilai mahasiswa berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SYSTEM_ERROR: Gagal menyimpan nilai mahasiswa", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menyimpan nilai.');
        }
    }
}
