<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisDaftar extends Model
{
    protected $table = 'jenis_daftars';
    protected $primaryKey = 'id_jenis_daftar';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_jenis_daftar',
        'nama_jenis_daftar',
    ];
}
