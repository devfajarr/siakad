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
                Rule::unique('kelas_kuliah', 'nama_kelas_kuliah')->ignore($kelasKuliahId, 'id'),
            ],
            'bahasan' => 'nullable|string|max:200',
            'tanggal_mulai_efektif' => 'nullable|date',
            'tanggal_akhir_efektif' => 'nullable|date|after_or_equal:tanggal_mulai_efektif',
            'apa_untuk_pditt' => 'nullable|in:0,1',
            'kapasitas' => 'nullable|integer|min:0|max:99999',
            'lingkup' => 'nullable|in:1,2,3',
            'mode' => 'nullable|in:O,F,M',
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
            'bahasan' => 'Keterangan',
        ];
    }
}
