<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mahasiswa;
use App\Models\RiwayatPendidikan;
use App\Models\KelasKuliah;
use App\Models\JadwalUjian;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use App\Models\Semester;
use App\Models\Ruang;
use App\Models\PengaturanUjian;
use App\Services\UjianService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\PesertaKelasKuliah;
use App\Models\PresensiPertemuan;
use App\Models\PresensiMahasiswa;

class GenerateUjianDummyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ujian:dummy-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate simulasi testing Manajemen Ujian & Dispensasi untuk Administrator';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=== MEMBUAT DATA Skenario TESTING MANAJEMEN UJIAN ===");

        DB::beginTransaction();
        try {
            $semesterAktif = Semester::where('a_periode_aktif', 1)->first() ?? Semester::first();
            if (!$semesterAktif) {
                $this->error("Semester tidak ditemukan.");
                return;
            }

            $prodi = ProgramStudi::first(); // Data real pasti ada
            $mk = MataKuliah::first();
            $ruangan = Ruang::first();

            if (!$prodi || !$mk || !$ruangan) {
                $this->error("Tabel Master (Prodi/MK/Ruangan) belum di-seeding!");
                return;
            }

            // 1. Ambil Kelas Kuliah real yang ada
            $kelas = KelasKuliah::with('pesertaKelasKuliah.riwayatPendidikan.mahasiswa')->first();
            if (!$kelas) {
                $this->error("Tabel Kelas Kuliah Kosong!");
                return;
            }

            // Hapus jadwal dummy sebelumnya
            $validKelasId = $kelas->id;
            JadwalUjian::where('kelas_kuliah_id', $validKelasId)->delete();

            // 2. Buat Pengaturan Ujian Timeframe BUKA
            PengaturanUjian::updateOrCreate(
                ['semester_id' => $semesterAktif->id_semester, 'tipe_ujian' => 'UTS'],
                [
                    'tgl_mulai_cetak' => now()->subDay(),
                    'tgl_akhir_cetak' => now()->addDays(7)
                ]
            );

            // 3. Buat Jadwal Ujian
            $uuidJadwal = \Illuminate\Support\Str::uuid()->toString();
            $jadwal = JadwalUjian::create([
                'id' => $uuidJadwal,
                'kelas_kuliah_id' => $validKelasId,
                'tipe_ujian' => 'UTS',
                'id_semester' => $semesterAktif->id_semester,
                'tanggal_ujian' => now()->addDays(14)->format('Y-m-d'),
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '10:00:00',
                'ruangan_id' => $ruangan->id,
                'tipe_waktu' => 'Universal',
                'metode_ujian' => 'Offline'
            ]);

            // 4. Update Mahasiswa Pertama di Kelas tersebut sebagai "Layak" (Hadir 7x)
            //    Kita ambil 3 peserta 
            $peserta = $kelas->pesertaKelasKuliah()->take(3)->get();
            if ($peserta->count() < 3) {
                $this->error("Kelas ini kurang dari 3 peserta. Simulasi tidak maksimal.");
                return;
            }

            $mhsLayak = $peserta[0];
            $mhsSanksi = $peserta[1];
            $mhsDispensasi = $peserta[2];

            $createPresence = function ($pesertaItem, $kehadiranBanyak) use ($validKelasId) {
                for ($i = 1; $i <= 7; $i++) {
                    $pertemuan = \App\Models\PresensiPertemuan::firstOrCreate(['id_kelas_kuliah' => $validKelasId, 'pertemuan_ke' => $i], [
                        'tanggal' => now()->subDays(30 - $i)->format('Y-m-d'),
                        'jam_mulai' => '08:00:00',
                        'jam_selesai' => '10:00:00',
                        'materi' => "Materi $i",
                        'status_sinkronisasi' => 'created_local'
                    ]);

                    PresensiMahasiswa::updateOrCreate([
                        'presensi_pertemuan_id' => $pertemuan->id,
                        'riwayat_pendidikan_id' => $pesertaItem->riwayat_pendidikan_id
                    ], ['status_kehadiran' => ($i <= $kehadiranBanyak) ? 'Hadir' : 'Alpa']);
                }
            };

            $createPresence($mhsLayak, 7);
            $createPresence($mhsSanksi, 2);
            $createPresence($mhsDispensasi, 2);

            // Generate Peserta Ujian
            $service = app(UjianService::class);
            $service->generatePesertaUjian($jadwal);

            // Berikan atribut is_dispensasi pada Mahasiswa ke-3
            \App\Models\PesertaUjian::where('jadwal_ujian_id', $jadwal->id)
                ->where('peserta_kelas_id', $mhsDispensasi->id_peserta_kelas)
                ->update(['is_dispensasi' => true]);

            DB::commit();

            $this->info("Data berhasil di-generate menggunakan Kelas Kuliah [{$kelas->nama_kelas_kuliah}] !");
            $this->warn("== AKUN MAHASISWA TEST ==");
            $this->line("1. Email: {$mhsLayak->riwayatPendidikan->mahasiswa->user->email} (Layak otomatis, hadir penuh)");
            $this->line("2. Email: {$mhsSanksi->riwayatPendidikan->mahasiswa->user->email} (Ditolak, absen terus)");
            $this->line("3. Email: {$mhsDispensasi->riwayatPendidikan->mahasiswa->user->email} (Ditolak tapi DIBERIKAN DISPENSASI)");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
        }
    }
}
