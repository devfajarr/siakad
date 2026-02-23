<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DaftarKelasMahasiswaController extends Controller
{
    /**
     * Tampilkan daftar seluruh kelas yang diambil mahasiswa di semester aktif
     */
    public function index(Request $request)
    {
        if (!session()->has('active_role')) {
            session(['active_role' => 'Mahasiswa']);
        }

        $user = auth()->user();
        $mahasiswa = $user->mahasiswa;

        if (!$mahasiswa) {
            return redirect()->route('dashboard')->with('error', 'Profil mahasiswa tidak ditemukan.');
        }

        $riwayatAktif = $mahasiswa->riwayatAktif;
        $semesterId = $request->get('semester_id', getActiveSemesterId());

        // Ambil daftar kelas via PesertaKelasKuliah (KRS)
        $kelasKuliahs = KelasKuliah::whereHas('pesertaKelasKuliah', function ($query) use ($riwayatAktif) {
            $query->where('riwayat_pendidikan_id', $riwayatAktif->id);
        })
            ->where('id_semester', $semesterId)
            ->with(['mataKuliah', 'dosenPengajars.dosen', 'dosenPengajars.dosenAliasLokal', 'jadwalKuliahs.ruangan'])
            ->get();

        $semesters = \App\Models\Semester::where('id_semester', '<=', getActiveSemesterId())
            ->orderBy('id_semester', 'desc')
            ->limit(20)
            ->get();

        Log::info("MAHASISWA_DAFTAR_KELAS: Diakses oleh {$mahasiswa->nama_mahasiswa}", ['semester_id' => $semesterId]);

        return view('mahasiswa.kelas.index', compact('kelasKuliahs', 'semesters', 'semesterId'));
    }

    /**
     * Tampilkan detail kelas perkuliahan bagi mahasiswa
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $mahasiswa = $user->mahasiswa;
        $riwayatAktif = $mahasiswa->riwayatAktif;

        $kelasKuliah = KelasKuliah::whereHas('pesertaKelasKuliah', function ($query) use ($riwayatAktif) {
            $query->where('riwayat_pendidikan_id', $riwayatAktif->id);
        })
            ->with(['mataKuliah', 'dosenPengajars.dosen', 'dosenPengajars.dosenAliasLokal', 'jadwalKuliahs.ruangan'])
            ->findOrFail($id);

        return view('mahasiswa.kelas.show', compact('kelasKuliah'));
    }
}
