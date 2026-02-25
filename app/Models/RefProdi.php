<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefProdi extends Model
{
    protected $table = 'ref_prodis';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'kode_program_studi',
        'nama_program_studi',
        'status',
        'id_jenjang_pendidikan',
        'nama_jenjang_pendidikan',
        'id_perguruan_tinggi',
    ];

    /**
     * Scope: filter by specific Perguruan Tinggi.
     */
    public function scopeByPerguruanTinggi(Builder $query, string $ptId): Builder
    {
        return $query->where('id_perguruan_tinggi', $ptId);
    }

    /**
     * Relationship to PT.
     */
    public function perguruanTinggi(): BelongsTo
    {
        return $this->belongsTo(RefPerguruanTinggi::class, 'id_perguruan_tinggi');
    }

    /**
     * Relationship to Kaprodi record.
     */
    public function kaprodi(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Kaprodi::class, 'id_prodi');
    }
}
