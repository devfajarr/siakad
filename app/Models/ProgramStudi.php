<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramStudi extends Model
{
    protected $table = 'program_studis';
    protected $primaryKey = 'id_prodi';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_prodi',
        'kode_program_studi',
        'nama_program_studi',
        'status',
        'id_jenjang_pendidikan',
        'nama_jenjang_pendidikan',
        'id_perguruan_tinggi',
    ];

    /**
     * Relasi ke jabatan Kaprodi Aktif.
     */
    public function kaprodi(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Kaprodi::class, 'id_prodi', 'id_prodi');
    }
}
