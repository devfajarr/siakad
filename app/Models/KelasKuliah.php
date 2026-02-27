<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KelasKuliah extends Model
{
    use HasFactory;

    protected $table = 'kelas_kuliah';

    // ─── Sync Status Constants ──────────────────────────────
    const STATUS_SYNCED = 'synced';
    const STATUS_CREATED_LOCAL = 'created_local';
    const STATUS_UPDATED_LOCAL = 'updated_local';
    const STATUS_DELETED_LOCAL = 'deleted_local';
    const STATUS_PENDING_PUSH = 'pending_push';
    const STATUS_PUSH_SUCCESS = 'push_success';
    const STATUS_PUSH_FAILED = 'push_failed';

    const ALL_STATUSES = [
        self::STATUS_SYNCED,
        self::STATUS_CREATED_LOCAL,
        self::STATUS_UPDATED_LOCAL,
        self::STATUS_DELETED_LOCAL,
        self::STATUS_PENDING_PUSH,
        self::STATUS_PUSH_SUCCESS,
        self::STATUS_PUSH_FAILED,
    ];

    protected $fillable = [
        'id_kelas_kuliah',
        'id_prodi',
        'id_semester',
        'id_matkul',
        'id_kurikulum',
        'nama_kelas_kuliah',
        'sks_mk',
        'sks_tm',
        'sks_prak',
        'sks_prak_lap',
        'sks_sim',
        'bahasan',
        'kapasitas',
        'tanggal_mulai_efektif',
        'tanggal_akhir_efektif',
        'mode',
        'lingkup',
        'apa_untuk_pditt',
        'a_selenggara_pditt',
        'id_mou',
        // Monitoring
        'sumber_data',
        'status_sinkronisasi',
        'is_deleted_server',
        'last_synced_at',
        'last_push_at',
        'sync_error_message',
        'sync_action',
        'is_local_change',
        'is_deleted_local',
        'is_locked',
        'locked_at',
    ];

    protected $casts = [
        'sks_mk' => 'decimal:2',
        'sks_tm' => 'decimal:2',
        'sks_prak' => 'decimal:2',
        'sks_prak_lap' => 'decimal:2',
        'sks_sim' => 'decimal:2',
        'kapasitas' => 'integer',
        'apa_untuk_pditt' => 'integer',
        'a_selenggara_pditt' => 'integer',
        'is_deleted_server' => 'boolean',
        'is_local_change' => 'boolean',
        'is_deleted_local' => 'boolean',
        'last_synced_at' => 'datetime',
        'last_push_at' => 'datetime',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'tanggal_mulai_efektif' => 'date',
        'tanggal_akhir_efektif' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope('active', function ($builder) {
            $builder->where('is_deleted_local', false)
                ->where('is_deleted_server', false);
        });
    }

    // ─── Scopes ─────────────────────────────────────────────

    /**
     * Scope: Hanya kelas yang tidak dihapus di server.
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted_server', false);
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
     * Scope: Data yang sudah sinkron dengan server.
     */
    public function scopeSynced($query)
    {
        return $query->where('status_sinkronisasi', self::STATUS_SYNCED);
    }

    // ─── Relationships ──────────────────────────────────────

    /**
     * Relasi ke Mata Kuliah.
     */
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'id_matkul', 'id_matkul');
    }

    /**
     * Relasi ke Kurikulum (opsional / lokal).
     */
    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class, 'id_kurikulum', 'id_kurikulum');
    }

    /**
     * Relasi ke Program Studi.
     */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'id_prodi', 'id_prodi');
    }

    /**
     * Relasi ke Semester.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    /**
     * Relasi ke Dosen Pengajar via tabel dosen_pengajar_kelas_kuliah.
     */
    public function dosenPengajar(): HasMany
    {
        return $this->hasMany(DosenPengajarKelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    /**
     * Alias method untuk Dosen Pengajar.
     */
    public function dosenPengajars(): HasMany
    {
        return $this->dosenPengajar();
    }

    /**
     * Relasi ke Peserta Kelas Kuliah (KRS).
     */
    public function pesertaKelasKuliah(): HasMany
    {
        return $this->hasMany(PesertaKelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    /**
     * Relasi ke Penjadwalan Waktu dan Ruang.
     */
    public function jadwalKuliahs(): HasMany
    {
        // Parameter kedua disesuaikan agar nyambung meskipun ID lokal KelasKuliah adalah 'id' auto-increment
        // Foreign id default di jadwal_kuliahs adalah kelas_kuliah_id.
        return $this->hasMany(JadwalKuliah::class, 'kelas_kuliah_id');
    }

    /**
     * Relasi ke Presensi Pertemuan (Jurnal).
     */
    public function presensiPertemuans(): HasMany
    {
        return $this->hasMany(PresensiPertemuan::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }
}
