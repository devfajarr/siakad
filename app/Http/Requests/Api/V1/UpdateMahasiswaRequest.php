<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMahasiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('mahasiswa')->id ?? $this->route('mahasiswa'); // Handle object or ID

        return [
            // Mandatory Fields
            'nama_mahasiswa' => ['sometimes', 'required', 'string', 'max:100'],
            'jenis_kelamin' => ['sometimes', 'required', Rule::in(['L', 'P'])],
            'tempat_lahir' => ['sometimes', 'required', 'string', 'max:32'],
            'tanggal_lahir' => ['sometimes', 'required', 'date'],
            'id_agama' => ['sometimes', 'required', 'integer'],
            'nik' => ['sometimes', 'required', 'string', 'max:16', Rule::unique('mahasiswas', 'nik')->ignore($id)],
            'nisn' => ['sometimes', 'required', 'string', 'size:10', 'regex:/^[0-9]+$/', Rule::unique('mahasiswas', 'nisn')->ignore($id)],
            'nama_ibu_kandung' => ['sometimes', 'required', 'string', 'max:100'],
            'kewarganegaraan' => ['sometimes', 'required', 'string', 'size:2'],
            'id_wilayah' => ['sometimes', 'required', 'string', 'max:8'],
            'kelurahan' => ['sometimes', 'required', 'string', 'max:60'],
            'penerima_kps' => ['boolean'],
            'handphone' => ['sometimes', 'required', 'string', 'max:20'],
            'email' => ['sometimes', 'required', 'email', 'max:60', Rule::unique('mahasiswas', 'email')->ignore($id)],

            // Optional Fields
            'nomor_kps' => ['nullable', 'string'],
            'npwp' => ['nullable', 'string'],
            'jalan' => ['nullable', 'string'],
            'dusun' => ['nullable', 'string'],
            'rt' => ['nullable', 'string'],
            'rw' => ['nullable', 'string'],
            'kode_pos' => ['nullable', 'string'],
            'telepon' => ['nullable', 'string'],
            'id_alat_transportasi' => ['nullable', 'integer', 'exists:alat_transportasi,id_alat_transportasi'],
            'id_jenis_tinggal' => ['nullable', 'integer', 'exists:jenis_tinggal,id_jenis_tinggal'],

            // Kebutuhan Khusus
            'id_kebutuhan_khusus_mahasiswa' => ['nullable', 'integer'],
            'id_kebutuhan_khusus_ayah' => ['nullable', 'integer'],
            'id_kebutuhan_khusus_ibu' => ['nullable', 'integer'],

            // Orang Tua - Ayah
            'nik_ayah' => ['nullable', 'string', 'max:16'],
            'nama_ayah' => ['nullable', 'string'],
            'tgl_lahir_ayah' => ['nullable', 'date'],
            'id_pendidikan_ayah' => ['nullable', 'integer', 'exists:jenjang_pendidikan,id_jenjang_didik'],
            'id_pekerjaan_ayah' => ['nullable', 'integer', 'exists:pekerjaan,id_pekerjaan'],
            'id_penghasilan_ayah' => ['nullable', 'integer', 'exists:penghasilan,id_penghasilan'],

            // Orang Tua - Ibu
            'nik_ibu' => ['nullable', 'string', 'max:16'],
            'tgl_lahir_ibu' => ['nullable', 'date'],
            'id_pendidikan_ibu' => ['nullable', 'integer', 'exists:jenjang_pendidikan,id_jenjang_didik'],
            'id_pekerjaan_ibu' => ['nullable', 'integer', 'exists:pekerjaan,id_pekerjaan'],
            'id_penghasilan_ibu' => ['nullable', 'integer', 'exists:penghasilan,id_penghasilan'],

            // Wali
            'nama_wali' => ['nullable', 'string'],
            'tgl_lahir_wali' => ['nullable', 'date'],
            'id_pendidikan_wali' => ['nullable', 'integer', 'exists:jenjang_pendidikan,id_jenjang_didik'],
            'id_pekerjaan_wali' => ['nullable', 'integer', 'exists:pekerjaan,id_pekerjaan'],
            'id_penghasilan_wali' => ['nullable', 'integer', 'exists:penghasilan,id_penghasilan'],
        ];
    }
}
