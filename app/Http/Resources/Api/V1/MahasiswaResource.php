<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MahasiswaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'id_feeder' => $this->id_feeder,
            'nama_mahasiswa' => $this->nama_mahasiswa,
            'jenis_kelamin' => $this->jenis_kelamin,
            'nisn' => $this->nisn,
            'nik' => $this->nik,
            'email' => $this->email,
            'handphone' => $this->handphone,
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => $this->tanggal_lahir, // cast to date
            'agama' => $this->id_agama,
            'alamat' => [
                'jalan' => $this->jalan,
                'rt' => $this->rt,
                'rw' => $this->rw,
                'kelurahan' => $this->kelurahan,
                'kode_pos' => $this->kode_pos,
                'id_wilayah' => $this->id_wilayah,
            ],
            'orang_tua' => [
                'ayah' => [
                    'nama' => $this->nama_ayah,
                    'nik' => $this->nik_ayah,
                ],
                'ibu' => [
                    'nama' => $this->nama_ibu_kandung,
                    'nik' => $this->nik_ibu,
                ]
            ],
            'sync_info' => [
                'is_synced' => $this->is_synced,
                'last_sync' => $this->last_sync,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
