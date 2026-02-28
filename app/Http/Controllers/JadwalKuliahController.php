<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalKuliah;
use App\Services\JadwalKuliahService;

class JadwalKuliahController extends Controller
{
    protected $jadwalService;

    public function __construct(JadwalKuliahService $jadwalService)
    {
        $this->jadwalService = $jadwalService;
    }

    /**
     * Store a newly created jadwal in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kelas_kuliah_id' => 'required|exists:kelas_kuliah,id',
            'ruang_id' => 'required|exists:ruangs,id',
            'hari' => 'required|integer|min:1|max:7',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jenis_pertemuan' => 'nullable|string|max:255',
            'tipe_waktu' => 'required|in:Pagi,Sore,Universal',
        ]);

        try {
            $this->jadwalService->checkBentrok(
                $request->ruang_id,
                $request->hari,
                $request->jam_mulai,
                $request->jam_selesai,
                $request->kelas_kuliah_id
            );

            JadwalKuliah::create($request->all());

            return back()->with('success', 'Jadwal kuliah berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan jadwal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified jadwal in storage.
     */
    public function update(Request $request, string $id)
    {
        $jadwal = JadwalKuliah::findOrFail($id);

        $request->validate([
            'kelas_kuliah_id' => 'required|exists:kelas_kuliah,id',
            'ruang_id' => 'required|exists:ruangs,id',
            'hari' => 'required|integer|min:1|max:7',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jenis_pertemuan' => 'nullable|string|max:255',
            'tipe_waktu' => 'required|in:Pagi,Sore,Universal',
        ]);

        try {
            $this->jadwalService->checkBentrok(
                $request->ruang_id,
                $request->hari,
                $request->jam_mulai,
                $request->jam_selesai,
                $request->kelas_kuliah_id,
                $jadwal->id
            );

            $jadwal->update($request->all());

            return back()->with('success', 'Jadwal kuliah berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified jadwal from storage.
     */
    public function destroy(string $id)
    {
        try {
            $jadwal = JadwalKuliah::findOrFail($id);
            $jadwal->delete();
            return back()->with('success', 'Jadwal kuliah berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }
}
