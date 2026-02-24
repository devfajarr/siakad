<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dosen extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'external_id',
        'nidn',
        'nip',
        'nama',
        'nama_alias',
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

    public function pengajaranKelas(): HasMany
    {
        return $this->hasMany(DosenPengajarKelasKuliah::class, 'id_dosen');
    }

    public function akun()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Presensi yang diinput oleh Dosen ini.
     */
    /**
     * Relasi ke Presensi yang diinput oleh Dosen ini.
     */
    public function presensiPertemuans(): HasMany
    {
        return $this->hasMany(PresensiPertemuan::class, 'id_dosen');
    }

    /**
     * Accessor untuk nama tampilan dosen berdasarkan role user yang login.
     * Admin melihat nama asli, Mahasiswa/Dosen melihat alias jika ada.
     */
    public function getNamaTampilanAttribute(): string
    {
        $user = auth()->user();
        $namaAsli = $this->nama;
        $namaAlias = $this->nama_alias;

        // Jika Admin, tampilkan nama asli
        if ($user && $user->hasRole('admin')) {
            return $namaAsli;
        }

        // Selain Admin, tampilkan alias jika tidak kosong
        return !empty($namaAlias) ? $namaAlias : $namaAsli;
    }
    /**
     * Accessor untuk nama tampilan dosen khusus di halaman Admin.
     * Format: Nama Asli (Nama Alias) jika ada alias, atau Nama Asli jika tidak ada.
     */
    public function getNamaAdminDisplayAttribute(): string
    {
        $namaAsli = $this->nama;
        $namaAlias = $this->nama_alias;

        if (!empty($namaAlias)) {
            return "{$namaAsli} ({$namaAlias})";
        }

        return $namaAsli;
    }
}
