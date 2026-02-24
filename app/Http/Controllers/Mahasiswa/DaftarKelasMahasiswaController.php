<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DaftarKelasMahasiswaController extends Controller
{
    /**
     * Tampilkan daftar seluruh kelas yang diambil mahasiswa di semester aktif
     */
    public function index(Request $request)
    {
        if (!session()->has('active_role')) {
            session(['active_role' => 'Mahasiswa']);
        }

        $user = auth()->user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            return redirect()->route('dashboard')->with('error', 'Profil mahasiswa tidak ditemukan.');
        }

        $riwayatAktif = $mahasiswa->riwayatAktif;
        $semesterId = $request->get('semester_id', getActiveSemesterId());

        // Ambil daftar kelas via PesertaKelasKuliah (KRS)
        $kelasKuliahs = KelasKuliah::whereHas('pesertaKelasKuliah', function ($query) use ($riwayatAktif) {
            $query->where('riwayat_pendidikan_id', $riwayatAktif->id);
        })
            ->with(['mataKuliah', 'dosenPengajars.dosen', 'dosenPengajars.dosenAliasLokal', 'jadwalKuliahs.ruang'])
            ->get();

        $semesters = \App\Models\Semester::where('id_semester', '<=', getActiveSemesterId())
            ->orderBy('id_semester', 'desc')
            ->limit(20)
            ->get();

        Log::info("MAHASISWA_DAFTAR_KELAS: Diakses oleh {$mahasiswa->nama_mahasiswa}", ['semester_id' => $semesterId]);

        return view('mahasiswa.kelas.index', compact('kelasKuliahs', 'semesters', 'semesterId'));
    }

    /**
     * Tampilkan detail kelas perkuliahan bagi mahasiswa
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $mahasiswa = $user->mahasiswa;
        $riwayatAktif = $mahasiswa->riwayatAktif;

        $kelasKuliah = KelasKuliah::whereHas('pesertaKelasKuliah', function ($query) use ($riwayatAktif) {
            $query->where('riwayat_pendidikan_id', $riwayatAktif->id);
        })
            ->with(['mataKuliah', 'dosenPengajars.dosen', 'dosenPengajars.dosenAliasLokal', 'jadwalKuliahs.ruang'])
            ->findOrFail($id);

        return view('mahasiswa.kelas.show', compact('kelasKuliah'));
    }

    /**
     * Tampilkan log kehadiran mahasiswa pada satu kelas
     */
    public function presensi(string $id)
    {
        $user = auth()->user();
        $mahasiswa = $user->mahasiswa;
        $riwayatAktif = $mahasiswa->riwayatAktif;

        // Ambil data mata kuliah dan dosen
        $kelasKuliah = KelasKuliah::whereHas('pesertaKelasKuliah', function ($query) use ($riwayatAktif) {
            $query->where('riwayat_pendidikan_id', $riwayatAktif->id);
        })
            ->with(['mataKuliah', 'dosenPengajars.dosen', 'dosenPengajars.dosenAliasLokal'])
            ->withCount('presensiPertemuans')
            ->findOrFail($id);

        // Ambil daftar pertemuan dan status presensi mahasiswa ini
        $pertemuans = \App\Models\PresensiPertemuan::where('id_kelas_kuliah', $kelasKuliah->id_kelas_kuliah)
            ->with([
                'presensiMahasiswas' => function ($query) use ($riwayatAktif) {
                    $query->where('riwayat_pendidikan_id', $riwayatAktif->id);
                }
            ])
            ->orderBy('pertemuan_ke', 'asc')
            ->get();

        // Hitung statistik
        $totalHadir = 0;
        foreach ($pertemuans as $pertemuan) {
            $presensi = $pertemuan->presensiMahasiswas->first();
            if ($presensi && $presensi->status_kehadiran === 'H') {
                $totalHadir++;
            }
        }

        $targetPertemuan = config('academic.target_pertemuan');
        $persentase = $targetPertemuan > 0 ? round(($totalHadir / $targetPertemuan) * 100, 1) : 0;

        $summary = [
            'total_pertemuan' => $kelasKuliah->presensi_pertemuans_count,
            'total_hadir' => $totalHadir,
            'persentase' => $persentase,
            'target' => $targetPertemuan
        ];

        return view('mahasiswa.presensi.show', compact('kelasKuliah', 'pertemuans', 'summary'));
    }
}
