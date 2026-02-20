<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class RefPerguruanTinggi extends Model
{
    protected $table = 'ref_perguruan_tinggis';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'kode_perguruan_tinggi',
        'nama_perguruan_tinggi',
    ];

    /**
     * Scope: exclude the local PT.
     */
    public function scopeExcludeLocal(Builder $query, string $localPtId): Builder
    {
        return $query->where('id', '!=', $localPtId);
    }
}
