<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direktur extends Model
{
    use HasFactory;

    protected $fillable = ['id_dosen', 'is_active'];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }
}
