<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenRiwayatPendidikan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_dosen',
        'external_id',
        'jenjang_pendidikan',
        'gelar_akademik',
        'perguruan_tinggi',
        'program_studi',
        'tahun_lulus',
        'sk_penyetaraan',
        'tanggal_ijazah',
        'nomor_ijazah',
    ];

    protected $casts = [
        'tahun_lulus' => 'integer',
        'tanggal_ijazah' => 'date',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }
}
