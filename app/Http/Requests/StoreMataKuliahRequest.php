<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMataKuliahRequest extends FormRequest
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
        return [
            'kode_mk' => 'required|string|max:20|unique:mata_kuliahs,kode_mk',
            'nama_mk' => 'required|string|max:255',
            'id_prodi' => 'required|exists:program_studis,id_prodi',
            'jenis_mk' => 'required|in:A,B,C,D,S', // A=Wajib, B=Pilihan, etc.
            'kelompok_mk' => 'required|in:A,B,C,D,E,F,G,H', // A=MPK, B=MKK, etc.
            'sks' => 'required|numeric|min:0',
            'sks_tatap_muka' => 'nullable|numeric|min:0',
            'sks_praktek' => 'nullable|numeric|min:0',
            'sks_praktek_lapangan' => 'nullable|numeric|min:0',
            'sks_simulasi' => 'nullable|numeric|min:0',
            'metode_kuliah' => 'nullable|string|max:50',
            'tanggal_mulai_efektif' => 'nullable|date',
            'tanggal_akhir_efektif' => 'nullable|date|after_or_equal:tanggal_mulai_efektif',
        ];
    }

    public function attributes()
    {
        return [
            'kode_mk' => 'Kode Mata Kuliah',
            'nama_mk' => 'Nama Mata Kuliah',
            'id_prodi' => 'Program Studi',
            'jenis_mk' => 'Jenis Mata Kuliah',
            'kelompok_mk' => 'Kelompok Mata Kuliah',
            'sks' => 'SKS Total',
            'sks_tatap_muka' => 'SKS Tatap Muka',
            'sks_praktek' => 'SKS Praktikum',
            'sks_praktek_lapangan' => 'SKS Praktek Lapangan',
            'sks_simulasi' => 'SKS Simulasi',
            'metode_kuliah' => 'Metode Pembelajaran',
        ];
    }
}
