<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PesertaKelasKuliah;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $id_semester = $request->get('id_semester', getActiveSemesterId());
        $mahasiswa = Auth::user()->mahasiswa;

        if (!$mahasiswa) {
            return redirect()->back()->with('error', 'Data Mahasiswa tidak ditemukan.');
        }

        $riwayatAktif = $mahasiswa->riwayatAktif;
        if (!$riwayatAktif) {
            return redirect()->back()->with('error', 'Riwayat pendidikan aktif tidak ditemukan.');
        }

        // Ambil kelas yang dikontrak mahasiswa pada semester terpilih
        $pesertas = PesertaKelasKuliah::with([
            'kelasKuliah' => function ($q) use ($id_semester) {
                $q->where('id_semester', $id_semester)
                    ->with([
                        'mataKuliah',
                        'jadwalKuliahs.ruang',
                        'dosenPengajars.dosen'
                    ]);
            }
        ])
            ->where('riwayat_pendidikan_id', $riwayatAktif->id)
            ->whereHas('kelasKuliah', function ($q) use ($id_semester) {
                $q->where('id_semester', $id_semester);
            })
            ->get();

        // Flatten dan Kelompokkan Jadwal berdasarkan Hari
        $jadwalGrouped = [];
        foreach ($pesertas as $peserta) {
            $kelas = $peserta->kelasKuliah;
            if (!$kelas)
                continue;

            // Ambil daftar dosen pengajar menggunakan accessor nama_tampilan model pivot
            $dosenList = $kelas->dosenPengajars->map(function ($dp) {
                return $dp->nama_tampilan;
            })->unique()->implode(', ');

            foreach ($kelas->jadwalKuliahs as $jadwal) {
                $hari = $jadwal->hari;
                $jadwalGrouped[$hari][] = [
                    'nama_mk' => $kelas->mataKuliah->nama_mk ?? '-',
                    'kode_mk' => $kelas->mataKuliah->kode_mk ?? '-',
                    'kelas' => $kelas->nama_kelas_kuliah,
                    'ruang' => $jadwal->ruang->nama_ruang ?? 'N/A',
                    'jam_mulai' => substr($jadwal->jam_mulai, 0, 5),
                    'jam_selesai' => substr($jadwal->jam_selesai, 0, 5),
                    'dosen' => $dosenList
                ];
            }
        }

        // Sort per hari berdasarkan jam_mulai
        foreach ($jadwalGrouped as $hari => &$list) {
            usort($list, function ($a, $b) {
                return strcmp($a['jam_mulai'], $b['jam_mulai']);
            });
        }

        $semesters = Semester::orderBy('id_semester', 'desc')->get();
        $activeSemester = Semester::where('id_semester', $id_semester)->first();

        Log::info("FETCH_JADWAL_MAHASISWA: Menampilkan jadwal untuk semester {$id_semester}", [
            'mahasiswa_id' => $mahasiswa->id,
            'count' => count($jadwalGrouped)
        ]);

        return view('mahasiswa.jadwal.index', compact('jadwalGrouped', 'semesters', 'id_semester', 'activeSemester'));
    }
}
