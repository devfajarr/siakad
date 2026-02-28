<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanUjian;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PengaturanUjianController extends Controller
{
    /**
     * Tampilkan halaman pengaturan jadwal cetak kartu ujian.
     */
    public function index(Request $request)
    {
        $semesterId = $request->input('semester_id', getActiveSemesterId());

        // Ambil data pengaturan untuk semester tersebut
        $pengaturanUTS = PengaturanUjian::firstOrCreate(
            ['semester_id' => $semesterId, 'tipe_ujian' => 'UTS']
        );
        $pengaturanUAS = PengaturanUjian::firstOrCreate(
            ['semester_id' => $semesterId, 'tipe_ujian' => 'UAS']
        );

        $semesters = Semester::orderBy('id_semester', 'desc')->limit(50)->get();

        return view('admin.pengaturan-ujian.index', compact(
            'semesterId',
            'semesters',
            'pengaturanUTS',
            'pengaturanUAS'
        ));
    }

    /**
     * Simpan pembaruan waktu cetak kartu ujian.
     */
    public function store(Request $request)
    {
        $request->validate([
            'semester_id' => 'required',
            'tipe_ujian' => 'required|in:UTS,UAS',
            'tgl_mulai_cetak' => 'nullable|date',
            'tgl_akhir_cetak' => 'nullable|date|after_or_equal:tgl_mulai_cetak',
        ]);

        try {
            $pengaturan = PengaturanUjian::updateOrCreate(
                [
                    'semester_id' => $request->semester_id,
                    'tipe_ujian' => $request->tipe_ujian,
                ],
                [
                    'tgl_mulai_cetak' => $request->tgl_mulai_cetak,
                    'tgl_akhir_cetak' => $request->tgl_akhir_cetak,
                ]
            );

            Log::info("PENGATURAN_UJIAN: Waktu cetak kartu {$request->tipe_ujian} diperbarui", [
                'semester_id' => $request->semester_id,
                'tgl_mulai' => $request->tgl_mulai_cetak,
                'tgl_akhir' => $request->tgl_akhir_cetak,
            ]);

            return back()->with('success', "Pengaturan waktu cetak kartu {$request->tipe_ujian} berhasil disimpan.");
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal menyimpan pengaturan waktu cetak kartu", [
                'message' => $e->getMessage(),
            ]);
            return back()->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }
}
