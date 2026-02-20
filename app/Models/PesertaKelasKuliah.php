<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaKelasKuliah extends Model
{
    use HasFactory;

    protected $table = 'peserta_kelas_kuliah';

    // ─── Sync Status Constants ──────────────────────────────
    const STATUS_SYNCED = 'synced';
    const STATUS_CREATED_LOCAL = 'created_local';
    const STATUS_UPDATED_LOCAL = 'updated_local';
    const STATUS_DELETED_LOCAL = 'deleted_local';
    const STATUS_PENDING_PUSH = 'pending_push';
    const STATUS_PUSH_SUCCESS = 'push_success';
    const STATUS_PUSH_FAILED = 'push_failed';

    protected $fillable = [
        'id_kelas_kuliah',
        'id_registrasi_mahasiswa',
        'nilai_angka',
        'nilai_akhir',
        'nilai_huruf',
        'nilai_indeks',
        // Monitoring
        'sumber_data',
        'status_sinkronisasi',
        'is_deleted_server',
        'last_synced_at',
        'last_push_at',
        'sync_error_message',
    ];

    protected $casts = [
        'nilai_angka' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'nilai_indeks' => 'decimal:2',
        'is_deleted_server' => 'boolean',
        'last_synced_at' => 'datetime',
        'last_push_at' => 'datetime',
    ];

    // ─── Scopes ─────────────────────────────────────────────

    /**
     * Scope: Data yang sudah sinkron dengan server.
     */
    public function scopeSynced($query)
    {
        return $query->where('status_sinkronisasi', self::STATUS_SYNCED);
    }

    /**
     * Scope: Data yang pending untuk di-push ke server.
     */
    public function scopePendingPush($query)
    {
        return $query->whereIn('status_sinkronisasi', [
            self::STATUS_CREATED_LOCAL,
            self::STATUS_UPDATED_LOCAL,
            self::STATUS_PENDING_PUSH,
        ]);
    }

    /**
     * Scope: Data aktif (tidak dihapus di server).
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted_server', false);
    }

    // ─── Relationships ──────────────────────────────────────

    /**
     * Relasi ke Kelas Kuliah.
     */
    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    /**
     * Relasi ke Riwayat Pendidikan (registrasi mahasiswa).
     * id_registrasi_mahasiswa di Feeder = id_riwayat_pendidikan di lokal.
     */
    public function riwayatPendidikan(): BelongsTo
    {
        return $this->belongsTo(RiwayatPendidikan::class, 'id_registrasi_mahasiswa', 'id_riwayat_pendidikan');
    }
}
