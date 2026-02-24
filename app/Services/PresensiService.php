<?php

namespace App\Services;

use App\Models\PresensiPertemuan;
use App\Models\PresensiMahasiswa;
use App\Models\KelasKuliah;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PresensiService
{
    /**
     * Simpan atau Update Jurnal dan Daftar Hadir.
     * 
     * @param array $pertemuanData [id_kelas_kuliah, id_dosen, pertemuan_ke, tanggal, jam_mulai, jam_selesai, materi, metode_pembelajaran]
     * @param array $mahasiswaData [[riwayat_pendidikan_id, status_kehadiran, keterangan], ...]
     */
    public function simpanPresensi(array $pertemuanData, array $mahasiswaData)
    {
        return DB::transaction(function () use ($pertemuanData, $mahasiswaData) {
            // 1. Simpan Header (Jurnal)
            $pertemuan = PresensiPertemuan::updateOrCreate(
                [
                    'id_kelas_kuliah' => $pertemuanData['id_kelas_kuliah'],
                    'pertemuan_ke' => $pertemuanData['pertemuan_ke'],
                ],
                $pertemuanData
            );

            Log::info("CRUD_CREATE/UPDATE: [PresensiPertemuan] berhasil disimpan", [
                'id' => $pertemuan->id,
                'kelas' => $pertemuan->id_kelas_kuliah,
                'pertemuan' => $pertemuan->pertemuan_ke
            ]);

            // 2. Simpan Detail Kehadiran Mahasiswa
            foreach ($mahasiswaData as $mhs) {
                PresensiMahasiswa::updateOrCreate(
                    [
                        'presensi_pertemuan_id' => $pertemuan->id,
                        'riwayat_pendidikan_id' => $mhs['riwayat_pendidikan_id'],
                    ],
                    [
                        'status_kehadiran' => $mhs['status_kehadiran'],
                        'keterangan' => $mhs['keterangan'] ?? null,
                        'sumber_data' => 'lokal',
                        'status_sinkronisasi' => 'created_local'
                    ]
                );
            }

            Log::info("CRUD_BULK_CREATE: [PresensiMahasiswa] berhasil disimpan", [
                'pertemuan_id' => $pertemuan->id,
                'count' => count($mahasiswaData)
            ]);

            return $pertemuan;
        });
    }

    /**
     * Hitung Rekapitulasi Kehadiran Mahasiswa per Kelas.
     */
    public function getRekapMahasiswa($idKelasKuliah)
    {
        $peserta = DB::table('peserta_kelas_kuliah as pkk')
            ->join('riwayat_pendidikans as rp', 'pkk.riwayat_pendidikan_id', '=', 'rp.id')
            ->join('mahasiswas as m', 'rp.id_mahasiswa', '=', 'm.id')
            ->where('pkk.id_kelas_kuliah', $idKelasKuliah)
            ->select('m.nama_mahasiswa', 'rp.nim', 'rp.id as rp_id')
            ->get();

        $totalPertemuan = PresensiPertemuan::where('id_kelas_kuliah', $idKelasKuliah)->count();

        foreach ($peserta as $p) {
            $hadir = PresensiMahasiswa::where('riwayat_pendidikan_id', $p->rp_id)
                ->whereHas('pertemuan', function ($q) use ($idKelasKuliah) {
                    $q->where('id_kelas_kuliah', $idKelasKuliah);
                })
                ->where('status_kehadiran', 'H')
                ->count();

            $p->total_hadir = $hadir;
            $p->total_pertemuan = $totalPertemuan;
            $p->persentase = config('academic.target_pertemuan') > 0 ? round(($hadir / config('academic.target_pertemuan')) * 100, 2) : 0;
        }

        return $peserta;
    }
}
