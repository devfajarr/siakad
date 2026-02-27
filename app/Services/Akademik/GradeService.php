<?php

namespace App\Services\Akademik;

use App\Models\SkalaNilaiProdi;
use Illuminate\Support\Facades\Log;

class GradeService
{
    /**
     * Konversi nilai angka ke nilai huruf dan indeks berdasarkan prodi.
     * 
     * @param string $id_prodi UUID Prodi di server/lokal
     * @param float $nilai_angka Nilai numerik 0-100
     * @return array|null
     */
    public function convertToGrade(string $id_prodi, float $nilai_angka)
    {
        $today = date('Y-m-d');

        $skala = SkalaNilaiProdi::where('id_prodi', $id_prodi)
            ->where('bobot_minimum', '<=', $nilai_angka)
            ->where('bobot_maksimum', '>=', $nilai_angka)
            ->where('tanggal_mulai_efektif', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('tanggal_akhir_efektif')
                    ->orWhere('tanggal_akhir_efektif', '>=', $today);
            })
            ->orderBy('nilai_indeks', 'desc')
            ->first();

        if (!$skala) {
            Log::warning("GRADE_CONVERSION_FAILED: Skala nilai tidak ditemukan", [
                'id_prodi' => $id_prodi,
                'nilai_angka' => $nilai_angka
            ]);
            return null;
        }

        return [
            'nilai_huruf' => $skala->nilai_huruf,
            'nilai_indeks' => $skala->nilai_indeks,
        ];
    }

    /**
     * Hitung nilai akhir berdasarkan komponen nilai.
     * 
     * @param array $components [tugas1...5, aktif, etika, uts, uas]
     * @param int $totalHadir Jumlah kehadiran mahasiswa
     * @param int $targetPertemuan Target pertemuan (default: 14)
     * @return float
     */
    public function calculateFinalScore(array $components, int $totalHadir, int $targetPertemuan = 14): float
    {
        $targetPertemuan = $targetPertemuan > 0 ? $targetPertemuan : 14;

        // 1. Rata-rata Tugas (25%)
        $tugas = [
            $components['tugas1'] ?? 0,
            $components['tugas2'] ?? 0,
            $components['tugas3'] ?? 0,
            $components['tugas4'] ?? 0,
            $components['tugas5'] ?? 0,
        ];
        $avgTugas = count($tugas) > 0 ? array_sum($tugas) / count($tugas) : 0;
        $scoreTugas = $avgTugas * 0.25;

        // 2. Aktif & Etika (Masing-masing 5%)
        $scoreAktif = ($components['aktif'] ?? 0) * 0.05;
        $scoreEtika = ($components['etika'] ?? 0) * 0.05;

        // 3. Presensi (15%)
        $scorePresensi = ($totalHadir / $targetPertemuan) * 15;
        // Cap di 15 jika kehadiran > target (ekstra)
        $scorePresensi = min($scorePresensi, 15);

        // 4. UTS & UAS (Masing-masing 25%)
        $scoreUTS = ($components['uts'] ?? 0) * 0.25;
        $scoreUAS = ($components['uas'] ?? 0) * 0.25;

        return round($scoreTugas + $scoreAktif + $scoreEtika + $scorePresensi + $scoreUTS + $scoreUAS, 2);
    }
}
