<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'nama_lengkap',
        'unit_kerja',
        'jabatan',
        'no_hp',
        'email',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mutator untuk membersihkan dan menyiapkan format No HP lokal (62...)
     */
    public function setNoHpAttribute($value)
    {
        $value = preg_replace('/[^0-9]/', '', $value); // Hapus karakter non-numerik

        // Ganti leading 0 dengan 62 untuk standar WA
        if (str_starts_with($value, '0')) {
            $value = '62' . substr($value, 1);
        }

        $this->attributes['no_hp'] = $value;
    }
}
