<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JalurPendaftaran extends Model
{
    protected $table = 'jalur_pendaftarans';
    protected $primaryKey = 'id_jalur_daftar';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_jalur_daftar',
        'nama_jalur_daftar',
    ];
}
