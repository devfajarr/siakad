<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembiayaan extends Model
{
    protected $table = 'pembiayaans';
    protected $primaryKey = 'id_pembiayaan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pembiayaan',
        'nama_pembiayaan',
    ];
}
