<?php

namespace App\Http\Requests\KelasDosen;

use App\Models\DosenPengajarKelasKuliah;
use App\Models\KelasKuliah;
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
            'jenis_evaluasi' => 'required|string|in:' . implode(',', array_keys(DosenPengajarKelasKuliah::JENIS_EVALUASI)),
            'id_dosen_alias_lokal' => 'nullable|exists:dosens,id',
            'dosen_alias' => 'nullable|string|max:255',
        ];
    }

    /**
     * Prevent duplicate dosen assignment and enforce SKS limit.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $kelasIdLokal = $this->input('kelas_kuliah_id');
            $dosenId = $this->input('dosen_id');
            $newSks = (float) $this->input('bobot_sks', 0);

            if (empty($kelasIdLokal)) {
                return;
            }

            $kelasKuliah = KelasKuliah::find($kelasIdLokal);
            if (!$kelasKuliah) {
                return;
            }

            // 1. Check Duplicate
            if (!empty($dosenId)) {
                $isDuplicate = DosenPengajarKelasKuliah::query()
                    ->where('id_kelas_kuliah', $kelasKuliah->id_kelas_kuliah)
                    ->where('id_dosen', $dosenId)
                    ->active()
                    ->exists();

                if ($isDuplicate) {
                    $validator->errors()->add('dosen_id', 'Dosen sudah terdaftar pada kelas kuliah ini.');
                }
            }

            // 2. Check SKS Limit
            $maxSks = (float) $kelasKuliah->sks_mk;

            // Total SKS from all records (local & server) for this class
            // Only sum records that are NOT mark as deleted_local
            $totalCurrentSks = (float) DosenPengajarKelasKuliah::query()
                ->where('id_kelas_kuliah', $kelasKuliah->id_kelas_kuliah)
                ->where('is_deleted_local', false)
                ->active()
                ->sum('sks_substansi');

            if (($totalCurrentSks + $newSks) > $maxSks) {
                $remaining = max(0, $maxSks - $totalCurrentSks);
                $validator->errors()->add('bobot_sks', "Total SKS dosen melebihi batas SKS Mata Kuliah (Maks: {$maxSks}, Terisi: {$totalCurrentSks}, Sisa: {$remaining}).");
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
