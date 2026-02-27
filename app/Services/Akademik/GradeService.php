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
}
