<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalUjian extends Model
{
    protected $table = 'jadwal_ujians';

    // ─── Constants ──────────────────────────────────────────
    const TIPE_UTS = 'UTS';
    const TIPE_UAS = 'UAS';

    const TIPE_WAKTU_PAGI = 'Pagi';
    const TIPE_WAKTU_SORE = 'Sore';
    const TIPE_WAKTU_UNIVERSAL = 'Universal';

    protected $fillable = [
        'kelas_kuliah_id',
        'id_semester',
        'ruang_id',
        'tipe_ujian',
        'tanggal_ujian',
        'jam_mulai',
        'jam_selesai',
        'tipe_waktu',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_ujian' => 'date',
        'kelas_kuliah_id' => 'integer',
    ];

    // ─── Relationships ──────────────────────────────────────

    /**
     * Relasi ke Kelas Kuliah.
     */
    public function kelasKuliah(): BelongsTo
    {
        return $this->belongsTo(KelasKuliah::class, 'kelas_kuliah_id');
    }

    /**
     * Relasi ke Semester.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    /**
     * Relasi ke Peserta Ujian.
     */
    public function pesertaUjians(): HasMany
    {
        return $this->hasMany(PesertaUjian::class, 'jadwal_ujian_id');
    }

    /**
     * Relasi ke Ruang.
     */
    public function ruang(): BelongsTo
    {
        return $this->belongsTo(Ruang::class, 'ruang_id');
    }
}
