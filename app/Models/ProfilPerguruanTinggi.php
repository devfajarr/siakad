<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilPerguruanTinggi extends Model
{
    protected $table = 'profil_perguruan_tinggis';
    protected $primaryKey = 'id_perguruan_tinggi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_perguruan_tinggi',
        'kode_perguruan_tinggi',
        'nama_perguruan_tinggi',
    ];
}
