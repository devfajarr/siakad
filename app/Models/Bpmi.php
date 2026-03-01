<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Bpmi extends Model
{
    use HasFactory;

    protected $table = 'bpmis';

    protected $fillable = [
        'id_dosen',
        'sk_tugas',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke model Dosen.
     * 
     * @return BelongsTo
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen', 'id');
    }

    /**
     * Relasi shortcut ke User melalui Dosen.
     * 
     * @return HasOneThrough
     */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Dosen::class,
            'id', // Foreign key on Dosen table
            'id', // Foreign key on User table
            'id_dosen', // Local key on Bpmi table
            'akun_id' // Local key on Dosen table
        );
    }
}
