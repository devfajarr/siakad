<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Mahasiswa;
use App\Models\KelasKuliah;
use App\Models\Dosen;
use App\Models\KuisionerJawabanDetail;

class KuisionerSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_kuisioner',
        'id_mahasiswa',
        'id_kelas_kuliah',
        'id_dosen',
        'status_sinkronisasi',
    ];

    public function kuisioner()
    {
        return $this->belongsTo(Kuisioner::class, 'id_kuisioner');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa', 'id_mahasiswa');
    }

    public function kelas()
    {
        return $this->belongsTo(KelasKuliah::class, 'id_kelas_kuliah', 'id_kelas_kuliah');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen', 'id');
    }

    public function jawabanDetails()
    {
        return $this->hasMany(KuisionerJawabanDetail::class, 'id_submission');
    }
}
