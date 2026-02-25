<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\DosenPengajarKelasKuliah;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $id_semester = $request->get('id_semester', getActiveSemesterId());
        $dosen = Auth::user()->dosen;

        if (!$dosen) {
            return redirect()->back()->with('error', 'Data Dosen tidak ditemukan.');
        }

        // Ambil data pengajaran dosen pada semester terpilih
        $pengajars = DosenPengajarKelasKuliah::with([
            'kelasKuliah' => function ($q) use ($id_semester) {
                $q->where('id_semester', $id_semester)
                    ->with(['mataKuliah', 'jadwalKuliahs.ruang']);
            }
        ])
            ->where(function ($q) use ($dosen) {
                $q->where('id_dosen', $dosen->id)
                    ->orWhere('id_dosen_alias_lokal', $dosen->id);
            })
            ->whereHas('kelasKuliah', function ($q) use ($id_semester) {
                $q->where('id_semester', $id_semester);
            })
            ->get();

        // Flatten dan Kelompokkan Jadwal berdasarkan Hari
        $jadwalGrouped = [];
        foreach ($pengajars as $pengajar) {
            $kelas = $pengajar->kelasKuliah;
            if (!$kelas)
                continue;

            foreach ($kelas->jadwalKuliahs as $jadwal) {
                $hari = $jadwal->hari; // 1 (Senin) - 7 (Minggu)
                $jadwalGrouped[$hari][] = [
                    'nama_mk' => $kelas->mataKuliah->nama_mk ?? '-',
                    'kode_mk' => $kelas->mataKuliah->kode_mk ?? '-',
                    'kelas' => $kelas->nama_kelas_kuliah,
                    'ruang' => $jadwal->ruang->nama_ruang ?? 'N/A',
                    'jam_mulai' => substr($jadwal->jam_mulai, 0, 5),
                    'jam_selesai' => substr($jadwal->jam_selesai, 0, 5),
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

        Log::info("FETCH_JADWAL_DOSEN: Menampilkan jadwal untuk semester {$id_semester}", [
            'dosen_id' => $dosen->id,
            'count' => count($jadwalGrouped)
        ]);

        return view('dosen.jadwal.index', compact('jadwalGrouped', 'semesters', 'id_semester', 'activeSemester'));
    }
}
