<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JadwalKuliah extends Model
{
    use HasFactory;

    protected $fillable = [
        'kelas_kuliah_id',
        'ruang_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'jenis_pertemuan',
    ];

    protected $casts = [
        'hari' => 'integer',
        // Opsional: jam_mulai dan jam_selesai biarkan default format 'H:i:s'
    ];

    /**
     * Get the kelas_kuliah that owns the jadwal.
     */
    public function kelasKuliah()
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    /**
     * Get the ruang that owns the jadwal.
     */
    public function ruang()
    {
        return $this->belongsTo(Ruang::class, 'ruang_id');
    }
}
