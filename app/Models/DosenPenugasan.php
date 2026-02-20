<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosenPenugasan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_dosen',
        'external_id',
        'id_tahun_ajaran',
        'id_prodi',
        'jenis_penugasan',
        'unit_penugasan',
        'tanggal_mulai',
        'tanggal_selesai',
        'sumber_data',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }
}
