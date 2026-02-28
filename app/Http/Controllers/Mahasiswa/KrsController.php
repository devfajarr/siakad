<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PesertaKelasKuliah;
use App\Models\KrsPeriod;
use App\Models\Semester;
use Illuminate\Support\Facades\Log;

class KrsController extends Controller
{
    public function index(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswa;

        // 1. Dapatkan Semester Aktif (Global)
        $semesterAktifGlobal = Semester::where('a_periode_aktif', '1')->first();
        if (!$semesterAktifGlobal) {
            $semesterAktifGlobal = Semester::orderBy('id_semester', 'desc')->first();
        }

        // 2. Tentukan Semester yang Sedang Dilihat
        $idSemesterDipilih = $request->get('id_semester', $semesterAktifGlobal->id_semester);
        $semesterDipilih = Semester::where('id_semester', $idSemesterDipilih)->first() ?: $semesterAktifGlobal;

        $isSemesterAktif = ($semesterDipilih->id_semester == $semesterAktifGlobal->id_semester);

        // 3. Ambil Daftar Riwayat Semester (untuk Dropdown)
        $riwayatIds = $mahasiswa->riwayatPendidikans()->pluck('id');
        $riwayatSemester = Semester::whereHas('kelasKuliah.pesertaKelasKuliah', function ($q) use ($riwayatIds) {
            $q->whereIn('riwayat_pendidikan_id', $riwayatIds);
        })
            ->orWhere('id_semester', $semesterAktifGlobal->id_semester)
            ->orderBy('id_semester', 'desc')
            ->distinct()
            ->get();

        // 4. Ambil Item KRS, dengan memfilter relasi jadwal sesuai tipe kelas mahasiswa
        $tipeKelas = $mahasiswa->tipe_kelas;
        $krsItems = PesertaKelasKuliah::with([
            'kelasKuliah.mataKuliah',
            'kelasKuliah.dosenPengajar.dosen',
            'kelasKuliah.jadwalKuliahs' => function ($query) use ($tipeKelas) {
                if ($tipeKelas) {
                    $query->whereIn('tipe_waktu', [$tipeKelas, 'Universal']);
                }
            }
        ])
            ->whereIn('riwayat_pendidikan_id', $riwayatIds)
            ->whereHas('kelasKuliah', function ($q) use ($semesterDipilih) {
                $q->where('id_semester', $semesterDipilih->id_semester);
            })
            ->get();

        // 5. Cek Periode & Bypass (Hanya jika di semester aktif)
        $periodeKrs = KrsPeriod::where('id_semester', $semesterDipilih->id_semester)->first();
        $canSubmit = false;

        if ($isSemesterAktif) {
            if ($periodeKrs && $periodeKrs->is_open) {
                $canSubmit = true;
            } elseif ($mahasiswa->bypass_krs_until && $mahasiswa->bypass_krs_until->isFuture()) {
                $canSubmit = true;
            }
        }

        return view('mahasiswa.krs.index', [
            'krsItems' => $krsItems,
            'semesterAktif' => $semesterDipilih,
            'isSemesterAktif' => $isSemesterAktif,
            'canSubmit' => $canSubmit,
            'mahasiswa' => $mahasiswa,
            'riwayatSemester' => $riwayatSemester
        ]);
    }

    public function submit(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswa;
        $semesterId = $request->id_semester;

        // Validasi Dosen PA
        if (!$mahasiswa->dosenPembimbing) {
            return back()->with('error', 'Gagal: Anda belum ditugaskan Dosen Pembimbing Akademik. Silakan hubungi admin.');
        }

        // Validasi Periode
        $periodeKrs = KrsPeriod::where('id_semester', $semesterId)->first();
        $isBypass = $mahasiswa->bypass_krs_until && $mahasiswa->bypass_krs_until->isFuture();

        if ((!$periodeKrs || !$periodeKrs->is_open) && !$isBypass) {
            return back()->with('error', 'Gagal: Masa pengisian KRS sudah ditutup atau belum dimulai.');
        }

        try {
            // Update status 'paket' -> 'pending'
            $updated = PesertaKelasKuliah::where('riwayat_pendidikan_id', $mahasiswa->riwayatAktif?->id)
                ->whereHas('kelasKuliah', function ($q) use ($semesterId) {
                    $q->where('id_semester', $semesterId);
                })
                ->where('status_krs', 'paket')
                ->update(['status_krs' => 'pending']);

            Log::info("CRUD_UPDATE: Mahasiswa mengajukan KRS", ['nim' => $mahasiswa->nim, 'count' => $updated]);

            return back()->with('success', 'KRS berhasil diajukan. Tunggu persetujuan Dosen PA.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal ajukan KRS", ['message' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function print(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswa;

        // Pilih semester dari request atau fallback ke aktif
        $idSemester = $request->get('id_semester');
        if ($idSemester) {
            $semester = Semester::where('id_semester', $idSemester)->first() ?: getActiveSemester();
        } else {
            $semester = getActiveSemester();
        }

        $riwayatIds = $mahasiswa->riwayatPendidikans()->pluck('id');

        $krsItems = PesertaKelasKuliah::with(['kelasKuliah.mataKuliah', 'kelasKuliah.dosenPengajar.dosen'])
            ->whereIn('riwayat_pendidikan_id', $riwayatIds)
            ->whereHas('kelasKuliah', fn($q) => $q->where('id_semester', $semester->id_semester))
            ->get();

        return view('shared.krs-print', compact('mahasiswa', 'semester', 'krsItems'));
    }
}
