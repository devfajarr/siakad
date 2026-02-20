<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Mahasiswa;
use Modules\Akademiks\app\Models\Dosen;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'name' => $this->name,
            'roles' => $this->getRoleNames(),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'profile_type' => $this->profileable_type,
            'profile' => $this->whenLoaded('profileable', function () {
                if ($this->profileable_type === Mahasiswa::class) {
                    return [
                        'nim' => $this->username, // Assuming username is NIM
                        'nama_mahasiswa' => $this->profileable->nama_mahasiswa,
                        'prodi' => $this->profileable->prodi?->nama_prodi ?? null, // Assuming relation exists or field
                        'angkatan' => $this->profileable->angkatan ?? null,
                    ];
                }

                if ($this->profileable_type === Dosen::class) {
                    return [
                        'nidn' => $this->profileable->nidn,
                        'nama_dosen' => $this->profileable->nama_dosen,
                        'jabatan_fungsional' => $this->profileable->jabatan_fungsional ?? null,
                        'prodi_homebase' => $this->profileable->prodi?->nama_prodi ?? null,
                    ];
                }

                return $this->profileable; // Fallback for other types
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $data;
    }
}
