<?php

use App\Models\Semester;
use Illuminate\Support\Facades\Cache;

if (!function_exists('getActiveSemester')) {
    /**
     * Dapatkan instance object Semester yang berstatus Aktif.
     * Menggunakan caching selamanya (ingat untuk clear cache saat mengubah semester aktif).
     *
     * @return \App\Models\Semester|null
     */
    function getActiveSemester()
    {
        return Cache::rememberForever('active_semester_object', function () {
            // 1. Cek apakah ada yang ditandai manual sebagai aktif
            $manual = Semester::where('a_periode_aktif', 1)->first();
            if ($manual) {
                return $manual;
            }

            // 2. Jika tidak ada, gunakan logika Smart-Selection (Terbesar & Prioritas Reguler)
            return Semester::getSmartDefault()->first();
        });
    }
}

if (!function_exists('getActiveSemesterId')) {
    /**
     * Helper cepat untuk mendapatkan hanya ID Semester Aktif (biasanya untuk default query/dropdown).
     *
     * @return string|null
     */
    function getActiveSemesterId()
    {
        $activeSemester = getActiveSemester();
        return $activeSemester ? $activeSemester->id_semester : null;
    }
}
