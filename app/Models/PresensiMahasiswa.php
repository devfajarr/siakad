<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'presensi_mahasiswa';

    protected $fillable = [
        'presensi_pertemuan_id',
        'riwayat_pendidikan_id',
        'status_kehadiran',
        'keterangan',
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
        'last_synced_at' => 'datetime',
        'last_push_at' => 'datetime',
        'is_deleted_server' => 'boolean',
        'is_local_change' => 'boolean',
        'is_deleted_local' => 'boolean',
    ];

    /**
     * Relasi ke Header (Pertemuan).
     */
    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(PresensiPertemuan::class, 'presensi_pertemuan_id');
    }

    /**
     * Relasi ke Riwayat Pendidikan (Mahasiswa).
     */
    public function riwayatPendidikan(): BelongsTo
    {
        return $this->belongsTo(RiwayatPendidikan::class, 'riwayat_pendidikan_id');
    }
}
