<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenRiwayatFungsional extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_dosen',
        'external_id',
        'jabatan_fungsional',
        'sk_nomor',
        'sk_tanggal',
        'tmt_jabatan',
    ];

    protected $casts = [
        'sk_tanggal' => 'date',
        'tmt_jabatan' => 'date',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }
}
