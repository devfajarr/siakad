<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penghasilan extends Model
{
    protected $table = 'penghasilan';
    protected $primaryKey = 'id_penghasilan';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_penghasilan',
        'nama_penghasilan',
    ];
}
