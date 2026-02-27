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
            ->withCount([
                'riwayatPendidikan as total_hadir' => function ($q) use ($id) {
                    $q->whereHas('presensiMahasiswas', function ($q) use ($id) {
                        $q->where('status_kehadiran', 'H')
                            ->whereHas('pertemuan', function ($q) use ($id) {
                                $q->where('id_kelas_kuliah', $id);
                            });
                    });
                }
            ])
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

        if ($kelas->is_locked) {
            return redirect()->back()->with('error', 'Gagal menyimpan. Kelas ini telah dikunci oleh bagian Akademik.');
        }

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

            // Ambil semua ID peserta dari request untuk diproses
            $pesertaIds = array_keys($data['uts'] ?? []); // UTS biasanya diisi paling terakhir/lengkap

            foreach ($pesertaIds as $pesertaId) {
                $peserta = PesertaKelasKuliah::with(['riwayatPendidikan'])
                    ->withCount([
                        'riwayatPendidikan as total_hadir' => function ($q) use ($id) {
                            $q->whereHas('presensiMahasiswas', function ($q) use ($id) {
                                $q->where('status_kehadiran', 'H')
                                    ->whereHas('pertemuan', function ($q) use ($id) {
                                        $q->where('id_kelas_kuliah', $id);
                                    });
                            });
                        }
                    ])
                    ->where('id_kelas_kuliah', $id)
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

                    $prodiId = $peserta->riwayatPendidikan->id_prodi ?? $kelas->id_prodi;
                    $grade = $this->gradeService->convertToGrade($prodiId, $nilaiAngka);

                    if ($grade) {
                        $peserta->update(array_merge($components, [
                            'nilai_angka' => $nilaiAngka,
                            'nilai_akhir' => $nilaiAngka,
                            'nilai_huruf' => $grade['nilai_huruf'],
                            'nilai_indeks' => $grade['nilai_indeks'],
                            'status_sinkronisasi' => 'updated_local',
                        ]));

                        Log::info("CRUD_UPDATE: [Nilai Mahasiswa Detailed] diupdate oleh dosen", [
                            'id' => $pesertaId,
                            'nilai_akhir' => $nilaiAngka,
                            'grade' => $grade['nilai_huruf']
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
