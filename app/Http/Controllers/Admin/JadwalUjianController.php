<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJadwalUjianRequest;
use App\Models\JadwalUjian;
use App\Models\KelasKuliah;
use App\Models\PesertaUjian;
use App\Models\Ruang;
use App\Models\Semester;
use App\Services\UjianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JadwalUjianController extends Controller
{
    protected UjianService $ujianService;

    public function __construct(UjianService $ujianService)
    {
        $this->ujianService = $ujianService;
    }

    /**
     * Tampilkan halaman utama manajemen jadwal ujian.
     */
    public function index(Request $request)
    {
        $semesterAktif = Semester::where('a_periode_aktif', '1')->first();
        $idSemester = $request->get('id_semester', $semesterAktif?->id_semester);

        $jadwalUjians = JadwalUjian::with(['kelasKuliah.mataKuliah', 'kelasKuliah.programStudi'])
            ->withCount([
                'pesertaUjians',
                'pesertaUjians as peserta_eligible_count' => function ($q) {
                    $q->where('is_eligible', true);
                }
            ])
            ->where('id_semester', $idSemester)
            ->orderBy('tanggal_ujian')
            ->orderBy('jam_mulai')
            ->get();

        $semesters = Semester::where('a_periode_aktif', '1')->orderBy('id_semester', 'desc')->get();

        // Daftar kelas yang belum punya jadwal UTS/UAS untuk semester ini
        $kelasKuliahs = KelasKuliah::where('id_semester', $idSemester)
            ->with('mataKuliah')
            ->orderBy('nama_kelas_kuliah')
            ->get();

        $ruangs = Ruang::orderBy('nama_ruang')->get();

        // Hitung permintaan cetak yang menunggu
        $permintaanCetakCount = PesertaUjian::where('status_cetak', PesertaUjian::CETAK_DIMINTA)
            ->whereHas('jadwalUjian', function ($q) use ($idSemester) {
                $q->where('id_semester', $idSemester);
            })
            ->count();

        return view('admin.ujian.index', compact(
            'jadwalUjians',
            'semesters',
            'idSemester',
            'kelasKuliahs',
            'ruangs',
            'permintaanCetakCount'
        ));
    }

    /**
     * Simpan jadwal ujian baru.
     */
    public function store(StoreJadwalUjianRequest $request)
    {
        try {
            $jadwal = JadwalUjian::create($request->validated());

            Log::info("CRUD_CREATE: [JadwalUjian] berhasil dibuat", [
                'id' => $jadwal->id,
                'kelas' => $jadwal->kelas_kuliah_id,
                'tipe' => $jadwal->tipe_ujian,
            ]);

            return back()->with('success', "Jadwal {$jadwal->tipe_ujian} berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal membuat jadwal ujian", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Gagal menambahkan jadwal ujian: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update jadwal ujian.
     */
    public function update(StoreJadwalUjianRequest $request, string $id)
    {
        try {
            $jadwal = JadwalUjian::findOrFail($id);
            $jadwal->update($request->validated());

            Log::info("CRUD_UPDATE: [JadwalUjian] diubah", [
                'id' => $jadwal->id,
                'changes' => $jadwal->getChanges(),
            ]);

            return back()->with('success', "Jadwal {$jadwal->tipe_ujian} berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal update jadwal ujian", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Hapus jadwal ujian (cascade ke peserta_ujians).
     */
    public function destroy(string $id)
    {
        try {
            $jadwal = JadwalUjian::findOrFail($id);
            $tipe = $jadwal->tipe_ujian;
            $jadwal->delete();

            Log::warning("CRUD_DELETE: [JadwalUjian] dihapus", ['id' => $id, 'tipe' => $tipe]);

            return back()->with('success', "Jadwal {$tipe} berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal hapus jadwal ujian", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Generate peserta ujian untuk satu jadwal.
     */
    public function generatePeserta(string $id)
    {
        try {
            $jadwal = JadwalUjian::findOrFail($id);
            $result = $this->ujianService->generatePesertaUjian($jadwal);

            $msg = "Berhasil generate {$result['total']} peserta: "
                . "{$result['eligible']} layak, {$result['not_eligible']} tidak layak.";

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal generate peserta ujian", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Gagal generate peserta: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan daftar peserta ujian untuk satu jadwal.
     */
    public function peserta(string $id)
    {
        $jadwal = JadwalUjian::with(['kelasKuliah.mataKuliah'])->findOrFail($id);

        $pesertaUjians = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)
            ->with(['pesertaKelasKuliah.riwayatPendidikan.mahasiswa'])
            ->orderByDesc('is_eligible')
            ->get();

        return view('admin.ujian.peserta', compact('jadwal', 'pesertaUjians'));
    }

    /**
     * Tandai peserta sebagai sudah dicetak kartu ujiannya.
     */
    public function markAsPrinted(Request $request, string $jadwalId, string $pesertaUjianId)
    {
        try {
            $jadwal = JadwalUjian::findOrFail($jadwalId);
            $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)
                ->findOrFail($pesertaUjianId);

            if (!$peserta->can_print) {
                return back()->with('error', 'Mahasiswa tidak layak mengikuti ujian dan belum memiliki dispensasi.');
            }

            $peserta->update([
                'status_cetak' => PesertaUjian::CETAK_DICETAK,
                'dicetak_pada' => now(),
            ]);

            Log::info("UJIAN_CETAK: Kartu ujian dicetak oleh admin", [
                'peserta_ujian_id' => $peserta->id,
                'status_cetak' => PesertaUjian::CETAK_DICETAK,
            ]);

            return back()->with('success', 'Kartu ujian berhasil ditandai sebagai dicetak.');
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal cetak kartu ujian", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Gagal menandai cetak: ' . $e->getMessage());
        }
    }

    /**
     * Tandai peserta mendapat / dicabut dispensasi presensi.
     */
    public function toggleDispensasi(Request $request, string $jadwalId, string $pesertaUjianId)
    {
        try {
            $jadwal = JadwalUjian::findOrFail($jadwalId);
            $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)
                ->findOrFail($pesertaUjianId);

            $peserta->update([
                'is_dispensasi' => !$peserta->is_dispensasi
            ]);

            $statusStr = $peserta->is_dispensasi ? 'Diberikan Dispensasi' : 'Dicabut Dispensasinya';

            Log::info("UJIAN_DISPENSASI: Hak dispensasi ujian $statusStr", [
                'peserta_ujian_id' => $peserta->id,
                'is_dispensasi' => $peserta->is_dispensasi,
            ]);

            return back()->with('success', "Mahasiswa berhasil $statusStr.");
        } catch (\Exception $e) {
            Log::error("SYSTEM_ERROR: Gagal merubah status dispensasi", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Gagal merubah dispensasi: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan print view kartu ujian (untuk dicetak admin).
     */
    public function printKartu(string $pesertaUjianId)
    {
        $peserta = PesertaUjian::with([
            'jadwalUjian.kelasKuliah.mataKuliah',
            'jadwalUjian.semester',
            'pesertaKelasKuliah.riwayatPendidikan.mahasiswa',
            'pesertaKelasKuliah.riwayatPendidikan.programStudi',
        ])->findOrFail($pesertaUjianId);

        if (!$peserta->can_print) {
            return back()->with('error', 'Mahasiswa tidak layak mengikuti ujian dan tidak memiliki dispensasi.');
        }

        // Ambil semua jadwal ujian mahasiswa ini di semester yang sama
        $riwayatId = $peserta->pesertaKelasKuliah->riwayat_pendidikan_id;
        $semesterId = $peserta->jadwalUjian->id_semester;
        $tipeUjian = $peserta->jadwalUjian->tipe_ujian;

        $semuaUjian = PesertaUjian::where(function ($q) {
            $q->where('is_eligible', true)
                ->orWhere('is_dispensasi', true);
        })
            ->whereHas('pesertaKelasKuliah', function ($q) use ($riwayatId) {
                $q->where('riwayat_pendidikan_id', $riwayatId);
            })
            ->whereHas('jadwalUjian', function ($q) use ($semesterId, $tipeUjian) {
                $q->where('id_semester', $semesterId)
                    ->where('tipe_ujian', $tipeUjian);
            })
            ->with(['jadwalUjian.kelasKuliah.mataKuliah'])
            ->get();

        $mahasiswa = $peserta->pesertaKelasKuliah->riwayatPendidikan->mahasiswa;
        $riwayat = $peserta->pesertaKelasKuliah->riwayatPendidikan;

        return view('admin.ujian.print-kartu', compact('peserta', 'semuaUjian', 'mahasiswa', 'riwayat'));
    }

    /**
     * Halaman daftar permintaan cetak kartu ujian.
     */
    public function permintaanCetak(Request $request)
    {
        $semesterAktif = Semester::where('a_periode_aktif', '1')->first();
        $idSemester = $request->get('id_semester', $semesterAktif?->id_semester);

        $permintaan = PesertaUjian::where('status_cetak', PesertaUjian::CETAK_DIMINTA)
            ->whereHas('jadwalUjian', function ($q) use ($idSemester) {
                $q->where('id_semester', $idSemester);
            })
            ->with([
                'jadwalUjian.kelasKuliah.mataKuliah',
                'pesertaKelasKuliah.riwayatPendidikan.mahasiswa',
            ])
            ->orderBy('diminta_pada')
            ->get();

        $semesters = Semester::where('a_periode_aktif', '1')->orderBy('id_semester', 'desc')->get();

        return view('admin.ujian.permintaan-cetak', compact('permintaan', 'semesters', 'idSemester'));
    }
}
