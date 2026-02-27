<?php

namespace App\Services\Akademik;

use App\Models\ProgramStudi;
use App\Models\KelasKuliah;

class RekapNilaiService
{
    /**
     * Mengambil statistik progres pengisian nilai per Program Studi.
     * 
     * @param string $semesterId
     * @return \Illuminate\Support\Collection
     */
    public function getProdiProgress(string $semesterId)
    {
        return ProgramStudi::select('id_prodi', 'nama_program_studi')
            ->orderBy('id_jenjang_pendidikan')
            ->orderBy('nama_program_studi')
            ->get()
            ->map(function ($prodi) use ($semesterId) {
                $stats = KelasKuliah::where('id_prodi', $prodi->id_prodi)
                    ->where('id_semester', $semesterId)
                    ->withCount([
                        'pesertaKelasKuliah as total_peserta',
                        'pesertaKelasKuliah as terisi_count' => function ($q) {
                            $q->whereNotNull('nilai_angka');
                        }
                    ])
                    ->get();

                $totalKelas = $stats->count();
                $totalMhs = $stats->sum('total_peserta');
                $totalTerisi = $stats->sum('terisi_count');
                $persentase = $totalMhs > 0 ? round(($totalTerisi / $totalMhs) * 100, 2) : 0;

                $kelasLocked = $stats->where('is_locked', true)->count();

                return (object) [
                    'id_prodi' => $prodi->id_prodi,
                    'nama_prodi' => $prodi->nama_program_studi,
                    'total_kelas' => $totalKelas,
                    'total_mhs' => $totalMhs,
                    'total_terisi' => $totalTerisi,
                    'persentase' => $persentase,
                    'kelas_locked' => $kelasLocked
                ];
            });
    }

    /**
     * Mengambil detail daftar kelas dan progres per kelas untuk prodi tertentu.
     * 
     * @param string $prodiId
     * @param string $semesterId
     * @return \Illuminate\Support\Collection
     */
    public function getClassDetailsByProdi(string $prodiId, string $semesterId)
    {
        return KelasKuliah::where('id_prodi', $prodiId)
            ->where('id_semester', $semesterId)
            ->with(['mataKuliah', 'dosenPengajar.dosen'])
            ->withCount([
                'pesertaKelasKuliah as total_peserta',
                'pesertaKelasKuliah as terisi_count' => function ($q) {
                    $q->whereNotNull('nilai_angka');
                }
            ])
            ->get()
            ->map(function ($kelas) {
                $kelas->persentase = $kelas->total_peserta > 0
                    ? round(($kelas->terisi_count / $kelas->total_peserta) * 100, 2)
                    : 0;

                $kelas->dosen_pengampu = $kelas->dosenPengajar->map(function ($dp) {
                    return $dp->dosen->nama_tampilan ?? 'Tanpa Nama';
                })->implode(', ');

                return $kelas;
            });
    }
}
