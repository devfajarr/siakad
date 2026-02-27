<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Services\Akademik\StatisticAkademikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KhsMahasiswaController extends Controller
{
    protected $statisticService;

    public function __construct(StatisticAkademikService $statisticService)
    {
        $this->statisticService = $statisticService;
    }

    /**
     * Tampilkan Kartu Hasil Studi (KHS) Mahasiswa.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            return redirect()->route('dashboard')->with('error', 'Profil mahasiswa tidak ditemukan.');
        }

        $riwayatAktif = $mahasiswa->riwayatAktif;
        if (!$riwayatAktif) {
            return redirect()->route('dashboard')->with('error', 'Riwayat pendidikan mahasiswa tidak ditemukan.');
        }

        // Get Semester Selection
        $semesterId = $request->get('semester_id', getActiveSemesterId());
        $selectedSemester = Semester::where('id_semester', $semesterId)->first();

        // Get KHS Data & Stats
        $khsData = $this->statisticService->getKhsPerSemester($riwayatAktif->id, $semesterId);
        $semesterStats = $this->statisticService->calculateIps($riwayatAktif->id, $semesterId);
        $cumulativeStats = $this->statisticService->calculateIpk($riwayatAktif->id, $semesterId);

        // Semesters for Filter (Only past & current semesters since enrollment)
        $semesters = Semester::where('id_semester', '>=', $riwayatAktif->id_periode_masuk)
            ->where('id_semester', '<=', getActiveSemesterId())
            ->orderBy('id_semester', 'desc')
            ->get();

        Log::info("MAHASISWA_VIEW_KHS: Diakses oleh {$mahasiswa->nama_mahasiswa}", [
            'nim' => $riwayatAktif->nim,
            'semester_id' => $semesterId
        ]);

        return view('mahasiswa.khs.index', compact(
            'mahasiswa',
            'riwayatAktif',
            'selectedSemester',
            'khsData',
            'semesterStats',
            'cumulativeStats',
            'semesters',
            'semesterId'
        ));
    }
}
