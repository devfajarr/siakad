<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KelasKuliah;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringPerkuliahanController extends Controller
{
    /**
     * Tampilkan monitoring progres perkuliahan.
     */
    public function index(Request $request)
    {
        $semesterId = $request->get('semester_id', getActiveSemesterId());
        $search = $request->get('search');

        // Master Semester untuk filter
        $semesters = Semester::orderBy('id_semester', 'desc')->get();

        // Statistik Widget (Selalu dihitung dari seluruh data semester ini)
        $allStatsQuery = KelasKuliah::where('id_semester', $semesterId)
            ->withCount('presensiPertemuans');

        $stats = [
            'total_kelas' => $allStatsQuery->count(),
            'selesai' => (clone $allStatsQuery)->has('presensiPertemuans', '>=', 13)->count(),
            'tertinggal' => (clone $allStatsQuery)->has('presensiPertemuans', '<', 7)->count(),
        ];

        // Query Utama dengan Pencarian dan Paginasi
        $kelasKuliahs = KelasKuliah::where('id_semester', $semesterId)
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

        // Tambahkan atribut visual ke setiap item yang ter-paginasi
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

        return view('admin.monitoring.perkuliahan.index', compact('kelasKuliahs', 'semesters', 'semesterId', 'stats', 'search'));
    }
}
