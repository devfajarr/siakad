<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kuisioner;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class KuisionerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:BPMI|bpmi', except: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info("SYNC_PULL: Mengakses daftar Kuisioner BPMI");

        $semesters = Semester::orderBy('id_semester', 'desc')->get();
        $activeSemesterId = getActiveSemesterId();

        // Ambil filter semester dari request, default ke semester aktif
        $idSemester = $request->query('id_semester', $activeSemesterId);

        $kuisioners = Kuisioner::with('semester')
            ->where('id_semester', $idSemester)
            ->latest()
            ->get();

        return view('dosen.kuisioner.index', compact('kuisioners', 'semesters', 'idSemester'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'id_semester' => 'required|exists:semesters,id_semester',
            'target_ujian' => 'required|in:UTS,UAS',
            'tipe' => 'required|in:pelayanan,dosen',
        ]);

        try {
            $kuisioner = Kuisioner::create([
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'id_semester' => $request->id_semester,
                'target_ujian' => $request->target_ujian,
                'tipe' => $request->tipe,
                'status' => 'draft', // Selalu draft saat awal dibuat
            ]);

            Log::info("CRUD_CREATE: [Kuisioner] Form {$kuisioner->judul} berhasil dibuat BPMI", [
                'id' => $kuisioner->id,
                'data' => $request->except('_token')
            ]);

            return back()->with('success', 'Master Kuesioner berhasil dibuat dan berstatus Draft.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: [Kuisioner] Gagal membuat kuesioner", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan Master Kuesioner.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kuisioner $kuisioner)
    {
        Log::info("SYNC_PULL: Membuka Form Builder Kuesioner: {$kuisioner->judul}");

        // Eager load questions related to this form, ordered by their sequence
        $kuisioner->load([
            'pertanyaans' => function ($query) {
                $query->orderBy('urutan', 'asc');
            },
            'semester'
        ]);

        return view('dosen.kuisioner.edit', compact('kuisioner'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kuisioner $kuisioner)
    {
        $request->validate([
            'status' => 'required|in:draft,published,closed',
            'judul' => 'sometimes|required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        try {
            // Update Core Data
            $kuisioner->update($request->only('judul', 'deskripsi', 'status'));

            Log::info("CRUD_UPDATE: [Kuisioner] Status form telah diperbarui", [
                'id' => $kuisioner->id,
                'status' => $kuisioner->status
            ]);

            return back()->with('success', 'Pengaturan Kuesioner berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: [Kuisioner] Gagal update kuesioner", [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan saat memperbarui kuesioner.');
        }
    }

    /**
     * Sinkronisasi data pertanyaan kuesioner dari Form Builder (POST Array)
     */
    public function syncPertanyaan(Request $request, Kuisioner $kuisioner)
    {
        if ($kuisioner->status === 'published') {
            return back()->with('error', 'Tidak dapat mengubah pertanyaan pada Kuesioner yang sudah di-publish!');
        }

        $request->validate([
            'pertanyaan' => 'required|array',
            'pertanyaan.*.teks_pertanyaan' => 'required|string',
            'pertanyaan.*.tipe_input' => 'required|in:likert,pilihan_ganda,esai',
            'pertanyaan.*.opsi_jawaban' => 'nullable|string',
            'pertanyaan.*.urutan' => 'required|integer'
        ]);

        try {
            // Gunakan Transaction agar aman
            \DB::beginTransaction();

            $incomingIds = [];

            foreach ($request->pertanyaan as $item) {
                // Parsing opsi jawaban jika pilihan ganda
                $opsi = null;
                if ($item['tipe_input'] === 'pilihan_ganda' && !empty($item['opsi_jawaban'])) {
                    // Explode by comma, trim whitespace, and re-index array
                    $opsi = array_values(array_filter(array_map('trim', explode(',', $item['opsi_jawaban']))));
                }

                if (isset($item['id']) && !empty($item['id'])) {
                    // Update yang sudah ada
                    $pertanyaan = $kuisioner->pertanyaans()->find($item['id']);
                    if ($pertanyaan) {
                        $pertanyaan->update([
                            'teks_pertanyaan' => $item['teks_pertanyaan'],
                            'tipe_input' => $item['tipe_input'],
                            'opsi_jawaban' => $opsi,
                            'urutan' => $item['urutan']
                        ]);
                        $incomingIds[] = $pertanyaan->id;
                    }
                } else {
                    // Buat Pertanyaan Baru
                    $baru = $kuisioner->pertanyaans()->create([
                        'teks_pertanyaan' => $item['teks_pertanyaan'],
                        'tipe_input' => $item['tipe_input'],
                        'opsi_jawaban' => $opsi,
                        'urutan' => $item['urutan']
                    ]);
                    $incomingIds[] = $baru->id;
                }
            }

            // Hapus pertanyaan yang tidak ada di payload Form Builder (Artinya dihapus user)
            $kuisioner->pertanyaans()->whereNotIn('id', $incomingIds)->delete();

            \DB::commit();

            Log::info("CRUD_UPDATE: [Kuisioner] Memperbarui struktur pertanyaan via Form Builder", [
                'id_kuisioner' => $kuisioner->id,
                'total_pertanyaan' => count($incomingIds)
            ]);

            return back()->with('success', 'Desain pertanyaan kuesioner berhasil disingkronisasi.');

        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error("SYSTEM_ERROR: [Kuisioner] Gagal sync pertanyaan kuesioner", [
                'id_kuisioner' => $kuisioner->id,
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan desain kuesioner.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kuisioner $kuisioner)
    {
        try {
            if ($kuisioner->status === 'published') {
                return back()->with('error', 'Tidak dapat menghapus kuesioner yang sudah di-publish. Ubah status menjadi Closed / Draft terlebih dahulu.');
            }

            $id = $kuisioner->id;
            $kuisioner->delete();

            Log::warning("CRUD_DELETE: [Kuisioner] Form kuesioner dihapus", ['id' => $id]);

            return back()->with('success', 'Kuesioner berhasil dihapus secara permanen.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: [Kuisioner] Gagal menghapus kuesioner", [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Gagal menghapus kuesioner karena terikat dengan data lain.');
        }
    }

    /**
     * Tampilkan Halaman Rekapitulasi Laporan Kuesioner untuk BPMI.
     */
    public function show(Kuisioner $kuisioner)
    {
        Log::info("SYNC_PULL: Membaca Rekapitulasi Laporan Kuesioner BPMI", ['id' => $kuisioner->id]);

        $kuisioner->load([
            'pertanyaans' => function ($q) {
                $q->where('tipe_input', 'likert')->orderBy('urutan', 'asc');
            },
            'semester'
        ]);

        // Dapatkan Total Responden Valid (Distinct ID Mahasiswa)
        $totalResponden = \App\Models\KuisionerSubmission::where('id_kuisioner', $kuisioner->id)->distinct('id_mahasiswa')->count('id_mahasiswa');

        // Menghitung Target Partisipan (Coverage)
        $targetPartisipan = 0;
        $activeSemesterId = $kuisioner->id_semester;

        if ($kuisioner->tipe === 'pelayanan') {
            // Asumsi: Semua mahasiswa yang memiliki riwayat_pendidikan di semester tersebut
            $targetPartisipan = \App\Models\Mahasiswa::whereHas('riwayatPendidikans', function ($q) use ($activeSemesterId) {
                // Di sistem Neo Feeder, biasanya semester berjalan dikorelasikan di Krs/PesertaKelasKuliah. 
                // Kita sederhanakan dengan mengambil user aktif yang memiliki KRS semester tersebut
                $q->whereHas('pesertaKelasKuliahs.kelasKuliah', function ($q2) use ($activeSemesterId) {
                    $q2->where('id_semester', $activeSemesterId);
                });
            })->count();
        } else {
            // Untuk dosen, total responden idealnya adalah total baris peserta_kelas_kuliah di semester itu
            $targetPartisipan = \App\Models\PesertaKelasKuliah::whereHas('kelasKuliah', function ($q) use ($activeSemesterId) {
                $q->where('id_semester', $activeSemesterId);
            })->count();

            // Re-evaluasi $totalResponden untuk tipe 'dosen' (Dihitung dari relasi kelas)
            $totalResponden = \App\Models\KuisionerSubmission::where('id_kuisioner', $kuisioner->id)
                ->whereNotNull('id_kelas_kuliah')
                ->count();
        }

        $coverage = $targetPartisipan > 0 ? round(($totalResponden / $targetPartisipan) * 100, 1) : 0;

        // --- Perhitungan Agregat per Pertanyaan (Khusus LIKERT) ---
        $rekapPertanyaan = [];
        $totalAverageSemua = 0;
        $countPertanyaan = $kuisioner->pertanyaans->count();

        foreach ($kuisioner->pertanyaans as $p) {
            // Ambil Rata-rata dari tabel Jawaban Detail
            $avgScore = \App\Models\KuisionerJawabanDetail::where('id_pertanyaan', $p->id)
                ->avg('jawaban_skala') ?? 0;

            $rekapPertanyaan[] = [
                'teks' => $p->teks_pertanyaan,
                'avg' => round($avgScore, 2),
                'label' => $this->getKesimpulanSkor($avgScore)
            ];

            $totalAverageSemua += $avgScore;
        }

        $grandAverage = $countPertanyaan > 0 ? round($totalAverageSemua / $countPertanyaan, 2) : 0;
        $grandKesimpulan = $this->getKesimpulanSkor($grandAverage);

        // --- Sample Esai (Mengambil 5 komentar terbaru) ---
        $esaiTerbaru = \App\Models\KuisionerJawabanDetail::whereHas('pertanyaan', function ($q) use ($kuisioner) {
            $q->where('id_kuisioner', $kuisioner->id)->where('tipe_input', 'esai');
        })
            ->whereNotNull('jawaban_teks')
            ->latest()
            ->limit(5)
            ->get();

        return view('dosen.kuisioner.show', compact('kuisioner', 'totalResponden', 'targetPartisipan', 'coverage', 'rekapPertanyaan', 'grandAverage', 'grandKesimpulan', 'esaiTerbaru'));
    }

    /**
     * Konversi Rata-rata 1-5 menjadi Kategori Kualitatif
     */
    private function getKesimpulanSkor($avg)
    {
        if ($avg >= 4.21)
            return ['teks' => 'Sangat Memuaskan', 'color' => 'success'];
        if ($avg >= 3.41)
            return ['teks' => 'Memuaskan', 'color' => 'primary'];
        if ($avg >= 2.61)
            return ['teks' => 'Cukup', 'color' => 'info'];
        if ($avg >= 1.81)
            return ['teks' => 'Kurang', 'color' => 'warning'];
        if ($avg > 0)
            return ['teks' => 'Sangat Kurang', 'color' => 'danger'];
        return ['teks' => 'Belum Ada Data', 'color' => 'secondary'];
    }
}
