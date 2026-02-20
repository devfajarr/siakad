<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RefAllProdi extends Model
{
    protected $table = 'ref_all_prodis';
    protected $primaryKey = 'id_prodi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_prodi',
        'kode_program_studi',
        'nama_program_studi',
        'status',
        'id_jenjang_pendidikan',
        'nama_jenjang_pendidikan',
        'id_perguruan_tinggi',
        'kode_perguruan_tinggi',
        'nama_perguruan_tinggi',
    ];

    /**
     * Scope: exclude prodi belonging to the local PT.
     */
    public function scopeExcludeLocalPT(Builder $query, string $localPtId): Builder
    {
        return $query->where('id_perguruan_tinggi', '!=', $localPtId);
    }

    /**
     * Scope: filter by specific Perguruan Tinggi.
     */
    public function scopeByPerguruanTinggi(Builder $query, string $ptId): Builder
    {
        return $query->where('id_perguruan_tinggi', $ptId);
    }

    /**
     * Scope: only active prodi.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'A');
    }
}
