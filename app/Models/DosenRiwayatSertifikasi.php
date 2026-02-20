<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenRiwayatSertifikasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_dosen',
        'external_id',
        'jenis_sertifikasi',
        'nomor_sertifikasi',
        'tahun_sertifikasi',
        'bidang_studi',
    ];

    protected $casts = [
        'tahun_sertifikasi' => 'integer',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }
}
