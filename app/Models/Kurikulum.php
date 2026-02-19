<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Constants for Sync Status
    const STATUS_SYNCED = 'synced';
    const STATUS_CREATED_LOCAL = 'created_local';
    const STATUS_UPDATED_LOCAL = 'updated_local';
    const STATUS_DELETED_LOCAL = 'deleted_local';
    const STATUS_PENDING_PUSH = 'pending_push';
    const STATUS_PUSH_SUCCESS = 'push_success';
    const STATUS_PUSH_FAILED = 'push_failed';

    protected $casts = [
        'is_deleted_server' => 'boolean',
        'last_synced_at' => 'datetime',
        'last_push_at' => 'datetime',
        'jumlah_sks_lulus' => 'integer',
        'jumlah_sks_wajib' => 'integer',
        'jumlah_sks_pilihan' => 'integer',
    ];

    /**
     * Relationship to Program Studi
     */
    public function prodi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_prodi', 'id_prodi');
    }

    // Optional: Relationship to Semester if model exists

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    /**
     * Relationship to Mata Kuliah (Many-to-Many via pivot)
     */
    public function matakuliah()
    {
        return $this->belongsToMany(MataKuliah::class, 'matkul_kurikulums', 'id_kurikulum', 'id_matkul', 'id_kurikulum', 'id_matkul')
            ->withPivot(['semester', 'sks_mata_kuliah', 'sks_tatap_muka', 'sks_praktek', 'sks_praktek_lapangan', 'sks_simulasi', 'apakah_wajib', 'status_sinkronisasi', 'sumber_data']);
    }

    /**
     * Scope for active records (not deleted on server)
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted_server', false);
    }

    /**
     * Scope for records needing sync
     */
    public function scopeUnsynced($query)
    {
        return $query->where('status_sinkronisasi', '!=', self::STATUS_SYNCED);
    }
}
