<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\JadwalKuliah;
use App\Models\PesertaKelasKuliah;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Tampilkan Ringkasan Dashboard Mahasiswa
     */
    public function index()
    {
        if (!session()->has('active_role')) {
            session(['active_role' => 'Mahasiswa']);
        }

        $user = auth()->user();

        // 1. Ambil Data Mahasiswa & Riwayat Aktif
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            Log::warning("DASHBOARD_MAHASISWA: Akun User ID {$user->id} tidak terhubung ke profil Mahasiswa.");
            return view('mahasiswa.dashboard')->with('error', 'Profil mahasiswa tidak ditemukan.');
        }

        $riwayatAktif = $mahasiswa->riwayatAktif;
        $activeSemesterId = getActiveSemesterId();
        $activeSemester = getActiveSemester();

        // 2. Statistik Semester Berjalan
        // Mengambil data KRS mahasiswa di semester aktif
        $krsQuery = PesertaKelasKuliah::with('kelasKuliah.mataKuliah')
            ->where('riwayat_pendidikan_id', $riwayatAktif->id)
            ->whereHas('kelasKuliah', function ($query) use ($activeSemesterId) {
                $query->where('id_semester', $activeSemesterId);
            });

        $totalSks = $krsQuery->get()->sum(function ($p) {
            return $p->kelasKuliah->mataKuliah->sks_mata_kuliah ?? 0;
        });

        $totalMatkul = $krsQuery->count();

        // 3. Jadwal Kuliah Hari Ini
        $todayDayOfWeek = Carbon::now()->dayOfWeekIso;

        $todaySchedules = JadwalKuliah::with(['kelasKuliah.mataKuliah', 'ruang'])
            ->whereHas('kelasKuliah.pesertaKelasKuliah', function ($query) use ($riwayatAktif) {
                $query->where('riwayat_pendidikan_id', $riwayatAktif->id);
            })
            ->whereHas('kelasKuliah', function ($query) use ($activeSemesterId) {
                $query->where('id_semester', $activeSemesterId);
            })
            ->where('hari', $todayDayOfWeek)
            ->orderBy('jam_mulai')
            ->get();

        Log::info("DASHBOARD_MAHASISWA: Diakses oleh {$mahasiswa->nama_mahasiswa}");

        return view('mahasiswa.dashboard', compact(
            'mahasiswa',
            'riwayatAktif',
            'activeSemester',
            'totalSks',
            'totalMatkul',
            'todaySchedules'
        ));
    }
}
