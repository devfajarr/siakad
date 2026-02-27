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
        'id_feeder',
        'riwayat_pendidikan_id',
        'nilai_angka',
        'nilai_akhir',
        'nilai_huruf',
        'nilai_indeks',
        // Komponen Nilai
        'tugas1',
        'tugas2',
        'tugas3',
        'tugas4',
        'tugas5',
        'aktif',
        'etika',
        'uts',
        'uas',
        // Monitoring
        'sumber_data',
        'status_sinkronisasi',
        'is_deleted_server',
        'last_synced_at',
        'last_push_at',
        'sync_error_message',
        'status_krs',
        'acc_by',
        'last_acc_at',
        'sync_action',
        'is_local_change',
        'is_deleted_local',
    ];

    /**
     * Accessor: Fallback to external_id if id_feeder is null.
     */
    public function getIdFeederAttribute($value)
    {
        return $value ?? $this->external_id;
    }

    protected $casts = [
        'nilai_angka' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
        'nilai_indeks' => 'decimal:2',
        'tugas1' => 'decimal:2',
        'tugas2' => 'decimal:2',
        'tugas3' => 'decimal:2',
        'tugas4' => 'decimal:2',
        'tugas5' => 'decimal:2',
        'aktif' => 'decimal:2',
        'etika' => 'decimal:2',
        'uts' => 'decimal:2',
        'uas' => 'decimal:2',
        'is_deleted_server' => 'boolean',
        'is_local_change' => 'boolean',
        'is_deleted_local' => 'boolean',
        'last_synced_at' => 'datetime',
        'last_push_at' => 'datetime',
        'last_acc_at' => 'datetime',
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
     * Secara lokal, tabel ini merelasikan ke `riwayat_pendidikan_id` demi mengakomodir 
     * mahasiswa baru (sumber_data: lokal) yang belum memiliki `id_registrasi_mahasiswa` dari Feeder.
     */
    public function riwayatPendidikan(): BelongsTo
    {
        return $this->belongsTo(RiwayatPendidikan::class, 'riwayat_pendidikan_id', 'id');
    }

    /**
     * Relasi fallback ke Feeder UUID (jika data sepenuhnya bersumber dari server dan belum dimigrasi).
     */
    public function riwayatPendidikanFeeder(): BelongsTo
    {
        return $this->belongsTo(RiwayatPendidikan::class, 'id_registrasi_mahasiswa', 'id_riwayat_pendidikan');
    }
}
