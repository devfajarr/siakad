<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiwayatPendidikanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_riwayat_pendidikan' => $this->id_riwayat_pendidikan,
            'id_mahasiswa' => $this->id_mahasiswa,
            'nim' => $this->nim,
            'id_jenis_daftar' => $this->id_jenis_daftar,
            'id_jalur_daftar' => $this->id_jalur_daftar,
            'id_periode_masuk' => $this->id_periode_masuk,
            'tanggal_daftar' => $this->tanggal_daftar ? $this->tanggal_daftar->format('Y-m-d') : null,
            'id_perguruan_tinggi_asal' => $this->id_perguruan_tinggi_asal,
            'id_prodi_asal' => $this->id_prodi_asal,
            'biaya_masuk' => $this->biaya_masuk,
            'is_synced' => $this->is_synced,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Relationships can be loaded if needed
            'mahasiswa' => new MahasiswaResource($this->whenLoaded('mahasiswa')),
        ];
    }
}
