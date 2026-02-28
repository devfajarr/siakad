<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanUjian extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_ujians';

    protected $fillable = [
        'semester_id',
        'tipe_ujian',
        'tgl_mulai_cetak',
        'tgl_akhir_cetak',
    ];

    protected $casts = [
        'tgl_mulai_cetak' => 'datetime',
        'tgl_akhir_cetak' => 'datetime',
    ];

    /**
     * Relasi ke Semester
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'id_semester');
    }
}
