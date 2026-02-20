<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KelasDosen extends Model
{
    use HasFactory;

    protected $table = 'kelas_dosen';

    public const STATUS_PENDING = 'pending';
    public const STATUS_SYNCED = 'synced';
    public const STATUS_UPDATED_LOCAL = 'updated_local';
    public const STATUS_DELETED_LOCAL = 'deleted_local';
    public const STATUS_FAILED = 'failed';

    public const SYNC_ACTION_INSERT = 'insert';
    public const SYNC_ACTION_UPDATE = 'update';
    public const SYNC_ACTION_DELETE = 'delete';

    public const JENIS_EVALUASI = ['1', '2', '3', '4'];

    protected $fillable = [
        'kelas_kuliah_id',
        'dosen_id',
        'bobot_sks',
        'jumlah_rencana_pertemuan',
        'jumlah_realisasi_pertemuan',
        'jenis_evaluasi',
        'feeder_id',
        'status_sinkronisasi',
        'sync_action',
        'is_from_server',
        'is_deleted_server',
        'last_synced_at',
        'last_push_at',
        'error_message',
    ];

    protected $casts = [
        'bobot_sks' => 'decimal:2',
        'jumlah_rencana_pertemuan' => 'integer',
        'jumlah_realisasi_pertemuan' => 'integer',
        'is_from_server' => 'boolean',
        'is_deleted_server' => 'boolean',
        'last_synced_at' => 'datetime',
        'last_push_at' => 'datetime',
    ];

    /**
     * Relasi ke kelas kuliah.
     */
    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    /**
     * Relasi ke dosen.
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }
}
