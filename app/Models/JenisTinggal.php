<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisTinggal extends Model
{
    protected $table = 'jenis_tinggal';
    protected $primaryKey = 'id_jenis_tinggal';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_jenis_tinggal',
        'nama_jenis_tinggal',
    ];
}
