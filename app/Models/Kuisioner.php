<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kuisioner extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'deskripsi',
        'id_semester',
        'target_ujian',
        'tipe',
        'status',
    ];

    public function pertanyaans()
    {
        return $this->hasMany(KuisionerPertanyaan::class, 'id_kuisioner')->orderBy('urutan', 'asc');
    }

    public function submissions()
    {
        return $this->hasMany(KuisionerSubmission::class, 'id_kuisioner');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }
}
