<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenRiwayatPangkat extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_dosen',
        'external_id',
        'pangkat_golongan',
        'sk_nomor',
        'sk_tanggal',
        'tmt_pangkat',
    ];

    protected $casts = [
        'sk_tanggal' => 'date',
        'tmt_pangkat' => 'date',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }
}
