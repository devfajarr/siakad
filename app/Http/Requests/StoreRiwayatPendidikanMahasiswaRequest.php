<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRiwayatPendidikanMahasiswaRequest extends FormRequest
{
    /**
     * ID Jenis Pendaftaran "Peserta Didik Baru" pada Feeder.
     */
    public const JENIS_PESERTA_DIDIK_BARU = '1';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_mahasiswa' => ['required', 'exists:mahasiswas,id'],
            'nim' => ['required', 'string', 'max:24'],
            'id_jenis_daftar' => ['required', 'string', 'max:2'],
            'id_jalur_daftar' => ['nullable', 'string', 'max:4'],
            'id_periode_masuk' => ['required', 'string', 'max:5'],
            'tanggal_daftar' => ['required', 'date'],
            'id_perguruan_tinggi' => ['nullable', 'string'],
            'id_prodi' => ['nullable', 'string'],
            'id_bidang_minat' => ['nullable', 'string'],
            'sks_diakui' => ['nullable', 'integer', 'min:0', 'max:999'],
            'id_perguruan_tinggi_asal' => [
                'nullable',
                'required_unless:id_jenis_daftar,' . self::JENIS_PESERTA_DIDIK_BARU,
                'string',
            ],
            'id_prodi_asal' => [
                'nullable',
                'required_unless:id_jenis_daftar,' . self::JENIS_PESERTA_DIDIK_BARU,
                'string',
            ],
            'id_pembiayaan' => ['nullable', 'string'],
            'biaya_masuk' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_mahasiswa.required' => 'ID Mahasiswa wajib diisi.',
            'id_mahasiswa.exists' => 'Mahasiswa tidak ditemukan.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.max' => 'NIM maksimal 24 karakter.',
            'id_jenis_daftar.required' => 'Jenis Pendaftaran wajib dipilih.',
            'id_periode_masuk.required' => 'Periode Pendaftaran wajib dipilih.',
            'tanggal_daftar.required' => 'Tanggal Masuk wajib diisi.',
            'tanggal_daftar.date' => 'Format Tanggal Masuk tidak valid.',
            'biaya_masuk.numeric' => 'Biaya Masuk harus berupa angka.',
            'biaya_masuk.min' => 'Biaya Masuk tidak boleh negatif.',
            'id_perguruan_tinggi_asal.required_unless' => 'Perguruan Tinggi Asal wajib diisi untuk jenis pendaftaran ini.',
            'id_prodi_asal.required_unless' => 'Program Studi Asal wajib diisi untuk jenis pendaftaran ini.',
        ];
    }
}
