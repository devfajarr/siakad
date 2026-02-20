<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKurikulumRequest extends FormRequest
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
            'nama_kurikulum' => 'required|string|max:60', // Adjusted max length to 60 as per migration
            'id_prodi' => 'required|exists:program_studis,id_prodi',
            'id_semester' => 'required|exists:semesters,id_semester',
            'jumlah_sks_lulus' => 'required|integer|min:0',
            'jumlah_sks_wajib' => 'required|integer|min:0',
            'jumlah_sks_pilihan' => 'required|integer|min:0',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_kurikulum' => 'Nama Kurikulum',
            'id_prodi' => 'Program Studi',
            'id_semester' => 'Mulai Berlaku (Semester)',
            'jumlah_sks_lulus' => 'Total SKS',
            'jumlah_sks_wajib' => 'SKS Wajib',
            'jumlah_sks_pilihan' => 'SKS Pilihan',
        ];
    }
}
