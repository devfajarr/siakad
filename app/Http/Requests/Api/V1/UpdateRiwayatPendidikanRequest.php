<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRiwayatPendidikanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_mahasiswa' => ['sometimes', 'exists:mahasiswas,id'],
            // Unique check ignoring current record
            'nim' => [
                'sometimes',
                'string',
                'max:24',
                Rule::unique('riwayat_pendidikans', 'nim')->ignore($this->riwayat_pendidikan)
            ],
            'id_jenis_daftar' => ['sometimes', 'exists:jenis_daftars,id_jenis_daftar'],
            'id_jalur_daftar' => ['nullable', 'exists:jalur_pendaftarans,id_jalur_daftar'],
            'id_periode_masuk' => ['sometimes', 'exists:semesters,id_semester'],
            'tanggal_daftar' => ['sometimes', 'date'],
            'id_perguruan_tinggi_asal' => ['nullable', 'uuid'],
            'id_prodi_asal' => ['nullable', 'uuid'],
            'id_pembiayaan' => ['nullable', 'uuid'],
            'biaya_masuk' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
