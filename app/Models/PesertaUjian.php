<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesertaUjian extends Model
{
    protected $table = 'peserta_ujians';

    // ─── Status Cetak Constants ─────────────────────────────
    const CETAK_BELUM = 'belum';
    const CETAK_DIMINTA = 'diminta';
    const CETAK_DICETAK = 'dicetak';

    protected $fillable = [
        'jadwal_ujian_id',
        'peserta_kelas_kuliah_id',
        'is_eligible',
        'is_dispensasi',
        'keterangan_tidak_layak',
        'persentase_kehadiran',
        'jumlah_hadir',
        'status_cetak',
        'diminta_pada',
        'dicetak_pada',
    ];

    protected $casts = [
        'is_eligible' => 'boolean',
        'persentase_kehadiran' => 'decimal:2',
        'jumlah_hadir' => 'integer',
        'diminta_pada' => 'datetime',
        'dicetak_pada' => 'datetime',
    ];

    // ─── Relationships ──────────────────────────────────────

    /**
     * Relasi ke Jadwal Ujian.
     */
    public function jadwalUjian(): BelongsTo
    {
        return $this->belongsTo(JadwalUjian::class, 'jadwal_ujian_id');
    }

    /**
     * Helper mutator: Menentukan apakah mahasiswa berhak mencetak kartu.
     * Hak cetak diberikan jika eligible SECARA SISTEM atau jika tidak eligible tapi mendapat DISPENSASI ADMIN.
     */
    public function getCanPrintAttribute(): bool
    {
        return $this->is_eligible || $this->is_dispensasi;
    }

    /**
     * Relasi ke Peserta Kelas Kuliah (sumber data KRS).
     */
    public function pesertaKelasKuliah(): BelongsTo
    {
        return $this->belongsTo(PesertaKelasKuliah::class, 'peserta_kelas_kuliah_id');
    }
}
