<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Dosen;
use App\Models\Pegawai;

class Kemahasiswaan extends Model
{
    use HasFactory;

    protected $fillable = ['id_dosen', 'id_pegawai', 'is_active'];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }
}
