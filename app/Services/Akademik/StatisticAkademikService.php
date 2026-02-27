<?php

namespace App\Services\Akademik;

use App\Models\PesertaKelasKuliah;
use App\Models\Semester;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticAkademikService
{
    /**
     * Ambil data KHS untuk satu semester tertentu.
     */
    public function getKhsPerSemester(string $riwayatPendidikanId, string $semesterId): Collection
    {
        return PesertaKelasKuliah::where('riwayat_pendidikan_id', $riwayatPendidikanId)
            ->whereHas('kelasKuliah', function ($query) use ($semesterId) {
                $query->where('id_semester', $semesterId);
            })
            ->with(['kelasKuliah.mataKuliah', 'kelasKuliah.semester'])
            ->get()
            ->map(function ($peserta) {
                // SKS bisa dari MataKuliah atau fallback ke KelasKuliah
                $sks = $peserta->kelasKuliah->mataKuliah->sks ?? $peserta->kelasKuliah->sks_mk ?? 0;
                $indeks = $peserta->nilai_indeks ?? 0;

                $peserta->sks_item = (float) $sks;
                $peserta->bobot_item = round($sks * $indeks, 2);

                return $peserta;
            });
    }

    /**
     * Hitung Indeks Prestasi Semester (IPS).
     */
    public function calculateIps(string $riwayatPendidikanId, string $semesterId): array
    {
        $khs = $this->getKhsPerSemester($riwayatPendidikanId, $semesterId);

        $totalSks = $khs->sum('sks_item');
        $totalBobot = $khs->sum('bobot_item');

        $ips = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;

        return [
            'total_sks' => $totalSks,
            'total_bobot' => $totalBobot,
            'ips' => $ips
        ];
    }

    /**
     * Hitung Indeks Prestasi Kumulatif (IPK).
     * Aturan: Matakuliah mengulang diambil NILAI TERTINGGI.
     */
    public function calculateIpk(string $riwayatPendidikanId, ?string $untilSemesterId = null): array
    {
        $query = PesertaKelasKuliah::where('riwayat_pendidikan_id', $riwayatPendidikanId)
            ->whereNotNull('nilai_indeks')
            ->with(['kelasKuliah.mataKuliah']);

        if ($untilSemesterId) {
            $query->whereHas('kelasKuliah', function ($q) use ($untilSemesterId) {
                $q->where('id_semester', '<=', $untilSemesterId);
            });
        }

        $allData = $query->get();

        // Logika Mengulang: Group by Mata Kuliah (id_matkul) dan ambil yang nilai_indeks nya tertinggi
        $filtered = $allData->groupBy(function ($item) {
            return $item->kelasKuliah->id_matkul;
        })->map(function ($group) {
            return $group->sortByDesc('nilai_indeks')->first();
        });

        $totalSks = 0;
        $totalBobot = 0;

        foreach ($filtered as $peserta) {
            $sks = (float) ($peserta->kelasKuliah->mataKuliah->sks ?? $peserta->kelasKuliah->sks_mk ?? 0);
            $indeks = (float) ($peserta->nilai_indeks ?? 0);

            $totalSks += $sks;
            $totalBobot += ($sks * $indeks);
        }

        $ipk = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;

        return [
            'total_sks' => $totalSks,
            'total_bobot' => round($totalBobot, 2),
            'ipk' => $ipk,
            'count_matkul' => $filtered->count()
        ];
    }
}
