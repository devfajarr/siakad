<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ruang extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_ruang',
        'nama_ruang',
        'kapasitas',
    ];

    /**
     * Get the jadwal_kuliahs for the ruang.
     */
    public function jadwalKuliahs()
    {
        return $this->hasMany(JadwalKuliah::class);
    }
}
