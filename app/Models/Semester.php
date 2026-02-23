<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semesters';
    protected $primaryKey = 'id_semester';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_semester',
        'nama_semester',
        'id_tahun_ajaran',
        'semester',
        'a_periode_aktif', // 1 = Aktif, 0 = Tidak
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Mengambil query semester dengan prioritas "Smart-Selection":
     * 1. Tahun terbaru (Digit 1-4)
     * 2. Tipe Semester (Digit terakhir): 2 (Genap) > 1 (Ganjil) > 3 (Pendek)
     */
    public static function getSmartDefault()
    {
        return self::query()
            ->orderByRaw('SUBSTR(id_semester, 1, 4) DESC')
            ->orderByRaw("CASE 
                WHEN SUBSTR(id_semester, 5, 1) = '2' THEN 1 
                WHEN SUBSTR(id_semester, 5, 1) = '1' THEN 2 
                ELSE 3 
            END ASC");
    }

    /**
     * Menjadikan semester ini sebagai satu-satunya yang aktif secara global.
     * Menggunakan Database Transaction & melibas Cache Helper.
     */
    public static function setActivePeriod($semesterId)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($semesterId) {
            // 1. Nonaktifkan semua semester
            self::query()->update(['a_periode_aktif' => 0]);

            // 2. Aktifkan hanya 1 semester ini
            self::where('id_semester', $semesterId)->update(['a_periode_aktif' => 1]);
        });

        // 3. Hancurkan Cache Global Helper
        \Illuminate\Support\Facades\Cache::forget('active_semester_object');
    }
}
