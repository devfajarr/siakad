<?php

namespace App\Services;

use App\Models\JadwalKuliah;
use App\Models\KelasKuliah;

class JadwalKuliahService
{
    /**
     * Memeriksa apakah terjadi bentrok Ruangan (Double Booking)
     * atau bentrok Dosen Pengajar (Double Teaching)
     * pada satu rentang waktu yang direquest.
     *
     * @param int|string $ruangId
     * @param int $hari (1=Senin ... 7=Minggu)
     * @param string $jamMulai (format H:i:s)
     * @param string $jamSelesai (format H:i:s)
     * @param int|string $kelasKuliahId (Id kelas yang sedang dinilai)
     * @param int|string|null $ignoreJadwalId (id jadwal saat edit agar tak nge-clash dirinya sendiri)
     * @throws \Exception
     */
    public function checkBentrok($ruangId, $hari, $jamMulai, $jamSelesai, $kelasKuliahId, $ignoreJadwalId = null)
    {
        // 1. Cek Bentrok Ruangan
        $queryRuang = JadwalKuliah::where('ruang_id', $ruangId)
            ->where('hari', $hari)
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai);

        if ($ignoreJadwalId) {
            $queryRuang->where('id', '!=', $ignoreJadwalId);
        }

        if ($queryRuang->exists()) {
            throw new \Exception("Ruangan sudah dipakai pada jam tersebut oleh kelas lain.");
        }

        // 2. Cek Bentrok Dosen Pengajar
        $kelas = KelasKuliah::with('dosenPengajars')->find($kelasKuliahId);

        if ($kelas && $kelas->dosenPengajars->count() > 0) {
            $dosenIds = $kelas->dosenPengajars->pluck('id_dosen')->toArray();

            $queryDosen = JadwalKuliah::whereHas('kelasKuliah.dosenPengajars', function ($query) use ($dosenIds) {
                $query->whereIn('id_dosen', $dosenIds);
            })
                ->where('hari', $hari)
                ->where('jam_mulai', '<', $jamSelesai)
                ->where('jam_selesai', '>', $jamMulai);

            if ($ignoreJadwalId) {
                $queryDosen->where('id', '!=', $ignoreJadwalId);
            }

            // Pengecualian tambahan: Boleh sama jam/hari ASALKAN itu memang Kelas yang Sama (satu entitas header)
            // (misal skenario aneh: 1 kelas punya 2 dosen tapi diinput double jadwal, walau jarang terjadi)
            $queryDosen->where('kelas_kuliah_id', '!=', $kelasKuliahId);

            if ($queryDosen->exists()) {
                throw new \Exception("Dosen pengajar sedang mengajar di kelas lain pada jam tersebut (terjadi overlapping jadwal dosen).");
            }
        }

        return true;
    }
}
