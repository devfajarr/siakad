<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\KelasKuliah;
use App\Models\PresensiPertemuan;
use App\Services\PresensiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PresensiController extends Controller
{
    protected $presensiService;

    public function __construct(PresensiService $presensiService)
    {
        $this->presensiService = $presensiService;
    }

    /**
     * Daftar pertemuan untuk kelas tertentu.
     */
    public function index($kelasId)
    {
        $dosenId = auth()->user()->dosen->id;

        // Mendukung pencarian via ID (integer) atau id_kelas_kuliah (UUID)
        $kelasKuliah = KelasKuliah::where(function ($q) use ($kelasId) {
            if (is_numeric($kelasId)) {
                $q->where('id', $kelasId);
            } else {
                $q->where('id_kelas_kuliah', $kelasId);
            }
        })
            ->whereHas('dosenPengajars', function ($query) use ($dosenId) {
                $query->where('id_dosen', $dosenId)
                    ->orWhere('id_dosen_alias_lokal', $dosenId);
            })
            ->withCount('pesertaKelasKuliah')
            ->with(['mataKuliah'])
            ->with([
                'presensiPertemuans' => function ($q) {
                    $q->withCount([
                        'presensiMahasiswas as hadir_count' => function ($query) {
                            $query->where('status_kehadiran', 'H');
                        }
                    ])
                        ->orderBy('pertemuan_ke', 'asc');
                }
            ])
            ->withCount([
                'pesertaKelasKuliah as peserta_count' => function ($query) {
                    $query->where('status_krs', 'acc');
                }
            ])
            ->firstOrFail();

        // Jika user mengakses via UUID, redirect ke URL berbasis ID integer (SEO & Konsistensi)
        if (!is_numeric($kelasId)) {
            return redirect()->route('dosen.presensi.index', $kelasKuliah->id);
        }

        return view('dosen.presensi.index', compact('kelasKuliah'));
    }

    /**
     * Form input presensi baru.
     */
    public function create($kelasId)
    {
        $dosenId = auth()->user()->dosen->id;

        $kelasKuliah = KelasKuliah::where(function ($q) use ($kelasId) {
            if (is_numeric($kelasId)) {
                $q->where('id', $kelasId);
            } else {
                $q->where('id_kelas_kuliah', $kelasId);
            }
        })
            ->whereHas('dosenPengajars', function ($query) use ($dosenId) {
                $query->where('id_dosen', $dosenId)
                    ->orWhere('id_dosen_alias_lokal', $dosenId);
            })
            ->with([
                'mataKuliah',
                'jadwalKuliahs.ruang',
                'pesertaKelasKuliah' => function ($query) {
                    $query->where('status_krs', 'acc')->with('riwayatPendidikan.mahasiswa');
                }
            ])
            ->firstOrFail();

        // Redirect ke ID numeric jika perlu
        if (!is_numeric($kelasId)) {
            return redirect()->route('dosen.presensi.create', $kelasKuliah->id);
        }

        // Hitung pertemuan ke berapa
        $pertemuanKe = PresensiPertemuan::where('id_kelas_kuliah', $kelasKuliah->id_kelas_kuliah)->count() + 1;

        if ($pertemuanKe > config('academic.target_pertemuan')) {
            return redirect()->route('dosen.presensi.index', $kelasId)
                ->with('error', 'Batas maksimal ' . config('academic.target_pertemuan') . ' pertemuan telah tercapai.');
        }

        // Ambil jadwal hari ini jika ada
        $now = Carbon::now();
        $hariMap = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
            'Sunday' => 7
        ];
        $hariIni = $hariMap[$now->format('l')];

        $jadwal = $kelasKuliah->jadwalKuliahs->firstWhere('hari', $hariIni);

        $defaultJamMulai = $jadwal ? $jadwal->jam_mulai : '08:00';
        $defaultJamSelesai = $jadwal ? $jadwal->jam_selesai : '10:00';

        return view('dosen.presensi.create', compact('kelasKuliah', 'pertemuanKe', 'defaultJamMulai', 'defaultJamSelesai'));
    }

    /**
     * Simpan data presensi.
     */
    public function store(Request $request, $kelasId)
    {
        $request->validate([
            'pertemuan_ke' => 'required|integer|min:1|max:' . config('academic.target_pertemuan'),
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'materi' => 'required|string',
            'metode_pembelajaran' => 'required|in:Luring,Daring',
            'presensi' => 'required|array',
        ]);

        $kelas = KelasKuliah::findOrFail($kelasId);
        $dosenId = auth()->user()->dosen->id;

        $pertemuanData = [
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'id_dosen' => $dosenId,
            'pertemuan_ke' => $request->pertemuan_ke,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'materi' => $request->materi,
            'metode_pembelajaran' => $request->metode_pembelajaran,
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local'
        ];

        $mahasiswaData = [];
        foreach ($request->presensi as $rpId => $status) {
            $mahasiswaData[] = [
                'riwayat_pendidikan_id' => $rpId,
                'status_kehadiran' => $status,
                'keterangan' => $request->keterangan[$rpId] ?? null
            ];
        }

        try {
            $this->presensiService->simpanPresensi($pertemuanData, $mahasiswaData);
            return redirect()->route('dosen.presensi.index', $kelas->id)
                ->with('success', 'Presensi pertemuan ke-' . $request->pertemuan_ke . ' berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan presensi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Form edit presensi.
     */
    public function edit($id)
    {
        $dosenId = auth()->user()->dosen->id;

        $pertemuan = PresensiPertemuan::with(['kelasKuliah.mataKuliah', 'presensiMahasiswas'])
            ->findOrFail($id);

        $kelasKuliah = $pertemuan->kelasKuliah;

        // Validasi Authorized Dosen (Pengampu atau Tim Teaching)
        $isAuthorized = $kelasKuliah->dosenPengajars()
            ->where(function ($query) use ($dosenId) {
                $query->where('id_dosen', $dosenId)
                    ->orWhere('id_dosen_alias_lokal', $dosenId);
            })->exists();

        if (!$isAuthorized) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah data ini.');
        }

        // Cek Batasan Waktu (Contoh: Maksimal 24 jam setelah tanggal pertemuan)
        // Jika butuh fleksibilitas lebih, bagian ini bisa dikomentari atau disesuaikan
        $pertemuanDateTime = Carbon::parse($pertemuan->tanggal->format('Y-m-d') . ' ' . $pertemuan->jam_mulai);
        if ($pertemuanDateTime->diffInHours(now()) > 24 && !auth()->user()->hasRole('admin')) {
            // return redirect()->route('dosen.presensi.index', $kelasKuliah->id)
            //     ->with('error', 'Batas waktu pengeditan (24 jam) telah berakhir.');
        }

        // Ambil data mahasiswa terdaftar untuk memastikan semua ter-input jika ada yang baru
        $kelasKuliah->load(['pesertaKelasKuliah.riwayatPendidikan.mahasiswa']);

        return view('dosen.presensi.edit', compact('pertemuan', 'kelasKuliah'));
    }

    /**
     * Update data presensi.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'materi' => 'required|string',
            'metode_pembelajaran' => 'required|in:Luring,Daring',
            'presensi' => 'required|array',
        ]);

        $pertemuan = PresensiPertemuan::with('kelasKuliah')->findOrFail($id);
        $kelas = $pertemuan->kelasKuliah;
        $dosenId = auth()->user()->dosen->id;

        // Validasi Kepemilikan (Sama seperti di edit)
        $isAuthorized = $pertemuan->kelasKuliah->dosenPengajars()
            ->where(function ($query) use ($dosenId) {
                $query->where('id_dosen', $dosenId)
                    ->orWhere('id_dosen_alias_lokal', $dosenId);
            })->exists();

        if (!$isAuthorized) {
            abort(403);
        }

        $pertemuanData = [
            'id_kelas_kuliah' => $pertemuan->id_kelas_kuliah,
            'id_dosen' => $pertemuan->id_dosen, // Tetap gunakan ID penginput awal
            'pertemuan_ke' => $pertemuan->pertemuan_ke,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'materi' => $request->materi,
            'metode_pembelajaran' => $request->metode_pembelajaran,
            'status_sinkronisasi' => 'updated_local' // Mark as updated
        ];

        $mahasiswaData = [];
        foreach ($request->presensi as $rpId => $status) {
            $mahasiswaData[] = [
                'riwayat_pendidikan_id' => $rpId,
                'status_kehadiran' => $status,
                'keterangan' => $request->keterangan[$rpId] ?? null
            ];
        }

        try {
            $this->presensiService->simpanPresensi($pertemuanData, $mahasiswaData);

            Log::info("CRUD_UPDATE: [PresensiPertemuan] ID: {$id} berhasil diperbarui oleh Dosen ID: {$dosenId}");

            return redirect()->route('dosen.presensi.index', $kelas->id)
                ->with('success', 'Perubahan presensi pertemuan ke-' . $pertemuan->pertemuan_ke . ' berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui presensi: ' . $e->getMessage())->withInput();
        }
    }
}
