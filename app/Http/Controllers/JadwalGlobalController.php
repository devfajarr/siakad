<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ruang;
use App\Models\JadwalKuliah;
use App\Models\KelasKuliah;
use App\Models\Semester;
use App\Services\JadwalKuliahService;

class JadwalGlobalController extends Controller
{
    /**
     * Handler pengecekan bentrok via Service.
     */
    protected $jadwalService;

    public function __construct(JadwalKuliahService $jadwalService)
    {
        $this->jadwalService = $jadwalService;
    }

    /**
     * Menampilkan Matriks Jadwal Terpadu berdasarkan Filter Hari
     */
    public function index(Request $request)
    {
        // 1. Ambil input hari, default 1 (Senin) jika kosong
        $hariFilter = $request->input('hari', 1);

        // 2. Ambil seluruh data ruangan sebagai basis Matriks Layout
        $ruangs = Ruang::orderBy('nama_ruang')->get();

        // 3. Eager Load jadwal pada hari tersebut
        // Mengambil Jadwal Kuliah khusus hari ini saja beserta Kelas, Dosen, Matkul
        $jadwalHariIni = JadwalKuliah::with([
            'kelasKuliah.mataKuliah',
            'kelasKuliah.dosenPengajars.dosen',
            'kelasKuliah.dosenPengajars.dosenAliasLokal'
        ])
            ->where('hari', $hariFilter)
            // Order by jam mulai supaya berurutan secara logis time-linear
            ->orderBy('jam_mulai')
            ->get();

        // 4. Kelompokkan jadwal yang ditarik tadi berdasarkan ID ruangan
        $jadwalku = $jadwalHariIni->groupBy('ruang_id');

        // Note: Untuk modal tambah kelas global, kita memerlukan dropdown Semester
        // Ambil 50 semester terakhir agar semester aktif saat ini (20252) tidak tergeser oleh data dummy masa depan (2035x)
        $semesters = Semester::orderBy('id_semester', 'desc')->limit(50)->get();

        return view('admin.jadwal-global.index', compact(
            'ruangs',
            'jadwalku',
            'hariFilter',
            'semesters'
        ));
    }

    /**
     * Store new jadwal from Global View.
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
        ]);

        try {
            // Service yang sama untuk mencegah konflik!
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
     * Tampilkan form edit jadwal.
     */
    public function edit($id)
    {
        $jadwal = JadwalKuliah::with(['kelasKuliah.mataKuliah', 'kelasKuliah.dosenPengajars.dosen', 'ruang'])->findOrFail($id);
        $ruangs = Ruang::orderBy('nama_ruang')->get();

        // Ambil daftar dosen pengajar dari kelas ini (Hanya untuk Tampilan)
        $dosenPengajars = $jadwal->kelasKuliah->dosenPengajars;

        return view('admin.jadwal-global.edit', compact('jadwal', 'ruangs', 'dosenPengajars'));
    }

    /**
     * Update data jadwal (Hanya Waktu & Ruang).
     */
    public function update(Request $request, $id)
    {
        $jadwal = JadwalKuliah::findOrFail($id);

        $request->validate([
            'ruang_id' => 'required|exists:ruangs,id',
            'hari' => 'required|integer|min:1|max:7',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jenis_pertemuan' => 'nullable|string|max:255',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $jadwal, $id) {
                // 1. Cek Bentrok (Secara otomatis mengecek seluruh dosen di kelas ini via Service)
                $this->jadwalService->checkBentrok(
                    $request->ruang_id,
                    $request->hari,
                    $request->jam_mulai,
                    $request->jam_selesai,
                    $jadwal->kelas_kuliah_id,
                    $id
                );

                // Catat perubahan untuk logging
                $oldData = $jadwal->only(['ruang_id', 'hari', 'jam_mulai', 'jam_selesai', 'jenis_pertemuan']);

                // 2. Update Data Jadwal
                $jadwal->update([
                    'ruang_id' => $request->ruang_id,
                    'hari' => $request->hari,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'jenis_pertemuan' => $request->jenis_pertemuan,
                ]);

                $newData = $jadwal->only(['ruang_id', 'hari', 'jam_mulai', 'jam_selesai', 'jenis_pertemuan']);
                $changes = array_diff_assoc($newData, $oldData);

                if (!empty($changes)) {
                    \Illuminate\Support\Facades\Log::info("[CRUD_UPDATE] - [JadwalKuliah] ID: {$id} diperbarui oleh Admin", [
                        'changes' => $changes
                    ]);
                }
            });

            return redirect()->route('admin.jadwal-global.index', ['hari' => $request->hari])
                ->with('success', 'Jadwal kuliah berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui jadwal: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * AJAX endpoint untuk mendapatkan daftar Kelas Kuliah berdasarkan Semester
     */
    public function getKelasBySemester(Request $request)
    {
        $semesterId = $request->semester_id;

        $kelas = KelasKuliah::with(['mataKuliah', 'dosenPengajars.dosen', 'dosenPengajars.dosenAliasLokal'])
            ->where('id_semester', $semesterId)
            ->orderBy('nama_kelas_kuliah')
            ->get()
            ->map(function ($k) {
                $dosenNames = [];
                foreach ($k->dosenPengajars as $pengajar) {
                    if ($pengajar->dosenAliasLokal) {
                        $dosenNames[] = $pengajar->dosenAliasLokal->nama;
                    } elseif ($pengajar->dosen) {
                        $dosenNames[] = $pengajar->dosen->nama;
                    }
                }
                $dosenString = !empty($dosenNames) ? implode(', ', $dosenNames) : 'Belum Ada Dosen';

                return [
                    'id' => $k->id,
                    'text' => 'Kls: ' . $k->nama_kelas_kuliah . ' | ' . ($k->mataKuliah->kode_mk ?? '') . ' - ' . ($k->mataKuliah->nama_mk ?? 'Matkul Kosong') . ' (' . $dosenString . ')'
                ];
            });

        return response()->json($kelas);
    }
}
