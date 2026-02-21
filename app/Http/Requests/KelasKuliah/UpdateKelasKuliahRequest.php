<?php

namespace App\Http\Requests\KelasKuliah;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKelasKuliahRequest extends FormRequest
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
        $kelasKuliahId = $this->route('kelas_kuliah');

        return [
            'id_prodi' => 'required|exists:program_studis,id_prodi',
            'id_semester' => 'required|exists:semesters,id_semester',
            'id_matkul' => 'required|exists:mata_kuliahs,id_matkul',
            'nama_kelas_kuliah' => [
                'required',
                'string',
                'max:5',
                Rule::unique('kelas_kuliah', 'nama_kelas_kuliah')
                    ->where('id_prodi', $this->id_prodi)
                    ->where('id_semester', $this->id_semester)
                    ->where('id_matkul', $this->id_matkul)
                    ->ignore($kelasKuliahId, 'id'),
            ],
            'bahasan' => 'nullable|string|max:1000',
            'tanggal_mulai_efektif' => 'nullable|date',
            'tanggal_akhir_efektif' => 'nullable|date|after_or_equal:tanggal_mulai_efektif',
            'apa_untuk_pditt' => 'nullable|in:0,1',
            'kapasitas' => 'nullable|integer|min:0|max:99999',
            'lingkup' => 'nullable|in:1,2,3',
            'mode' => 'nullable|in:O,F,M',
            'sks_mk' => 'nullable|numeric|min:0',
            'sks_tm' => 'nullable|numeric|min:0',
            'sks_prak' => 'nullable|numeric|min:0',
            'sks_prak_lap' => 'nullable|numeric|min:0',
            'sks_sim' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'id_prodi' => 'Program Studi',
            'id_semester' => 'Semester',
            'id_matkul' => 'Mata Kuliah',
            'nama_kelas_kuliah' => 'Nama Kelas',
            'lingkup' => 'Lingkup',
            'mode' => 'Mode Kuliah',
            'tanggal_mulai_efektif' => 'Tanggal Mulai Efektif',
            'tanggal_akhir_efektif' => 'Tanggal Akhir Efektif',
            'bahasan' => 'Bahasan (Keterangan)',
            'kapasitas' => 'Kapasitas',
            'sks_tm' => 'Bobot Tatap Muka',
            'sks_prak' => 'Bobot Praktikum',
            'sks_prak_lap' => 'Bobot Praktek Lapangan',
            'sks_sim' => 'Bobot Simulasi',
            'sks_mk' => 'Bobot Mata Kuliah',
        ];
    }
}
