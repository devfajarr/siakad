<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferenceWilayah extends Model
{
    protected $table = 'ref_wilayah';
    protected $primaryKey = 'id_wilayah';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_wilayah',
        'nama_wilayah',
        'id_level_wilayah',
        'id_induk_wilayah',
        'id_negara',
    ];

    protected $casts = [
        'id_level_wilayah' => 'integer',
        'id_induk_wilayah' => 'string',
        'id_negara' => 'string',
    ];

    /**
     * Scope untuk mengambil data Provinsi (Level 1).
     */
    public function scopeProvinsi($query)
    {
        return $query->where('id_level_wilayah', 1);
    }

    /**
     * Scope untuk mengambil data Kabupaten/Kota (Level 2).
     */
    public function scopeKabupaten($query)
    {
        return $query->where('id_level_wilayah', 2);
    }

    /**
     * Scope untuk mengambil data Kecamatan (Level 3).
     */
    public function scopeKecamatan($query)
    {
        return $query->where('id_level_wilayah', 3);
    }

    /**
     * Relasi ke Parent Wilayah.
     * Contoh: Kecamatan -> Kabupaten -> Provinsi -> Negara
     */
    public function parent()
    {
        return $this->belongsTo(ReferenceWilayah::class, 'id_induk_wilayah', 'id_wilayah');
    }

    /**
     * Relasi ke Children Wilayah.
     * Contoh: Provinsi -> Kabupatens
     */
    public function children()
    {
        return $this->hasMany(ReferenceWilayah::class, 'id_induk_wilayah', 'id_wilayah');
    }

    /**
     * Helper Static untuk mendapatkan list Kabupaten berdasarkan ID Provinsi.
     */
    public static function getKabupatenByProvinsi($idProvinsi)
    {
        return self::where('id_induk_wilayah', 'like', trim($idProvinsi) . '%')
            ->where('id_level_wilayah', 2)
            ->orderBy('nama_wilayah')
            ->get();
    }

    /**
     * Helper Static untuk mendapatkan list Kecamatan berdasarkan ID Kabupaten.
     */
    public static function getKecamatanByKabupaten($idKabupaten)
    {
        return self::where('id_induk_wilayah', 'like', trim($idKabupaten) . '%')
            ->where('id_level_wilayah', 3)
            ->orderBy('nama_wilayah')
            ->get();
    }

    /**
     * Find a wilayah by ID with trimming (handles trailing spaces from API).
     */
    public static function findByIdTrimmed(?string $id): ?self
    {
        if (empty($id)) {
            return null;
        }

        return self::whereRaw('TRIM(id_wilayah) = ?', [trim($id)])->first();
    }

    /**
     * Resolve the full hierarchy (Kecamatan -> Kabupaten -> Provinsi) from a kecamatan id_wilayah.
     * Returns an array with keys: kecamatan, kabupaten, provinsi (each is a ReferenceWilayah or null).
     */
    public static function resolveHierarchy(?string $idWilayah): array
    {
        $result = [
            'kecamatan' => null,
            'kabupaten' => null,
            'provinsi' => null,
        ];

        if (empty($idWilayah)) {
            return $result;
        }

        // Step 1: Find Kecamatan
        $kecamatan = self::whereRaw('TRIM(id_wilayah) = ?', [trim($idWilayah)])
            ->where('id_level_wilayah', 3)
            ->first();

        if (!$kecamatan) {
            \Log::warning("Wilayah: Kecamatan not found for id_wilayah: [{$idWilayah}]");
            return $result;
        }
        $result['kecamatan'] = $kecamatan;

        // Step 2: Find Kabupaten (parent of Kecamatan)
        $kabupaten = self::whereRaw('TRIM(id_wilayah) = ?', [trim($kecamatan->id_induk_wilayah)])
            ->where('id_level_wilayah', 2)
            ->first();

        if (!$kabupaten) {
            \Log::warning("Wilayah: Kabupaten not found for id_induk: [{$kecamatan->id_induk_wilayah}]");
            return $result;
        }
        $result['kabupaten'] = $kabupaten;

        // Step 3: Find Provinsi (parent of Kabupaten)
        $provinsi = self::whereRaw('TRIM(id_wilayah) = ?', [trim($kabupaten->id_induk_wilayah)])
            ->where('id_level_wilayah', 1)
            ->first();

        if (!$provinsi) {
            \Log::warning("Wilayah: Provinsi not found for id_induk: [{$kabupaten->id_induk_wilayah}]");
            return $result;
        }
        $result['provinsi'] = $provinsi;

        return $result;
    }
}
