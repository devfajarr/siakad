<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDosenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'nidn' => 'nullable|string|unique:dosens,nidn',
            'nip' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
            'id_agama' => 'required|exists:agama,id_agama',
            'is_active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required' => 'Nama dosen wajib diisi.',
            'nidn.unique' => 'NIDN sudah terdaftar.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'id_agama.required' => 'Agama wajib dipilih.',
            'id_agama.exists' => 'Agama tidak valid.',
        ];
    }
}
