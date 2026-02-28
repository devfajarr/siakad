<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJadwalUjianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kelas_kuliah_id' => 'required|exists:kelas_kuliah,id',
            'id_semester' => 'required|exists:semesters,id_semester',
            'ruang_id' => 'required|exists:ruangs,id',
            'tipe_ujian' => 'required|in:UTS,UAS',
            'tanggal_ujian' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'tipe_waktu' => 'required|in:Pagi,Sore,Universal',
            'keterangan' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'kelas_kuliah_id.required' => 'Kelas kuliah wajib dipilih.',
            'kelas_kuliah_id.exists' => 'Kelas kuliah tidak valid.',
            'tipe_ujian.required' => 'Tipe ujian wajib dipilih.',
            'tipe_ujian.in' => 'Tipe ujian harus UTS atau UAS.',
            'tanggal_ujian.required' => 'Tanggal ujian wajib diisi.',
            'jam_mulai.required' => 'Jam mulai ujian wajib diisi.',
            'jam_selesai.required' => 'Jam selesai ujian wajib diisi.',
            'jam_selesai.after' => 'Jam selesai harus lebih dari jam mulai.',
            'tipe_waktu.required' => 'Tipe waktu wajib dipilih.',
            'tipe_waktu.in' => 'Tipe waktu harus Pagi, Sore, atau Universal.',
        ];
    }
}
