<?php

namespace App\Http\Controllers\Dosen\Kaprodi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kaprodi;
use App\Models\Semester;
use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use App\Models\PresensiPertemuan;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $dosen = auth()->user()->dosen;
        if (!$dosen) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Data dosen tidak ditemukan.');
        }

        $kaprodiEntries = Kaprodi::with('prodi')->where('dosen_id', $dosen->id)->get();
        if ($kaprodiEntries->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Anda bukan Kaprodi.');
        }

        $prodiIds = $kaprodiEntries->pluck('id_prodi')->toArray();

        // Cari semester aktif global sebagai batas atas
        $globalActiveSemester = Semester::where('a_periode_aktif', '1')->first();
        if (!$globalActiveSemester) {
            $globalActiveSemester = Semester::orderBy('id_semester', 'desc')->first();
        }

        // Ambil daftar semester (aktif + lampau), sembunyikan yang akan datang
        $availableSemesters = Semester::where('id_semester', '<=', $globalActiveSemester->id_semester)
            ->orderBy('id_semester', 'desc')
            ->get();

        // Gunakan semester dari request atau default ke aktif
        $selectedSemesterId = $request->input('semester_id', $globalActiveSemester->id_semester);
        $selectedSemester = $availableSemesters->firstWhere('id_semester', $selectedSemesterId) ?: $globalActiveSemester;

        // 1. Statistik Dasar
        $totalKelas = KelasKuliah::whereIn('id_prodi', $prodiIds)
            ->where('id_semester', $selectedSemester->id_semester)
            ->count();

        $totalMahasiswa = PesertaKelasKuliah::whereHas('kelasKuliah', function ($q) use ($prodiIds, $selectedSemester) {
            $q->whereIn('id_prodi', $prodiIds)
                ->where('id_semester', $selectedSemester->id_semester);
        })
            ->where('status_krs', 'acc')
            ->distinct('riwayat_pendidikan_id')
            ->count('riwayat_pendidikan_id');

        // 2. Agregasi Progres Kelas
        $kelasQuery = KelasKuliah::with(['mataKuliah', 'dosenPengajar.dosen', 'programStudi'])
            ->withCount('presensiPertemuans')
            ->whereIn('id_prodi', $prodiIds)
            ->where('id_semester', $selectedSemester->id_semester)
            ->get();

        $avgProgres = $kelasQuery->avg(function ($kelas) {
            return ($kelas->presensi_pertemuan_count / 14) * 100;
        });

        // 3. Mapping data untuk tabel
        $kelasData = $kelasQuery->map(function ($kelas) {
            $progres = ($kelas->presensi_pertemuan_count / 14) * 100;
            return [
                'id' => $kelas->id,
                'kode_mk' => $kelas->mataKuliah->kode_mk ?? '-',
                'nama_mk' => $kelas->mataKuliah->nama_mk ?? '-',
                'nama_kelas' => $kelas->nama_kelas_kuliah,
                'prodi' => $kelas->programStudi->nama_program_studi ?? '-',
                'dosen' => $kelas->dosenPengajar->map(fn($d) => $d->dosen->nama_tampilan)->implode(', ') ?: '-',
                'pertemuan_count' => $kelas->presensi_pertemuan_count,
                'progres_percent' => round(min($progres, 100), 1),
                'status' => $this->getStatusKelas($progres)
            ];
        });

        return view('dosen.kaprodi.monitoring.index', compact(
            'kaprodiEntries',
            'availableSemesters',
            'selectedSemester',
            'totalKelas',
            'totalMahasiswa',
            'avgProgres',
            'kelasData'
        ));
    }

    public function show($id)
    {
        $dosen = auth()->user()->dosen;
        $prodiIds = Kaprodi::where('dosen_id', $dosen->id)->pluck('id_prodi')->toArray();

        if (empty($prodiIds)) {
            abort(403, 'Akses ditolak: Anda bukan Kaprodi.');
        }

        $kelas = KelasKuliah::with(['mataKuliah', 'dosenPengajar.dosen', 'semester'])
            ->whereIn('id_prodi', $prodiIds)
            ->findOrFail($id);

        $jurnal = PresensiPertemuan::with('dosen')
            ->where('id_kelas_kuliah', $kelas->id_kelas_kuliah)
            ->orderBy('pertemuan_ke', 'asc')
            ->get();

        // Rekap Kehadiran Mahasiswa
        $peserta = PesertaKelasKuliah::with(['riwayatPendidikan.mahasiswa'])
            ->where('id_kelas_kuliah', $kelas->id_kelas_kuliah)
            ->where('status_krs', 'acc')
            ->get();

        $rekapAbsensi = $peserta->map(function ($p) use ($jurnal) {
            $hadirCount = $p->presensiMahasiswas()->where('status', 'H')->count();
            $totalPertemuan = $jurnal->count();
            return [
                'nama' => $p->riwayatPendidikan->mahasiswa->nama_mahasiswa,
                'nim' => $p->riwayatPendidikan->mahasiswa->nim,
                'hadir' => $hadirCount,
                'total' => $totalPertemuan,
                'percent' => $totalPertemuan > 0 ? round(($hadirCount / $totalPertemuan) * 100, 1) : 0
            ];
        });

        return view('dosen.kaprodi.monitoring.show', compact('kelas', 'jurnal', 'rekapAbsensi'));
    }

    private function getStatusKelas($progres)
    {
        if ($progres >= 100)
            return 'Selesai';
        if ($progres >= 50)
            return 'Berjalan';
        if ($progres > 0)
            return 'Mulai';
        return 'Belum Mulai';
    }
}
