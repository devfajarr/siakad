<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PembimbingAkademik;
use App\Models\PesertaKelasKuliah;
use App\Models\Mahasiswa;
use App\Models\Semester;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class KrsApprovalController extends Controller
{
    /**
     * Dashboard Perwalian Dosen PA.
     */
    public function index()
    {
        $dosen = auth()->user()->dosen;

        // Cari Semester Aktif
        $semesterAktif = Semester::where('a_periode_aktif', '1')->first();
        if (!$semesterAktif) {
            $semesterAktif = Semester::orderBy('id_semester', 'desc')->first();
        }

        $mahasiswaBimbingan = $dosen->mahasiswaBimbingan()
            ->with(['riwayatAktif.programStudi'])
            ->get();

        // Calculate statuses for each student
        foreach ($mahasiswaBimbingan as $m) {
            $krs = PesertaKelasKuliah::where('riwayat_pendidikan_id', $m->riwayatAktif?->id)
                ->whereHas('kelasKuliah', fn($q) => $q->where('id_semester', $semesterAktif->id_semester))
                ->get();

            if ($krs->isEmpty()) {
                $m->status_krs_label = '<span class="badge bg-secondary">Kosong</span>';
            } else {
                $statuses = $krs->pluck('status_krs')->unique();
                if ($statuses->contains('pending')) {
                    $m->status_krs_label = '<span class="badge bg-warning">Pending Approval</span>';
                } elseif ($statuses->contains('paket')) {
                    $m->status_krs_label = '<span class="badge bg-info">Draft (Paket)</span>';
                } elseif ($statuses->every(fn($s) => $s === 'acc')) {
                    $m->status_krs_label = '<span class="badge bg-success">Approved (ACC)</span>';
                } else {
                    $m->status_krs_label = '<span class="badge bg-danger">Revision Needed</span>';
                }
            }
        }

        // Hitung statistik status KRS untuk mahasiwa bimbingan di semester aktif
        $stats = [
            'total' => $mahasiswaBimbingan->count(),
            'pending' => PesertaKelasKuliah::whereIn('riwayat_pendidikan_id', $mahasiswaBimbingan->pluck('riwayatAktif.id'))
                ->whereHas('kelasKuliah', fn($q) => $q->where('id_semester', $semesterAktif->id_semester))
                ->where('status_krs', 'pending')
                ->distinct('riwayat_pendidikan_id')
                ->count(),
            'acc' => PesertaKelasKuliah::whereIn('riwayat_pendidikan_id', $mahasiswaBimbingan->pluck('riwayatAktif.id'))
                ->whereHas('kelasKuliah', fn($q) => $q->where('id_semester', $semesterAktif->id_semester))
                ->where('status_krs', 'acc')
                ->distinct('riwayat_pendidikan_id')
                ->count(),
        ];

        return view('dosen.perwalian.index', compact('mahasiswaBimbingan', 'semesterAktif', 'stats'));
    }

    /**
     * Detail KRS Mahasiswa.
     */
    public function show($id)
    {
        $dosen = auth()->user()->dosen;
        $mahasiswa = $dosen->mahasiswaBimbingan()->findOrFail($id);

        $semesterAktif = Semester::where('a_periode_aktif', '1')->first();

        $krsItems = PesertaKelasKuliah::with(['kelasKuliah.mataKuliah'])
            ->where('riwayat_pendidikan_id', $mahasiswa->riwayatAktif?->id)
            ->whereHas('kelasKuliah', fn($q) => $q->where('id_semester', $semesterAktif->id_semester))
            ->get();

        return view('dosen.perwalian.show', compact('mahasiswa', 'krsItems', 'semesterAktif'));
    }

    /**
     * Approve KRS (Mass per Student).
     */
    public function approve(Request $request, $id)
    {
        $dosen = auth()->user()->dosen;
        $mahasiswa = $dosen->mahasiswaBimbingan()->findOrFail($id);
        $semesterId = $request->id_semester;

        try {
            DB::beginTransaction();

            $updated = PesertaKelasKuliah::where('riwayat_pendidikan_id', $mahasiswa->riwayatAktif?->id)
                ->whereHas('kelasKuliah', fn($q) => $q->where('id_semester', $semesterId))
                ->whereIn('status_krs', ['pending', 'paket']) // PA bisa ACC meskipun masih status paket jika diperlukan
                ->update([
                    'status_krs' => 'acc',
                    'last_acc_at' => now(),
                    'acc_by' => $dosen->id
                ]);

            DB::commit();

            Log::info("CRUD_UPDATE: Dosen PA Approve KRS", ['nim' => $mahasiswa->nim, 'count' => $updated]);

            return back()->with('success', "KRS Mahasiswa {$mahasiswa->nama_mahasiswa} berhasil di ACC.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SYSTEM_ERROR: Gagal ACC KRS", ['message' => $e->getMessage()]);
            return back()->with('error', 'Gagal memproses persetujuan.');
        }
    }

    /**
     * Cetak KRS Mahasiswa (Perwalian).
     */
    public function print($id)
    {
        $dosen = auth()->user()->dosen;
        $mahasiswa = $dosen->mahasiswaBimbingan()->findOrFail($id);
        $semester = getActiveSemester();

        $krsItems = PesertaKelasKuliah::with(['kelasKuliah.mataKuliah', 'kelasKuliah.dosenPengajar.dosen'])
            ->where('riwayat_pendidikan_id', $mahasiswa->riwayatAktif?->id)
            ->whereHas('kelasKuliah', fn($q) => $q->where('id_semester', $semester->id_semester))
            ->get();

        return view('shared.krs-print', compact('mahasiswa', 'semester', 'krsItems'));
    }
}
