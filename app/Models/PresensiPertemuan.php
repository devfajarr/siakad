<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresensiPertemuan extends Model
{
    use HasFactory;

    protected $table = 'presensi_pertemuan';

    protected $fillable = [
        'id_kelas_kuliah',
        'id_dosen',
        'pertemuan_ke',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'materi',
        'metode_pembelajaran',
        // Monitoring
        'sumber_data',
        'status_sinkronisasi',
        'is_deleted_server',
        'last_synced_at',
        'last_push_at',
        'sync_action',
        'is_local_change',
        'is_deleted_local',
        'sync_error_message',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'last_synced_at' => 'datetime',
        'last_push_at' => 'datetime',
        'is_deleted_server' => 'boolean',
        'is_local_change' => 'boolean',
        'is_deleted_local' => 'boolean',
    ];

    /**
     * Relasi ke Kelas Kuliah.
     */
    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    /**
     * Relasi ke Dosen (Pengampu yang mengisi).
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }

    /**
     * Relasi ke Detail Presensi Mahasiswa.
     */
    public function presensiMahasiswas(): HasMany
    {
        return $this->hasMany(PresensiMahasiswa::class, 'presensi_pertemuan_id');
    }
}
