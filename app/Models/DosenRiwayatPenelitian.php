<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenRiwayatPenelitian extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_dosen',
        'external_id',
        'judul_penelitian',
        'kategori_kegiatan',
        'kelompok_bidang',
        'lembaga_iptek',
        'tahun_kegiatan',
    ];

    protected $casts = [
        'tahun_kegiatan' => 'integer',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }
}
