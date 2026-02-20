<?php

namespace App\Http\Requests\KelasDosen;

use App\Models\KelasDosen;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDosenPengajarRequest extends FormRequest
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
            'kelas_kuliah_id' => 'required|exists:kelas_kuliah,id',
            'dosen_id' => 'required|exists:dosens,id',
            'bobot_sks' => 'required|numeric|min:0',
            'jumlah_rencana_pertemuan' => 'required|integer|min:0',
            'jumlah_realisasi_pertemuan' => 'nullable|integer|min:0',
            'jenis_evaluasi' => 'required|in:' . implode(',', KelasDosen::JENIS_EVALUASI),
        ];
    }

    /**
     * Prevent duplicate dosen assignment in one class.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $kelasKuliahId = $this->input('kelas_kuliah_id');
            $dosenId = $this->input('dosen_id');

            if (empty($kelasKuliahId) || empty($dosenId)) {
                return;
            }

            $isDuplicate = KelasDosen::query()
                ->where('kelas_kuliah_id', $kelasKuliahId)
                ->where('dosen_id', $dosenId)
                ->exists();

            if ($isDuplicate) {
                $validator->errors()->add('dosen_id', 'Dosen sudah terdaftar pada kelas kuliah ini.');
            }
        });
    }

    /**
     * Custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'kelas_kuliah_id' => 'Kelas Kuliah',
            'dosen_id' => 'Dosen',
            'bobot_sks' => 'Bobot SKS',
            'jumlah_rencana_pertemuan' => 'Jumlah Rencana Pertemuan',
            'jumlah_realisasi_pertemuan' => 'Jumlah Realisasi Pertemuan',
            'jenis_evaluasi' => 'Jenis Evaluasi',
        ];
    }
}
