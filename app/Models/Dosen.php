<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dosen extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'nidn',
        'nip',
        'nama',
        'email',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'id_agama',
        'id_status_aktif',
        'status_sinkronisasi',
        'is_active',
        'is_struktural',
        'is_pengajar',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_struktural' => 'boolean',
        'is_pengajar' => 'boolean',
        'tanggal_lahir' => 'date',
    ];

    // Scopes
    public function scopeLokal($query)
    {
        return $query->where('status_sinkronisasi', 'lokal');
    }

    public function scopePusat($query)
    {
        return $query->where('status_sinkronisasi', 'pusat');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_active', true);
    }

    // Relations
    public function penugasans(): HasMany
    {
        return $this->hasMany(DosenPenugasan::class, 'id_dosen');
    }

    public function riwayatFungsionals(): HasMany
    {
        return $this->hasMany(DosenRiwayatFungsional::class, 'id_dosen');
    }

    public function riwayatPangkats(): HasMany
    {
        return $this->hasMany(DosenRiwayatPangkat::class, 'id_dosen');
    }

    public function riwayatPendidikans(): HasMany
    {
        return $this->hasMany(DosenRiwayatPendidikan::class, 'id_dosen');
    }

    public function riwayatSertifikasis(): HasMany
    {
        return $this->hasMany(DosenRiwayatSertifikasi::class, 'id_dosen');
    }

    public function riwayatPenelitians(): HasMany
    {
        return $this->hasMany(DosenRiwayatPenelitian::class, 'id_dosen');
    }

    public function kelasDosen(): HasMany
    {
        return $this->hasMany(KelasDosen::class, 'dosen_id');
    }
}
