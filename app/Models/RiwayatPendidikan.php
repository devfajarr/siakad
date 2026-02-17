<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPendidikan extends Model
{
    protected $table = 'riwayat_pendidikans';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'tanggal_keluar' => 'date',
        'tanggal_sk_yudisium' => 'date',
        'biaya_masuk' => 'decimal:2',
        'sks_diakui' => 'integer',
        'is_synced' => 'boolean',
        'last_sync' => 'datetime',
    ];


    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id');
    }

    public function perguruanTinggi()
    {
        return $this->belongsTo(ProfilPerguruanTinggi::class, 'id_perguruan_tinggi', 'id_perguruan_tinggi');
    }

    public function prodi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_prodi', 'id_prodi');
    }

    public function getIdJenisDaftarAttribute($value)
    {
        return trim($value);
    }

    public function getIdJalurDaftarAttribute($value)
    {
        return trim($value);
    }

    public function getIdPeriodeMasukAttribute($value)
    {
        return trim($value);
    }
}
