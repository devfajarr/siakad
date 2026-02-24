<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\KelasKuliah;
use Illuminate\Http\Request;

class DaftarKelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $dosenId = auth()->user()->dosen->id;
        $semesterId = $request->get('semester_id', getActiveSemesterId());

        $kelasKuliahs = KelasKuliah::whereHas('dosenPengajars', function ($query) use ($dosenId) {
            $query->where('id_dosen', $dosenId)
                ->orWhere('id_dosen_alias_lokal', $dosenId);
        })
            ->where('id_semester', $semesterId)
            ->withCount('pesertaKelasKuliah')
            ->with(['mataKuliah', 'dosenPengajars.dosen', 'jadwalKuliahs.ruang'])
            ->get();

        // Ambil daftar semester (hanya yang sedang berjalan dan yang lampau)
        $semesters = \App\Models\Semester::where('id_semester', '<=', getActiveSemesterId())
            ->orderBy('id_semester', 'desc')
            ->get();

        return view('dosen.daftar-kelas.index', compact('kelasKuliahs', 'semesters', 'semesterId'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $dosenId = auth()->user()->dosen->id;
        $semesterId = getActiveSemesterId();

        $kelasKuliah = KelasKuliah::whereHas('dosenPengajars', function ($query) use ($dosenId) {
            $query->where('id_dosen', $dosenId)
                ->orWhere('id_dosen_alias_lokal', $dosenId);
        })
            ->where('id_semester', $semesterId)
            ->withCount('pesertaKelasKuliah')
            ->with(['mataKuliah', 'dosenPengajars.dosen', 'jadwalKuliahs.ruang', 'pesertaKelasKuliah.riwayatPendidikan.mahasiswa.riwayatAktif.prodi'])
            ->findOrFail($id);

        return view('dosen.daftar-kelas.show', compact('kelasKuliah'));
    }
}
