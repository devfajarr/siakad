<?php

namespace App\Http\Controllers\Dosen\Kaprodi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kaprodi;
use App\Models\Semester;
use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use App\Models\PresensiPertemuan;

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

        $semesterId = $request->get('semester_id', getActiveSemesterId());
        $search = $request->get('search');

        // Master Semester untuk filter
        $semesters = Semester::orderBy('id_semester', 'desc')->get();

        // Statistik Widget (Discope khusus prodi)
        $allStatsQuery = KelasKuliah::milikProdi($prodiIds)
            ->where('id_semester', $semesterId)
            ->withCount('presensiPertemuans');

        $stats = [
            'total_kelas' => $allStatsQuery->count(),
            'selesai' => (clone $allStatsQuery)->has('presensiPertemuans', '>=', 13)->count(),
            'tertinggal' => (clone $allStatsQuery)->has('presensiPertemuans', '<', 7)->count(),
        ];

        // Query Utama dengan Pencarian dan Paginasi
        $kelasKuliahs = KelasKuliah::milikProdi($prodiIds)
            ->where('id_semester', $semesterId)
            ->withCount('presensiPertemuans')
            ->with(['mataKuliah', 'programStudi', 'dosenPengajars.dosen', 'dosenPengajars.dosenAliasLokal'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('mataKuliah', function ($mq) use ($search) {
                        $mq->where('nama_mk', 'like', "%{$search}%")
                            ->orWhere('kode_mk', 'like', "%{$search}%");
                    })
                        ->orWhereHas('dosenPengajars.dosen', function ($dq) use ($search) {
                            $dq->where('nama', 'like', "%{$search}%");
                        })
                        ->orWhereHas('programStudi', function ($pq) use ($search) {
                            $pq->where('nama_program_studi', 'like', "%{$search}%");
                        })
                        ->orWhere('nama_kelas_kuliah', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Tambahkan atribut visual progress bar
        $kelasKuliahs->getCollection()->transform(function ($kelas) {
            $progres = $kelas->presensi_pertemuans_count;
            if ($progres < 7) {
                $kelas->status_warna = 'danger';
                $kelas->status_label = 'Tertinggal';
            } elseif ($progres <= 12) {
                $kelas->status_warna = 'warning';
                $kelas->status_label = 'Berjalan';
            } else {
                $kelas->status_warna = 'success';
                $kelas->status_label = 'Selesai/Mendekati';
            }
            return $kelas;
        });

        return view('dosen.kaprodi.monitoring.index', compact(
            'kaprodiEntries',
            'kelasKuliahs',
            'semesters',
            'semesterId',
            'stats',
            'search'
        ));
    }

    public function show($id)
    {
        $dosen = auth()->user()->dosen;
        $kaprodiEntries = Kaprodi::with('prodi')->where('dosen_id', $dosen->id)->get();
        $prodiIds = $kaprodiEntries->pluck('id_prodi')->toArray();

        if (empty($prodiIds)) {
            abort(403, 'Akses ditolak: Anda bukan Kaprodi.');
        }

        $kelas = KelasKuliah::with([
            'mataKuliah',
            'dosenPengajars.dosen',
            'dosenPengajars.dosenAliasLokal',
            'programStudi',
            'semester'
        ])
            ->milikProdi($prodiIds)
            ->withCount('presensiPertemuans')
            ->where('id_kelas_kuliah', $id)
            ->firstOrFail();

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
                'nama' => $p->riwayatPendidikan->mahasiswa->nama_mahasiswa ?? '-',
                'nim' => $p->riwayatPendidikan->mahasiswa->nim ?? '-',
                'hadir' => $hadirCount,
                'total' => $totalPertemuan,
                'percent' => $totalPertemuan > 0 ? round(($hadirCount / $totalPertemuan) * 100, 1) : 0
            ];
        });

        return view('dosen.kaprodi.monitoring.show', compact('kelas', 'jurnal', 'rekapAbsensi', 'kaprodiEntries'));
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
