<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KuisionerJawabanDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_submission',
        'id_pertanyaan',
        'jawaban_skala',
        'jawaban_teks',
    ];

    public function submission()
    {
        return $this->belongsTo(KuisionerSubmission::class, 'id_submission');
    }

    public function pertanyaan()
    {
        return $this->belongsTo(KuisionerPertanyaan::class, 'id_pertanyaan');
    }
}
