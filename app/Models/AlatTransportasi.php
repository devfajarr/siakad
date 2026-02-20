<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlatTransportasi extends Model
{
    protected $table = 'alat_transportasi';
    protected $primaryKey = 'id_alat_transportasi';
    public $incrementing = false;
    protected $keyType = 'string'; // ID bisa string/integer tergantung feeder, amannya string

    protected $fillable = [
        'id_alat_transportasi',
        'nama_alat_transportasi',
    ];
}
