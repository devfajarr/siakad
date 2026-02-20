<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'semesters';
    protected $primaryKey = 'id_semester';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_semester',
        'nama_semester',
        'id_tahun_ajaran',
        'semester',
        'a_periode_aktif', // 1 = Aktif, 0 = Tidak
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];
}
