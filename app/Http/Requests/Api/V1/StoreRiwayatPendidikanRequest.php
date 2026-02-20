<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRiwayatPendidikanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_mahasiswa' => ['required', 'exists:mahasiswas,id'],
            'nim' => ['required', 'string', 'max:24', 'unique:riwayat_pendidikans,nim'],
            'id_jenis_daftar' => ['required', 'exists:jenis_daftars,id_jenis_daftar'],
            'id_jalur_daftar' => ['nullable', 'exists:jalur_pendaftarans,id_jalur_daftar'],
            'id_periode_masuk' => ['required', 'exists:semesters,id_semester'],
            'tanggal_daftar' => ['required', 'date'],
            'id_perguruan_tinggi_asal' => ['nullable', 'uuid'], // Validation depends on whether we have this table synced or not. For now verify format.
            'id_prodi_asal' => ['nullable', 'uuid'],
            'id_pembiayaan' => ['nullable', 'uuid'], // Same here
            'biaya_masuk' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
