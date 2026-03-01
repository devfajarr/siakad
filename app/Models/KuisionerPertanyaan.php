<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KuisionerPertanyaan extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_kuisioner',
        'teks_pertanyaan',
        'tipe_input',
        'opsi_jawaban',
        'urutan',
    ];

    protected $casts = [
        'opsi_jawaban' => 'array',
    ];

    public function kuisioner()
    {
        return $this->belongsTo(Kuisioner::class, 'id_kuisioner');
    }
}
