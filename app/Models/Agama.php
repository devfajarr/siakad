<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agama extends Model
{
    protected $table = 'agama';
    protected $primaryKey = 'id_agama';
    public $incrementing = false;
    protected $keyType = 'string'; // Often numeric ID but Feeder sometimes uses strings

    protected $fillable = [
        'id_agama',
        'nama_agama',
    ];
}
