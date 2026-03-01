<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Dosen;

class WakilDirektur extends Model
{
    use HasFactory;

    protected $fillable = ['id_dosen', 'tipe_wadir', 'is_active'];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }
}
