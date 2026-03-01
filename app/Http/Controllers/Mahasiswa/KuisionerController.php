<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kuisioner;
use App\Models\KuisionerSubmission;
use App\Models\PesertaKelasKuliah;

class KuisionerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $mahasiswa = $user->mahasiswa;

        // Asumsi semester aktif
        $activeSemesterId = getActiveSemesterId();

        // Ambil riwayat pendidikan ids
        $riwayatIds = $mahasiswa->riwayatPendidikans()->pluck('id');

        // Ambil Form Kuisioner Target yang Published
        $kuesionerPelayanan = Kuisioner::where('id_semester', $activeSemesterId)
            ->where('tipe', 'pelayanan')
            ->where('status', 'published')
            ->get();

        $kuesionerDosen = Kuisioner::where('id_semester', $activeSemesterId)
            ->where('tipe', 'dosen')
            ->where('status', 'published')
            ->get();

        // Evaluasi progres penyelesaian per formulir
        $progressPelayanan = [];
        foreach ($kuesionerPelayanan as $kp) {
            $isDone = KuisionerSubmission::where('id_kuisioner', $kp->id)
                ->where('id_mahasiswa', $mahasiswa->id)
                ->exists();
            $progressPelayanan[$kp->id] = $isDone;
        }

        $progressDosen = [];
        $pesertaKelasArray = [];
        if ($kuesionerDosen->isNotEmpty()) {
            // Muat kelas beserta relasi dosen pengajar (Dosen utama dan Alias)
            $pesertaKelas = PesertaKelasKuliah::with(['kelasKuliah.mataKuliah', 'kelasKuliah.dosenPengajar.dosen'])
                ->whereIn('riwayat_pendidikan_id', $riwayatIds)
                ->whereHas('kelasKuliah', function ($q) use ($activeSemesterId) {
                    $q->where('id_semester', $activeSemesterId);
                })
                ->get();
            $pesertaKelasArray = $pesertaKelas;

            $idKelasDiambil = $pesertaKelas->pluck('id_kelas_kuliah');

            // Hitung total dosen yang harus dinilai (per kelas)
            $totalDosenTarget = \App\Models\DosenPengajarKelasKuliah::whereIn('id_kelas_kuliah', $idKelasDiambil)->count();

            foreach ($kuesionerDosen as $kd) {
                if ($totalDosenTarget === 0) {
                    $progressDosen[$kd->id] = ['done' => 0, 'total' => 0, 'completed' => true];
                    continue; // Skip jika tidak ada kelas
                }

                $sudahHit = KuisionerSubmission::where('id_kuisioner', $kd->id)
                    ->where('id_mahasiswa', $mahasiswa->id)
                    ->whereNotNull('id_kelas_kuliah')
                    ->whereNotNull('id_dosen')
                    ->count();

                $progressDosen[$kd->id] = [
                    'done' => $sudahHit,
                    'total' => $totalDosenTarget,
                    'completed' => ($sudahHit >= $totalDosenTarget)
                ];
            }
        }

        return view('mahasiswa.kuisioner.index', compact(
            'kuesionerPelayanan',
            'progressPelayanan',
            'kuesionerDosen',
            'progressDosen',
            'pesertaKelasArray'
        ));
    }

    /**
     * Show the specified resource form.
     */
    public function show(Kuisioner $kuisioner, Request $request)
    {
        $user = auth()->user();
        $mahasiswa = $user->mahasiswa;

        if ($kuisioner->status !== 'published') {
            return back()->with('error', 'Kuesioner ini tidak aktif.');
        }

        // Jika tipe dosen, pastikan ada id_kelas yang dipilih di querystring GET ?kelas=...
        $kelasAktif = null;
        $dosenAktif = null;
        if ($kuisioner->tipe === 'dosen') {
            if (!$request->has('kelas') || !$request->has('dosen')) {
                return back()->with('error', 'Pemilihan form Kinerja Dosen memerlukan rujukan Kelas dan Dosen yang dievaluasi!');
            }

            $kelasId = $request->kelas;
            $dosenId = $request->dosen;
            $kelasAktif = \App\Models\KelasKuliah::findOrFail($kelasId);
            $dosenAktif = \App\Models\Dosen::findOrFail($dosenId);

            // Verifikasi apakah dia peserta di kelas ini
            $isBoleh = PesertaKelasKuliah::whereIn('riwayat_pendidikan_id', $mahasiswa->riwayatPendidikans()->pluck('id'))
                ->where('id_kelas_kuliah', $kelasAktif->id_kelas_kuliah)
                ->exists();
            if (!$isBoleh) {
                return back()->with('error', 'Anda tidak terdaftar sebagai peserta pada kelas ini.');
            }

            // Verifikasi apakah dosen ini mengajar di kelas tersebut
            $isDosenMengajar = \App\Models\DosenPengajarKelasKuliah::where('id_kelas_kuliah', $kelasAktif->id_kelas_kuliah)
                ->where(function ($q) use ($dosenId) {
                    $q->where('id_dosen', $dosenId)
                        ->orWhere('id_dosen_alias', $dosenId)
                        ->orWhere('id_dosen_alias_lokal', $dosenId);
                })->exists();

            if (!$isDosenMengajar) {
                return back()->with('error', 'Dosen yang dipilih tidak mengajar pada kelas ini.');
            }

            // Cek sudah isi form atau belum
            $sudahIsi = KuisionerSubmission::where('id_kuisioner', $kuisioner->id)
                ->where('id_mahasiswa', $mahasiswa->id)
                ->where('id_kelas_kuliah', $kelasAktif->id_kelas_kuliah)
                ->where('id_dosen', $dosenId)
                ->exists();

            if ($sudahIsi)
                return back()->with('info', 'Anda telah mengevaluasi dosen ini untuk mata kuliah tersebut. Terima kasih!');
        } else {
            // Tipe pelayanan murni
            $sudahIsi = KuisionerSubmission::where('id_kuisioner', $kuisioner->id)
                ->where('id_mahasiswa', $mahasiswa->id)
                ->exists();
            if ($sudahIsi)
                return back()->with('info', 'Form pelayanan ini telah selesai anda lengkapi. Terima kasih!');
        }

        $kuisioner->load([
            'pertanyaans' => function ($q) {
                $q->orderBy('urutan', 'asc');
            }
        ]);

        return view('mahasiswa.kuisioner.show', compact('kuisioner', 'kelasAktif', 'dosenAktif'));
    }

    /**
     * Store submission answers.
     */
    public function store(Request $request, Kuisioner $kuisioner)
    {
        if ($kuisioner->status !== 'published') {
            return back()->with('error', 'Kuesioner ini tidak aktif.');
        }

        $request->validate([
            'jawaban' => 'required|array',
            'id_kelas_kuliah' => 'nullable',
            'id_dosen' => 'nullable'
        ]);

        $mahasiswa = auth()->user()->mahasiswa;

        try {
            \DB::beginTransaction();

            // 1. Validasi Ganda (Apakah sudah mensubmit?)
            $queryCheck = KuisionerSubmission::where('id_kuisioner', $kuisioner->id)
                ->where('id_mahasiswa', $mahasiswa->id);

            if ($kuisioner->tipe === 'dosen') {
                if (!$request->id_kelas_kuliah || !$request->id_dosen) {
                    throw new \Exception("Evaluasi Kinerja Dosen memerlukan Identitas Kelas Kuliah dan Dosen yang valid.");
                }
                $queryCheck->where('id_kelas_kuliah', $request->id_kelas_kuliah)
                    ->where('id_dosen', $request->id_dosen);
            }

            if ($queryCheck->exists()) {
                return redirect()->route('mahasiswa.kuisioner.index')->with('info', 'Anda sudah mengisi kuesioner ini sebelumnya.');
            }

            // 2. Buat Header Submission
            $submission = KuisionerSubmission::create([
                'id_kuisioner' => $kuisioner->id,
                'id_mahasiswa' => $mahasiswa->id,
                'id_kelas_kuliah' => $request->id_kelas_kuliah ?? null,
                'id_dosen' => $request->id_dosen ?? null,
                'status_sinkronisasi' => 'synced'
            ]);

            // 3. Simpan Detail Jawaban
            $details = [];
            foreach ($request->jawaban as $pertanyaanId => $answer) {
                $detail = [
                    'id_submission' => $submission->id,
                    'id_pertanyaan' => $pertanyaanId,
                    'jawaban_skala' => $answer['skala'] ?? null,
                    'jawaban_teks' => $answer['teks'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                $details[] = $detail;
            }

            \App\Models\KuisionerJawabanDetail::insert($details);

            \DB::commit();

            \Log::info("CRUD_CREATE: [KuisionerSubmission] Mahasiswa berhasil mensubmit kuesioner", [
                'id_kuisioner' => $kuisioner->id,
                'id_mahasiswa' => $mahasiswa->id,
                'tipe' => $kuisioner->tipe
            ]);

            return redirect()->route('mahasiswa.kuisioner.index')->with('success', 'Terima kasih, jawaban kuesioner Anda berhasil disimpan.');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("SYSTEM_ERROR: [KuisionerSubmission] Gagal simpan jawaban mahasiswa", [
                'message' => $e->getMessage()
            ]);
            return back()->with('error', 'Terjadi kesalahan sistem saat menyimpan jawaban Anda. ' . $e->getMessage());
        }
    }
}
