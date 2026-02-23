<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\KelasKuliah;
use App\Models\JadwalKuliah;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the Dosen Dashboard.
     */
    public function index()
    {
        if (!session()->has('active_role')) {
            session(['active_role' => 'Dosen']);
        }

        $dosen = auth()->user()->dosen;
        $dosenId = $dosen->id;
        $semesterId = getActiveSemesterId();
        $semester = getActiveSemester();

        // 1. Statistics Widget Data
        $kelasQuery = KelasKuliah::whereHas('dosenPengajars', function ($query) use ($dosenId) {
            $query->where('id_dosen', $dosenId)
                ->orWhere('id_dosen_alias_lokal', $dosenId);
        })->where('id_semester', $semesterId);

        $totalKelas = $kelasQuery->count();
        $totalMahasiswa = $kelasQuery->withCount('pesertaKelasKuliah')->get()->sum('peserta_kelas_kuliah_count');

        // 2. Today's teaching schedule
        // In this system: 1=Senin, 2=Selasa, ..., 7=Minggu (base on Carbon dayOfWeekIso)
        $todayDayOfWeek = Carbon::now()->dayOfWeekIso;

        $todaySchedules = JadwalKuliah::with(['kelasKuliah.mataKuliah', 'ruang'])
            ->whereHas('kelasKuliah.dosenPengajars', function ($query) use ($dosenId) {
                $query->where('id_dosen', $dosenId)
                    ->orWhere('id_dosen_alias_lokal', $dosenId);
            })
            ->whereHas('kelasKuliah', function ($query) use ($semesterId) {
                $query->where('id_semester', $semesterId);
            })
            ->where('hari', $todayDayOfWeek)
            ->orderBy('jam_mulai')
            ->get();

        return view('dosen.dashboard', compact(
            'dosen',
            'semester',
            'totalKelas',
            'totalMahasiswa',
            'todaySchedules'
        ));
    }
}
