<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkalaNilaiProdi extends Model
{
    use HasFactory;

    protected $table = 'skala_nilai_prodis';

    protected $fillable = [
        'id_bobot_nilai',
        'id_prodi',
        'nilai_huruf',
        'nilai_indeks',
        'bobot_minimum',
        'bobot_maksimum',
        'tanggal_mulai_efektif',
        'tanggal_akhir_efektif',
        'last_synced_at',
    ];

    protected $casts = [
        'nilai_indeks' => 'decimal:2',
        'bobot_minimum' => 'decimal:2',
        'bobot_maksimum' => 'decimal:2',
        'tanggal_mulai_efektif' => 'date',
        'tanggal_akhir_efektif' => 'date',
        'last_synced_at' => 'datetime',
    ];

    // ─── Scopes ─────────────────────────────────────────────

    /**
     * Scope: Skala nilai yang masih berlaku (efektif).
     */
    public function scopeEfektif($query)
    {
        $today = now()->toDateString();

        return $query->where('tanggal_mulai_efektif', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_akhir_efektif')
                    ->orWhere('tanggal_akhir_efektif', '>=', $today);
            });
    }

    /**
     * Scope: Filter berdasarkan id_prodi.
     */
    public function scopeForProdi($query, string $idProdi)
    {
        return $query->where('id_prodi', $idProdi);
    }

    // ─── Helpers ────────────────────────────────────────────

    /**
     * Cek apakah nilai angka masuk dalam range skala ini.
     */
    public function matchesNilaiAngka(float $nilaiAngka): bool
    {
        return $nilaiAngka >= $this->bobot_minimum && $nilaiAngka <= $this->bobot_maksimum;
    }

    /**
     * Ambil skala nilai yang sesuai untuk angka tertentu.
     */
    public static function resolveNilaiHuruf(string $idProdi, float $nilaiAngka): ?self
    {
        return static::forProdi($idProdi)
            ->efektif()
            ->where('bobot_minimum', '<=', $nilaiAngka)
            ->where('bobot_maksimum', '>=', $nilaiAngka)
            ->first();
    }
}
