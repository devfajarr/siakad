<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    protected $table = 'pengumumans';

    protected $fillable = [
        'judul',
        'konten',
        'kategori',
        'icon',
        'tgl_mulai',
        'tgl_selesai',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Icon default berdasarkan kategori.
     */
    public const KATEGORI_ICONS = [
        'krs' => 'ri-file-list-3-line',
        'kuisioner' => 'ri-survey-line',
        'ujian' => 'ri-edit-box-line',
        'jadwal' => 'ri-calendar-event-line',
        'umum' => 'ri-information-line',
    ];

    /**
     * Scope: Pengumuman yang sedang aktif (dalam periode tampil).
     */
    public function scopeAktif($query)
    {
        return $query->where('is_active', true)
            ->where('tgl_mulai', '<=', now()->toDateString())
            ->where('tgl_selesai', '>=', now()->toDateString());
    }

    /**
     * Accessor: dapatkan icon, fallback ke default berdasarkan kategori.
     */
    public function getIconDisplayAttribute(): string
    {
        return $this->icon ?? (self::KATEGORI_ICONS[$this->kategori] ?? 'ri-information-line');
    }

    /**
     * Relasi ke User pembuat.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
