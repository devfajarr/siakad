<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenPengajarKelasKuliah extends Model
{
    use HasFactory;

    protected $table = 'dosen_pengajar_kelas_kuliah';

    // ─── Constants ──────────────────────────────────────────
    public const JENIS_EVALUASI = [
        '1' => 'Evaluasi Akademik',
        '2' => 'Aktivitas Partisipatif',
        '3' => 'Hasil Proyek',
        '4' => 'Kognitif / Pengetahuan',
    ];

    // ─── Sync Status Constants ──────────────────────────────
    const STATUS_SYNCED = 'synced';

    const STATUS_CREATED_LOCAL = 'created_local';

    const STATUS_UPDATED_LOCAL = 'updated_local';

    const STATUS_DELETED_LOCAL = 'deleted_local';

    const STATUS_PENDING_PUSH = 'pending_push';

    const STATUS_PUSH_SUCCESS = 'push_success';

    const STATUS_PUSH_FAILED = 'push_failed';

    protected $fillable = [
        'id_aktivitas_mengajar',
        'id_kelas_kuliah',
        'id_dosen',
        'id_registrasi_dosen',
        // Dosen Alias
        'id_dosen_alias',           // FK ke dosens.id (Pusat - untuk push)
        'id_registrasi_dosen_alias', // external_id dosen alias (Pusat - untuk push)
        'id_dosen_alias_lokal',     // FK ke dosens.id (Lokal - untuk Select2 Alias)
        'dosen_alias',              // Nama dosen honorer (Lokal - backup string)
        // Data Mengajar
        'sks_substansi',
        'rencana_minggu_pertemuan',
        'realisasi_minggu_pertemuan',
        'jenis_evaluasi',
        'substansi_pilar',
        // Monitoring Sinkronisasi
        'sumber_data',
        'status_sinkronisasi',
        'sync_action',
        'is_local_change',
        'is_deleted_local',
        'is_deleted_server',
        'last_synced_at',
        'last_push_at',
        'sync_error_message',
    ];

    protected $casts = [
        'sks_substansi' => 'decimal:2',
        'rencana_minggu_pertemuan' => 'integer',
        'realisasi_minggu_pertemuan' => 'integer',
        'is_deleted_server' => 'boolean',
        'is_deleted_local' => 'boolean',
        'is_local_change' => 'boolean',
        'last_synced_at' => 'datetime',
        'last_push_at' => 'datetime',
    ];

    // ─── Scopes ─────────────────────────────────────────────

    public function scopeSynced($query)
    {
        return $query->where('status_sinkronisasi', self::STATUS_SYNCED);
    }

    public function scopePendingPush($query)
    {
        return $query->whereIn('status_sinkronisasi', [
            self::STATUS_CREATED_LOCAL,
            self::STATUS_UPDATED_LOCAL,
            self::STATUS_PENDING_PUSH,
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_deleted_server', false);
    }

    /**
     * Scope: Record yang menggunakan dosen alias.
     */
    public function scopeWithAlias($query)
    {
        return $query->whereNotNull('id_dosen_alias');
    }

    // ─── Relationships ──────────────────────────────────────

    /**
     * Kelas Kuliah.
     */
    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    /**
     * Dosen yang mengajar secara nyata (bisa lokal tanpa NIDN).
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }

    public function dosenPenugasan()
    {
        return $this->belongsTo(DosenPenugasan::class, 'id_registrasi_dosen', 'external_id');
    }

    /**
     * Dosen alias (Pusat) yang ID registrasi-nya dipakai saat push ke server.
     * Null jika dosen utama sudah terdaftar di server.
     */
    public function dosenAlias(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen_alias');
    }

    /**
     * Dosen alias (Lokal) yang dipilih secara manual via Select2.
     */
    public function dosenAliasLokal(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen_alias_lokal');
    }

    // ─── Helper Methods ─────────────────────────────────────

    /**
     * Mendapatkan id_registrasi_dosen yang dipakai untuk push ke server.
     * Prioritas: alias pusat → dosen utama.
     */
    public function getRegistrasiDosenForPush(): ?string
    {
        return $this->id_registrasi_dosen_alias ?? $this->id_registrasi_dosen;
    }

    /**
     * Apakah record ini menggunakan dosen alias?
     * Cek alias pusat maupun alias lokal.
     */
    public function isUsingAlias(): bool
    {
        return !is_null($this->id_dosen_alias) || !is_null($this->id_dosen_alias_lokal) || !is_null($this->dosen_alias);
    }
}
