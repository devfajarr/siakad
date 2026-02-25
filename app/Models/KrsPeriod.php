<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KrsPeriod extends Model
{
    use HasFactory;

    protected $table = 'krs_periods';

    protected $fillable = [
        'id_semester',
        'nama_periode',
        'tgl_mulai',
        'tgl_selesai',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tgl_mulai' => 'datetime',
        'tgl_selesai' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Cek apakah periode saat ini sedang berjalan (Admin Active + Tanggal Valid).
     */
    public function getIsOpenAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return now()->between($this->tgl_mulai, $this->tgl_selesai);
    }

    /**
     * Scope untuk mencari periode yang terbuka.
     */
    public function scopeOpen($query)
    {
        return $query->where('is_active', true)
            ->where('tgl_mulai', '<=', now())
            ->where('tgl_selesai', '>=', now());
    }

    /**
     * Relasi ke Semester.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'id_semester', 'id_semester');
    }

    /**
     * Relasi ke User pembuat.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User pengupdate.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
