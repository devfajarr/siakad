<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\ProgramStudi;
use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use App\Services\Akademik\RekapNilaiService;
use App\Services\Akademik\GradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RekapNilaiController extends Controller
{
    protected RekapNilaiService $rekapService;
    protected GradeService $gradeService;

    public function __construct(RekapNilaiService $rekapService, GradeService $gradeService)
    {
        $this->rekapService = $rekapService;
        $this->gradeService = $gradeService;
    }

    /**
     * Menampilkan dashboard rekapitulasi progres per Prodi.
     */
    public function index(Request $request)
    {
        $semesterId = $request->get('semester_id', getActiveSemesterId());
        $activeSemester = getActiveSemester();
        $semesters = Semester::orderBy('id_semester', 'desc')->get();

        $selectedSemester = $semesters->where('id_semester', $semesterId)->first();

        $prodiStats = $this->rekapService->getProdiProgress($semesterId);

        return view('admin.nilai.rekap.index', compact('prodiStats', 'semesters', 'semesterId', 'activeSemester', 'selectedSemester'));
    }

    /**
     * Menampilkan detail daftar kelas per Prodi.
     */
    public function show(Request $request, $id_prodi)
    {
        $prodi = ProgramStudi::where('id_prodi', $id_prodi)->firstOrFail();

        $activeSemester = Semester::where('a_periode_aktif', '1')->first()
            ?? Semester::orderBy('id_semester', 'desc')->first();

        $semesterId = $request->get('semester_id', $activeSemester->id_semester);
        $selectedSemester = Semester::where('id_semester', $semesterId)->first() ?? $activeSemester;

        $kelas = $this->rekapService->getClassDetailsByProdi($id_prodi, $semesterId);

        return view('admin.nilai.rekap.show', compact('prodi', 'kelas', 'semesterId', 'activeSemester', 'selectedSemester'));
    }

    /**
     * Kunci / Buka kunci nilai kelas.
     */
    public function toggleLock(Request $request, $id_kelas)
    {
        $kelas = KelasKuliah::where('id_kelas_kuliah', $id_kelas)->firstOrFail();

        $isLocked = $request->action === 'lock';

        $kelas->update([
            'is_locked' => $isLocked,
            'locked_at' => $isLocked ? now() : null
        ]);

        $statusLabel = $isLocked ? 'dikunci' : 'dibuka';

        Log::info("CRUD_UPDATE: [Rekap Nilai] Kelas {$statusLabel} oleh admin", [
            'id_kelas' => $id_kelas,
            'admin_id' => auth()->id()
        ]);

        return redirect()->back()->with('success', "Status pengisian nilai kelas berhasil {$statusLabel}.");
    }

    /**
     * Kunci massal per Prodi.
     */
    public function bulkLock(Request $request)
    {
        $validated = $request->validate([
            'id_prodi' => 'required',
            'id_semester' => 'required',
            'action' => 'required|in:lock,unlock'
        ]);

        $isLocked = $validated['action'] === 'lock';

        KelasKuliah::where('id_prodi', $validated['id_prodi'])
            ->where('id_semester', $validated['id_semester'])
            ->update([
                'is_locked' => $isLocked,
                'locked_at' => $isLocked ? now() : null
            ]);

        $statusLabel = $isLocked ? 'dikunci' : 'dibuka';

        Log::info("CRUD_UPDATE: [Rekap Nilai] Kunci massal prodi {$statusLabel} oleh admin", [
            'prodi_id' => $validated['id_prodi'],
            'semester_id' => $validated['id_semester'],
            'admin_id' => auth()->id()
        ]);

        return redirect()->back()->with('success', "Seluruh kelas pada prodi ini berhasil {$statusLabel}.");
    }

    /**
     * Tampilkan halaman override nilai (Admin).
     */
    public function editNilai($id_kelas)
    {
        $kelas = KelasKuliah::where('id_kelas_kuliah', $id_kelas)
            ->with(['mataKuliah', 'programStudi', 'semester'])
            ->firstOrFail();

        $peserta = PesertaKelasKuliah::where('id_kelas_kuliah', $id_kelas)
            ->with(['riwayatPendidikan.mahasiswa'])
            ->withCount([
                'riwayatPendidikan as total_hadir' => function ($q) use ($id_kelas) {
                    $q->whereHas('presensiMahasiswas', function ($q) use ($id_kelas) {
                        $q->where('status_kehadiran', 'H')
                            ->whereHas('pertemuan', function ($q) use ($id_kelas) {
                                $q->where('id_kelas_kuliah', $id_kelas);
                            });
                    });
                }
            ])
            ->get()
            ->sortBy(function ($p) {
                return $p->riwayatPendidikan->mahasiswa->nama_mahasiswa ?? '';
            });

        return view('admin.nilai.rekap.override', compact('kelas', 'peserta'));
    }

    /**
     * Simpan override nilai (Admin).
     */
    public function storeOverride(Request $request, $id_kelas)
    {
        $kelas = KelasKuliah::where('id_kelas_kuliah', $id_kelas)->firstOrFail();
        $adminName = auth()->user()->name ?? 'Administrator';

        $data = $request->validate([
            'tugas1' => 'nullable|array',
            'tugas2' => 'nullable|array',
            'tugas3' => 'nullable|array',
            'tugas4' => 'nullable|array',
            'tugas5' => 'nullable|array',
            'aktif' => 'nullable|array',
            'etika' => 'nullable|array',
            'uts' => 'nullable|array',
            'uas' => 'nullable|array',
            'tugas1.*' => 'nullable|numeric|min:0|max:100',
            'tugas2.*' => 'nullable|numeric|min:0|max:100',
            'tugas3.*' => 'nullable|numeric|min:0|max:100',
            'tugas4.*' => 'nullable|numeric|min:0|max:100',
            'tugas5.*' => 'nullable|numeric|min:0|max:100',
            'aktif.*' => 'nullable|numeric|min:0|max:100',
            'etika.*' => 'nullable|numeric|min:0|max:100',
            'uts.*' => 'nullable|numeric|min:0|max:100',
            'uas.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $targetPertemuan = config('academic.target_pertemuan', 14);

        try {
            DB::beginTransaction();

            // Ambil semua ID peserta dari request (UTS sebagai anchor)
            $pesertaIds = array_keys($data['uts'] ?? []);

            foreach ($pesertaIds as $pesertaId) {
                $peserta = PesertaKelasKuliah::with(['riwayatPendidikan.mahasiswa'])
                    ->withCount([
                        'riwayatPendidikan as total_hadir' => function ($q) use ($id_kelas) {
                            $q->whereHas('presensiMahasiswas', function ($q) use ($id_kelas) {
                                $q->where('status_kehadiran', 'H')
                                    ->whereHas('pertemuan', function ($q) use ($id_kelas) {
                                        $q->where('id_kelas_kuliah', $id_kelas);
                                    });
                            });
                        }
                    ])
                    ->where('id_kelas_kuliah', $id_kelas)
                    ->where('id', $pesertaId)
                    ->first();

                if ($peserta) {
                    $components = [
                        'tugas1' => $data['tugas1'][$pesertaId] ?? 0,
                        'tugas2' => $data['tugas2'][$pesertaId] ?? 0,
                        'tugas3' => $data['tugas3'][$pesertaId] ?? 0,
                        'tugas4' => $data['tugas4'][$pesertaId] ?? 0,
                        'tugas5' => $data['tugas5'][$pesertaId] ?? 0,
                        'aktif' => $data['aktif'][$pesertaId] ?? 0,
                        'etika' => $data['etika'][$pesertaId] ?? 0,
                        'uts' => $data['uts'][$pesertaId] ?? 0,
                        'uas' => $data['uas'][$pesertaId] ?? 0,
                    ];

                    $nilaiAngka = $this->gradeService->calculateFinalScore(
                        $components,
                        $peserta->total_hadir,
                        $targetPertemuan
                    );

                    $oldNilai = $peserta->nilai_angka ?? 'N/A';
                    $prodiId = $peserta->riwayatPendidikan->id_prodi ?? $kelas->id_prodi;
                    $grade = $this->gradeService->convertToGrade($prodiId, (float) $nilaiAngka);

                    if ($grade) {
                        $peserta->update(array_merge($components, [
                            'nilai_angka' => $nilaiAngka,
                            'nilai_akhir' => $nilaiAngka,
                            'nilai_huruf' => $grade['nilai_huruf'],
                            'nilai_indeks' => $grade['nilai_indeks'],
                            'status_sinkronisasi' => 'updated_local',
                            'is_local_change' => true,
                            'sync_action' => $peserta->external_id ? 'update' : 'insert'
                        ]));

                        // Cek apakah ada perubahan nilai untuk logging audit trail
                        if ((float) $oldNilai != (float) $nilaiAngka) {
                            $nim = $peserta->riwayatPendidikan->nim ?? 'Unknown NIM';
                            Log::info("[OVERRIDE_NILAI_DETAILED] - Nilai Mahasiswa {$nim} diubah oleh Admin {$adminName}. Nilai Baru: {$nilaiAngka}", [
                                'admin_id' => auth()->id(),
                                'peserta_id' => $pesertaId,
                                'kelas_id' => $id_kelas
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.rekap-nilai.show', $kelas->id_prodi)
                ->with('success', 'Override nilai berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SYSTEM_ERROR: Gagal override nilai oleh admin", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat menyimpan override.');
        }
    }

    /**
     * AJAX endpoint untuk konversi nilai secara real-time (Admin).
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
}
