<?php

namespace App\Http\Requests\KelasKuliah;

use Illuminate\Foundation\Http\FormRequest;

class StoreKelasKuliahRequest extends FormRequest
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
            'id_prodi' => 'required|exists:program_studis,id_prodi',
            'id_semester' => 'required|exists:semesters,id_semester',
            'id_matkul' => 'required|exists:mata_kuliahs,id_matkul',
            // Feeder InsertKelasKuliah: character varying(5)
            'nama_kelas_kuliah' => 'required|string|max:5|unique:kelas_kuliah,nama_kelas_kuliah',
            // Feeder InsertKelasKuliah: character varying(200)
            'bahasan' => 'nullable|string|max:200',
            'tanggal_mulai_efektif' => 'nullable|date',
            'tanggal_akhir_efektif' => 'nullable|date|after_or_equal:tanggal_mulai_efektif',
            // Feeder InsertKelasKuliah: numeric(1,0) (0 / 1)
            'apa_untuk_pditt' => 'nullable|in:0,1',
            // Feeder InsertKelasKuliah: numeric(5,0)
            'kapasitas' => 'nullable|integer|min:0|max:99999',
            // Feeder InsertKelasKuliah: numeric(1,0) 1: Internal, 2: External, 3: Campuran
            'lingkup' => 'nullable|in:1,2,3',
            // Feeder InsertKelasKuliah: character(1) O: Online, F: Offline, M: Campuran
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
