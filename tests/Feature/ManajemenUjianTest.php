<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Semester;
use App\Models\RiwayatPendidikan;
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use App\Models\JadwalUjian;
use App\Models\PesertaUjian;
use App\Models\PresensiPertemuan;
use App\Models\PresensiMahasiswa;
use App\Services\UjianService;
use Illuminate\Support\Str;

/**
 * Feature Test: Manajemen Ujian Semester (Admin & Mahasiswa)
 *
 * Cakupan:
 * - Admin: CRUD Jadwal Ujian, Generate Peserta, Kelayakan, Cetak Kartu
 * - Mahasiswa: Lihat Kartu Ujian (filter Tipe Kelas), Ajukan Cetak
 * - Service: Logika eligibility berdasarkan kehadiran & status KRS
 */
class ManajemenUjianTest extends TestCase
{
    use DatabaseTransactions;

    // ── Shared test data ────────────────────────────────────
    protected $semester;
    protected $prodi;
    protected $mataKuliah;
    protected $kelasKuliah;
    protected $dosen;
    protected $adminUser;
    protected static $pertemuanOffset = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // Semester Aktif
        $this->semester = Semester::where('a_periode_aktif', '1')->first();
        if (!$this->semester) {
            $this->semester = Semester::create([
                'id_semester' => '20251',
                'nama_semester' => '2025/2026 Ganjil',
                'id_tahun_ajaran' => '2025',
                'a_periode_aktif' => '1',
            ]);
        }

        // Program Studi
        $this->prodi = ProgramStudi::first();
        if (!$this->prodi) {
            $this->prodi = ProgramStudi::create([
                'id_prodi' => (string) Str::uuid(),
                'nama_program_studi' => 'Prodi Test Ujian',
                'kode_program_studi' => 'PTU',
            ]);
        }

        // Dosen (untuk presensi pertemuan)
        $this->dosen = \App\Models\Dosen::first();
        if (!$this->dosen) {
            $dosenUser = User::factory()->create([
                'username' => 'dosen_test_ujian',
                'email' => 'dosen_test_ujian@test.com',
            ]);
            $this->dosen = \App\Models\Dosen::create([
                'user_id' => $dosenUser->id,
                'nama_dosen' => 'Dosen Test Ujian',
                'nidn' => '00' . rand(10000000, 99999999),
                'jenis_kelamin' => 'L',
                'sumber_data' => 'lokal',
                'status_sinkronisasi' => 'created_local',
            ]);
        }

        // Mata Kuliah
        $this->mataKuliah = MataKuliah::create([
            'id_matkul' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'kode_mk' => 'MKU' . rand(100, 999),
            'nama_mk' => 'Mata Kuliah Ujian Test',
            'sks' => 3,
            'status_aktif' => true,
        ]);

        // Kelas Kuliah (unique per test run to avoid pertemuan_ke conflicts)
        $this->kelasKuliah = KelasKuliah::create([
            'id_kelas_kuliah' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'id_semester' => $this->semester->id_semester,
            'id_matkul' => $this->mataKuliah->id_matkul,
            'nama_kelas_kuliah' => 'Kelas Test Ujian ' . uniqid(),
            'sks_mk' => 3,
            'kapasitas' => 40,
        ]);

        // Admin User
        $this->adminUser = User::where('email', 'admin@admin.com')->first();
        if (!$this->adminUser) {
            $this->adminUser = User::factory()->create([
                'username' => 'admin_test_ujian',
                'email' => 'admin_test_ujian@test.com',
            ]);
            $this->adminUser->assignRole('admin');
        }
    }

    // ═════════════════════════════════════════════════════════
    // HELPER METHODS
    // ═════════════════════════════════════════════════════════

    /**
     * Buat user mahasiswa lengkap dengan riwayat pendidikan dan KRS ACC.
     */
    private function buatMahasiswaLengkap(string $tipeKelas, string $statusKrs = 'acc'): array
    {
        $uid = uniqid();
        $user = User::factory()->create([
            'username' => 'mhs_ujian_' . $uid,
            'email' => 'mhs_ujian_' . $uid . '@test.com',
        ]);
        $user->assignRole('Mahasiswa');

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nama_mahasiswa' => 'Mhs Ujian ' . ucfirst($tipeKelas) . ' ' . $uid,
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Makassar',
            'tanggal_lahir' => '2002-05-15',
            'id_agama' => 1,
            'nik' => rand(1000000000000000, 9999999999999999),
            'nisn' => str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
            'nama_ibu_kandung' => 'Ibu Test',
            'id_wilayah' => '000000',
            'kelurahan' => 'Kelurahan Test',
            'handphone' => '0812' . rand(10000000, 99999999),
            'email' => $user->email,
            'tipe_kelas' => $tipeKelas,
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local',
        ]);

        $riwayat = RiwayatPendidikan::create([
            'id_mahasiswa' => $mahasiswa->id,
            'nim' => 'NIM' . rand(10000, 99999),
            'id_jenis_daftar' => '1',
            'id_periode_masuk' => $this->semester->id_semester,
            'tanggal_daftar' => now()->format('Y-m-d'),
            'id_prodi' => $this->prodi->id_prodi,
            'status_sinkronisasi' => 'lokal',
            'sumber_data' => 'lokal',
        ]);

        $pkk = PesertaKelasKuliah::create([
            'id_kelas_kuliah' => $this->kelasKuliah->id_kelas_kuliah,
            'riwayat_pendidikan_id' => $riwayat->id,
            'status_krs' => $statusKrs,
            'sumber_data' => 'lokal',
        ]);

        return [
            'user' => $user,
            'mahasiswa' => $mahasiswa,
            'riwayat' => $riwayat,
            'pkk' => $pkk,
        ];
    }

    /**
     * Buat data presensi (kehadiran) untuk seorang mahasiswa.
     *
     * @param int $riwayatId ID riwayat pendidikan
     * @param int $jumlahHadir Jumlah pertemuan yang status = 'H'
     * @param int $totalPertemuan Total pertemuan yang dibuat
     */
    private function buatDataPresensi(int $riwayatId, int $jumlahHadir, int $totalPertemuan = 14): void
    {
        for ($i = 1; $i <= $totalPertemuan; $i++) {
            $pertemuan = PresensiPertemuan::create([
                'id_kelas_kuliah' => $this->kelasKuliah->id_kelas_kuliah,
                'id_dosen' => $this->dosen->id,
                'pertemuan_ke' => $i,
                'tanggal' => now()->subDays($totalPertemuan - $i)->format('Y-m-d'),
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
                'materi' => 'Materi Pertemuan ' . $i,
                'sumber_data' => 'lokal',
                'status_sinkronisasi' => 'created_local',
            ]);

            PresensiMahasiswa::create([
                'presensi_pertemuan_id' => $pertemuan->id,
                'riwayat_pendidikan_id' => $riwayatId,
                'status_kehadiran' => $i <= $jumlahHadir ? 'H' : 'A',
                'sumber_data' => 'lokal',
                'status_sinkronisasi' => 'created_local',
            ]);
        }
    }

    /**
     * Buat jadwal ujian untuk kelas yang sudah disetup.
     */
    private function buatJadwalUjian(string $tipeUjian = 'UTS', string $tipeWaktu = 'Universal'): JadwalUjian
    {
        // Pastikan ruang ada
        $ruang = \App\Models\Ruang::first() ?? \App\Models\Ruang::create([
            'kode_ruang' => 'R01',
            'nama_ruang' => 'Ruang 01',
            'kapasitas' => 40
        ]);

        return JadwalUjian::create([
            'kelas_kuliah_id' => $this->kelasKuliah->id,
            'id_semester' => $this->semester->id_semester,
            'ruang_id' => $ruang->id,
            'tipe_ujian' => $tipeUjian,
            'tanggal_ujian' => now()->addDays(7)->format('Y-m-d'),
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'tipe_waktu' => $tipeWaktu,
        ]);
    }

    // ═════════════════════════════════════════════════════════
    // A. ADMIN: CRUD JADWAL UJIAN
    // ═════════════════════════════════════════════════════════

    /**
     * Test A1: Admin dapat mengakses halaman index jadwal ujian.
     */
    public function test_admin_dapat_mengakses_halaman_jadwal_ujian(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.ujian.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ujian.index');
        $response->assertViewHas('jadwalUjians');
        $response->assertViewHas('semesters');
        $response->assertViewHas('kelasKuliahs');
    }

    /**
     * Test A2: Admin dapat membuat jadwal ujian baru (store).
     */
    public function test_admin_dapat_membuat_jadwal_ujian_baru(): void
    {
        $ruang = \App\Models\Ruang::first() ?? \App\Models\Ruang::create([
            'kode_ruang' => 'R02',
            'nama_ruang' => 'Ruang 02',
            'kapasitas' => 40
        ]);

        $data = [
            'kelas_kuliah_id' => $this->kelasKuliah->id,
            'id_semester' => $this->semester->id_semester,
            'ruang_id' => $ruang->id,
            'tipe_ujian' => 'UTS',
            'tanggal_ujian' => now()->addDays(14)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'tipe_waktu' => 'Pagi',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.store'), $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_ujians', [
            'kelas_kuliah_id' => $this->kelasKuliah->id,
            'ruang_id' => $ruang->id,
            'tipe_ujian' => 'UTS',
            'tipe_waktu' => 'Pagi',
        ]);
    }

    /**
     * Test A3: Validasi gagal jika data tidak lengkap.
     */
    public function test_validasi_store_jadwal_gagal_jika_tidak_lengkap(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.store'), [
                'kelas_kuliah_id' => '',
                'tipe_ujian' => '',
            ]);

        $response->assertSessionHasErrors(['kelas_kuliah_id', 'tipe_ujian', 'tanggal_ujian', 'jam_mulai', 'jam_selesai', 'tipe_waktu']);
    }

    /**
     * Test A4: Validasi gagal jika tipe_ujian bukan UTS/UAS.
     */
    public function test_validasi_tipe_ujian_harus_uts_atau_uas(): void
    {
        $ruang = \App\Models\Ruang::first();

        $data = [
            'kelas_kuliah_id' => $this->kelasKuliah->id,
            'id_semester' => $this->semester->id_semester,
            'ruang_id' => $ruang ? $ruang->id : 1, // Will fail validation if no ruang
            'tipe_ujian' => 'INVALID',
            'tanggal_ujian' => now()->addDays(14)->format('Y-m-d'),
            'jam_mulai' => '09:00',
            'jam_selesai' => '11:00',
            'tipe_waktu' => 'Universal',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.store'), $data);

        $response->assertSessionHasErrors('tipe_ujian');
    }

    /**
     * Test A5: Admin dapat update jadwal ujian.
     */
    public function test_admin_dapat_mengupdate_jadwal_ujian(): void
    {
        $jadwal = $this->buatJadwalUjian('UTS', 'Universal');
        $tanggalBaru = now()->addDays(21)->format('Y-m-d');
        $ruang = \App\Models\Ruang::first();

        $response = $this->actingAs($this->adminUser)
            ->put(route('admin.ujian.update', $jadwal->id), [
                'kelas_kuliah_id' => $this->kelasKuliah->id,
                'id_semester' => $this->semester->id_semester,
                'ruang_id' => $ruang->id,
                'tipe_ujian' => 'UAS',
                'tanggal_ujian' => $tanggalBaru,
                'jam_mulai' => '13:00',
                'jam_selesai' => '15:00',
                'tipe_waktu' => 'Sore',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_ujians', [
            'id' => $jadwal->id,
            'ruang_id' => $ruang->id,
            'tipe_ujian' => 'UAS',
            'tipe_waktu' => 'Sore',
        ]);
    }

    /**
     * Test A6: Admin dapat menghapus jadwal ujian.
     */
    public function test_admin_dapat_menghapus_jadwal_ujian(): void
    {
        $jadwal = $this->buatJadwalUjian();

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.ujian.destroy', $jadwal->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('jadwal_ujians', ['id' => $jadwal->id]);
    }

    // ═════════════════════════════════════════════════════════
    // B. ADMIN: GENERATE PESERTA & KELAYAKAN
    // ═════════════════════════════════════════════════════════

    /**
     * Test B1: Generate peserta ujian – mahasiswa KRS ACC dengan kehadiran cukup → Layak.
     */
    public function test_generate_peserta_kehadiran_cukup_maka_layak(): void
    {
        $jadwal = $this->buatJadwalUjian('UTS', 'Universal');
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');

        // Buat presensi: UTS mengecek pert 1-7. Jika 6 di awal hadir, maka 6/7 = 85.7% -> Layak
        $this->buatDataPresensi($data['riwayat']->id, 6);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.generate-peserta', $jadwal->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)
            ->where('peserta_kelas_kuliah_id', $data['pkk']->id)
            ->first();

        $this->assertNotNull($peserta, 'Peserta ujian harus ter-generate');
        $this->assertTrue($peserta->is_eligible, 'Mahasiswa UTS dengan 6/7 hadir (85.7%) harus LAYAK');
        $this->assertEquals(6, $peserta->jumlah_hadir);
        $this->assertNull($peserta->keterangan_tidak_layak);
        $this->assertEquals(PesertaUjian::CETAK_BELUM, $peserta->status_cetak);
    }

    /**
     * Test B2: Generate peserta ujian – mahasiswa dengan kehadiran kurang → Tidak Layak.
     */
    public function test_generate_peserta_kehadiran_kurang_maka_tidak_layak(): void
    {
        $jadwal = $this->buatJadwalUjian('UTS', 'Universal');
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');

        // Buat presensi: 5 hadir (1-5). UTS cek pert 1-7. 5/7 = 71.4% -> Tidak Layak
        $this->buatDataPresensi($data['riwayat']->id, 5);

        $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.generate-peserta', $jadwal->id));

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)
            ->where('peserta_kelas_kuliah_id', $data['pkk']->id)
            ->first();

        $this->assertNotNull($peserta);
        $this->assertFalse($peserta->is_eligible, 'Mahasiswa UTS dengan 5/7 hadir (71.4%) harus TIDAK LAYAK');
        $this->assertEquals(5, $peserta->jumlah_hadir);
        $this->assertNotNull($peserta->keterangan_tidak_layak);
    }

    /**
     * Test B3: Mahasiswa dengan KRS belum ACC tidak akan di-generate sebagai peserta ujian.
     */
    public function test_mahasiswa_krs_belum_acc_tidak_digenerate(): void
    {
        $jadwal = $this->buatJadwalUjian();

        // Buat mahasiswa dengan status KRS 'paket' (bukan 'acc')
        $data = $this->buatMahasiswaLengkap('Pagi', 'paket');
        $this->buatDataPresensi($data['riwayat']->id, 14);

        $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.generate-peserta', $jadwal->id));

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)
            ->where('peserta_kelas_kuliah_id', $data['pkk']->id)
            ->first();

        $this->assertNull($peserta, 'Mahasiswa KRS paket tidak boleh menjadi peserta ujian');
    }

    /**
     * Test B4: Generate peserta bersifat idempotent (updateOrCreate, tidak duplikat).
     */
    public function test_generate_peserta_idempotent_tidak_duplikat(): void
    {
        $jadwal = $this->buatJadwalUjian('UTS', 'Universal');
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 6);

        // Generate pertama
        $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.generate-peserta', $jadwal->id));

        $countPertama = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->count();

        // Generate kedua (harus update, bukan duplikat)
        $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.generate-peserta', $jadwal->id));

        $countKedua = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->count();

        $this->assertEquals($countPertama, $countKedua, 'Generate kedua tidak boleh membuat duplikat');
    }

    /**
     * Test B5: Persentase kehadiran dihitung dengan benar.
     */
    public function test_persentase_kehadiran_dihitung_benar(): void
    {
        $jadwal = $this->buatJadwalUjian('UTS', 'Universal');
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        // Hadir pert 1-6 = 6/7 = 85.71%
        $this->buatDataPresensi($data['riwayat']->id, 6, 14);

        $service = app(UjianService::class);
        $result = $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();

        // 6/7 * 100 = 85.71
        $this->assertEquals(85.71, (float) $peserta->persentase_kehadiran);
        $this->assertEquals(6, $peserta->jumlah_hadir);
    }

    /**
     * Test B6: Batas tepat minimum kehadiran (exactly 12 = layak).
     */
    public function test_kehadiran_tepat_batas_minimum_layak(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        // 6/14 means 6/7 for UTS = 85.71%, which is >= 75%
        $this->buatDataPresensi($data['riwayat']->id, 6, 14);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();

        $this->assertTrue($peserta->is_eligible, 'Hadir 6/14 (85.7%) harus LAYAK (min 75%)');
    }

    /**
     * Test B7: Satu kurang dari minimum kehadiran (11 = tidak layak).
     */
    public function test_kehadiran_satu_kurang_dari_minimum_tidak_layak(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        // 5/14 means 5/7 for UTS = 71.42%, which is < 75%
        $this->buatDataPresensi($data['riwayat']->id, 5, 14);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();

        $this->assertFalse($peserta->is_eligible, 'Hadir 5/14 (71.42%) harus TIDAK LAYAK (min 75%)');
    }

    // ═════════════════════════════════════════════════════════
    // C. ADMIN: CETAK KARTU & PERMINTAAN CETAK
    // ═════════════════════════════════════════════════════════

    /**
     * Test C1: Admin dapat menandai peserta sebagai "dicetak".
     */
    public function test_admin_dapat_menandai_peserta_dicetak(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 14);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.mark-printed', [$jadwal->id, $peserta->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $peserta->refresh();
        $this->assertEquals(PesertaUjian::CETAK_DICETAK, $peserta->status_cetak);
        $this->assertNotNull($peserta->dicetak_pada);
    }

    /**
     * Test C2: Admin tidak bisa cetak kartu untuk peserta yang tidak layak/tidak dispensasi.
     */
    public function test_admin_tidak_bisa_cetak_kartu_peserta_tidak_layak_tanpa_dispensasi(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 4);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();
        $this->assertFalse($peserta->is_eligible);
        $this->assertFalse($peserta->can_print);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.mark-printed', [$jadwal->id, $peserta->id]));

        $response->assertSessionHas('error');
    }

    /**
     * Test C3: Admin bisa memberikan dispensasi, dan mahasiswa yang dispensasi bisa dicetak.
     */
    public function test_admin_bisa_memberikan_dispensasi_ke_mahasiswa_tidak_layak(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 4); // Tidak layak

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();
        $this->assertFalse($peserta->is_eligible);

        // Berikan dispensasi
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.toggle-dispensasi', [$jadwal->id, $peserta->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $peserta->refresh();
        $this->assertTrue($peserta->is_dispensasi);
        $this->assertTrue($peserta->can_print); // Walau tidak eligible, can_print harus true
    }

    /**
     * Test C4: Mahasiswa dengan dispensasi dapat mengajukan cetak kartu.
     */
    public function test_mahasiswa_dengan_dispensasi_bisa_mengajukan_cetak_kartu(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 2); // Sangat tidak layak

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();

        // Beri dispensasi
        $peserta->update(['is_dispensasi' => true]);

        // Ajukan cetak
        $response = $this->actingAs($data['user'])
            ->post(route('mahasiswa.ujian.ajukan-cetak', $peserta->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $response->assertSessionHas('success');

        $peserta->refresh();
        $this->assertEquals(PesertaUjian::CETAK_DIMINTA, $peserta->status_cetak);
    }

    /**
     * Test C5: Mahasiswa tidak bisa cetak jika periode cetak belum dibuka.
     */
    public function test_mahasiswa_gagal_cetak_jika_periode_belum_dibuka(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 7); // Layak

        // Buat aturan timeframe: baru buka besok
        \App\Models\PengaturanUjian::where('semester_id', $jadwal->id_semester)->where('tipe_ujian', 'UTS')->delete();
        \App\Models\PengaturanUjian::create([
            'semester_id' => $jadwal->id_semester,
            'tipe_ujian' => 'UTS',
            'tgl_mulai_cetak' => now()->addDay(),
        ]);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);
        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();

        $response = $this->actingAs($data['user'])
            ->post(route('mahasiswa.ujian.ajukan-cetak', $peserta->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('belum dibuka', session('error'));
    }

    /**
     * Test C6: Mahasiswa tidak bisa cetak jika periode cetak sudah ditutup.
     */
    public function test_mahasiswa_gagal_cetak_jika_periode_sudah_berakhir(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 7); // Layak

        // Buat aturan timeframe: sudah lewat kemaren
        \App\Models\PengaturanUjian::where('semester_id', $jadwal->id_semester)->where('tipe_ujian', 'UTS')->delete();
        \App\Models\PengaturanUjian::create([
            'semester_id' => $jadwal->id_semester,
            'tipe_ujian' => 'UTS',
            'tgl_akhir_cetak' => now()->subDay(),
        ]);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);
        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();

        $response = $this->actingAs($data['user'])
            ->post(route('mahasiswa.ujian.ajukan-cetak', $peserta->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('sudah berakhir', session('error'));
    }

    /**
     * Test C5: Admin tidak bisa cetak kartu untuk peserta yang tidak layak.
     */
    public function test_admin_tidak_bisa_cetak_kartu_peserta_tidak_layak(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 4);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();
        $this->assertFalse($peserta->is_eligible);

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.ujian.mark-printed', [$jadwal->id, $peserta->id]));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $peserta->refresh();
        $this->assertEquals(PesertaUjian::CETAK_BELUM, $peserta->status_cetak);
    }

    /**
     * Test C6: Admin dapat mengakses halaman peserta ujian.
     */
    public function test_admin_dapat_melihat_halaman_peserta_ujian(): void
    {
        $jadwal = $this->buatJadwalUjian();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.ujian.peserta', $jadwal->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ujian.peserta');
        $response->assertViewHas('jadwal');
        $response->assertViewHas('pesertaUjians');
    }

    /**
     * Test C4: Admin dapat mengakses halaman permintaan cetak.
     */
    public function test_admin_dapat_melihat_halaman_permintaan_cetak(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.ujian.permintaan-cetak'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ujian.permintaan-cetak');
        $response->assertViewHas('permintaan');
    }

    /**
     * Test C5: Admin dapat mengakses print view kartu ujian.
     */
    public function test_admin_dapat_mengakses_print_view_kartu_ujian(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 14);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.ujian.print-kartu', $peserta->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ujian.print-kartu');
        $response->assertViewHas('peserta');
        $response->assertViewHas('semuaUjian');
        $response->assertViewHas('mahasiswa');
    }

    /**
     * Test C6: Halaman permintaan cetak menampilkan jumlah yang benar.
     */
    public function test_permintaan_cetak_menampilkan_data_diminta(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 14);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();
        $peserta->update([
            'status_cetak' => PesertaUjian::CETAK_DIMINTA,
            'diminta_pada' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.ujian.permintaan-cetak', ['id_semester' => $this->semester->id_semester]));

        $response->assertStatus(200);
        $permintaan = $response->viewData('permintaan');
        $this->assertEquals(1, $permintaan->count());
    }

    // ═════════════════════════════════════════════════════════
    // D. MAHASISWA: KARTU UJIAN & AJUKAN CETAK
    // ═════════════════════════════════════════════════════════

    /**
     * Test D1: Mahasiswa Pagi melihat jadwal ujian tipe_waktu Pagi & Universal.
     */
    public function test_mahasiswa_pagi_melihat_jadwal_pagi_dan_universal(): void
    {
        $jadwalPagi = $this->buatJadwalUjian('UTS', 'Pagi');
        $jadwalUniversal = $this->buatJadwalUjian('UAS', 'Universal');

        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 14);

        // Generate peserta untuk kedua jadwal
        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwalPagi);
        $service->generatePesertaUjian($jadwalUniversal);

        $response = $this->actingAs($data['user'])->get(route('mahasiswa.ujian.index'));

        $response->assertStatus(200);
        $pesertaUjians = $response->viewData('pesertaUjians');

        $jadwalIds = $pesertaUjians->pluck('jadwal_ujian_id');
        $this->assertTrue($jadwalIds->contains($jadwalPagi->id), 'Jadwal Pagi harus tampil untuk Mhs Pagi');
        $this->assertTrue($jadwalIds->contains($jadwalUniversal->id), 'Jadwal Universal harus tampil untuk Mhs Pagi');
    }

    /**
     * Test D2: Mahasiswa Pagi TIDAK melihat jadwal ujian tipe_waktu Sore.
     */
    public function test_mahasiswa_pagi_tidak_melihat_jadwal_sore(): void
    {
        $jadwalSore = $this->buatJadwalUjian('UTS', 'Sore');
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 14);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwalSore);

        $response = $this->actingAs($data['user'])->get(route('mahasiswa.ujian.index'));

        $pesertaUjians = $response->viewData('pesertaUjians');
        $jadwalIds = $pesertaUjians->pluck('jadwal_ujian_id');

        $this->assertFalse($jadwalIds->contains($jadwalSore->id), 'Jadwal Sore TIDAK boleh tampil untuk Mhs Pagi');
    }

    /**
     * Test D3: Mahasiswa Sore melihat jadwal Sore & Universal, TIDAK melihat Pagi.
     */
    public function test_mahasiswa_sore_filter_tipe_kelas(): void
    {
        $jadwalPagi = $this->buatJadwalUjian('UTS', 'Pagi');
        $jadwalSore = $this->buatJadwalUjian('UAS', 'Sore');

        $data = $this->buatMahasiswaLengkap('Sore', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 14);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwalPagi);
        $service->generatePesertaUjian($jadwalSore);

        $response = $this->actingAs($data['user'])->get(route('mahasiswa.ujian.index'));

        $pesertaUjians = $response->viewData('pesertaUjians');
        $jadwalIds = $pesertaUjians->pluck('jadwal_ujian_id');

        $this->assertTrue($jadwalIds->contains($jadwalSore->id), 'Jadwal Sore harus tampil untuk Mhs Sore');
        $this->assertFalse($jadwalIds->contains($jadwalPagi->id), 'Jadwal Pagi TIDAK boleh tampil untuk Mhs Sore');
    }

    /**
     * Test D4: Mahasiswa yang layak dapat mengajukan cetak kartu.
     */
    public function test_mahasiswa_layak_dapat_mengajukan_cetak(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 14);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();
        $this->assertTrue($peserta->is_eligible);

        $response = $this->actingAs($data['user'])
            ->post(route('mahasiswa.ujian.ajukan-cetak', $peserta->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $peserta->refresh();
        $this->assertEquals(PesertaUjian::CETAK_DIMINTA, $peserta->status_cetak);
        $this->assertNotNull($peserta->diminta_pada);
    }

    /**
     * Test D5: Mahasiswa yang TIDAK layak tidak bisa mengajukan cetak.
     */
    public function test_mahasiswa_tidak_layak_tidak_bisa_ajukan_cetak(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 4);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();
        $this->assertFalse($peserta->is_eligible);

        $response = $this->actingAs($data['user'])
            ->post(route('mahasiswa.ujian.ajukan-cetak', $peserta->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $peserta->refresh();
        $this->assertEquals(PesertaUjian::CETAK_BELUM, $peserta->status_cetak);
    }

    /**
     * Test D6: Mahasiswa tidak bisa ajukan cetak dua kali (sudah diminta).
     */
    public function test_mahasiswa_tidak_bisa_ajukan_cetak_dua_kali(): void
    {
        $jadwal = $this->buatJadwalUjian();
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($data['riwayat']->id, 14);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();

        // Ajukan pertama kali
        $this->actingAs($data['user'])
            ->post(route('mahasiswa.ujian.ajukan-cetak', $peserta->id));

        // Ajukan kedua kali
        $response = $this->actingAs($data['user'])
            ->post(route('mahasiswa.ujian.ajukan-cetak', $peserta->id));

        $response->assertRedirect();
        $response->assertSessionHas('info');
    }

    /**
     * Test D7: Mahasiswa tidak bisa ajukan cetak kartu ujian milik mahasiswa lain (ownership).
     */
    public function test_mahasiswa_tidak_bisa_ajukan_cetak_milik_orang_lain(): void
    {
        $jadwal = $this->buatJadwalUjian();

        // Mahasiswa A: pemilik peserta ujian
        $dataA = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($dataA['riwayat']->id, 14);

        $service = app(UjianService::class);
        $service->generatePesertaUjian($jadwal);

        $pesertaA = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)
            ->where('peserta_kelas_kuliah_id', $dataA['pkk']->id)
            ->first();

        // Mahasiswa B: mencoba ajukan cetak milik A
        $dataB = $this->buatMahasiswaLengkap('Pagi', 'acc');

        $response = $this->actingAs($dataB['user'])
            ->post(route('mahasiswa.ujian.ajukan-cetak', $pesertaA->id));

        // Controller catches ModelNotFoundException via try-catch → redirect with error
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ═════════════════════════════════════════════════════════
    // E. FLOW END-TO-END: JADWAL → GENERATE → CETAK
    // ═════════════════════════════════════════════════════════

    /**
     * Test E1: Alur lengkap – Admin buat jadwal → generate peserta → mahasiswa ajukan cetak → admin cetak.
     */
    public function test_alur_lengkap_jadwal_sampai_cetak(): void
    {
        // 1. Admin buat jadwal
        $ruang = \App\Models\Ruang::first() ?? \App\Models\Ruang::create([
            'kode_ruang' => 'R03',
            'nama_ruang' => 'Ruang 03',
            'kapasitas' => 40
        ]);

        $dataJadwal = [
            'kelas_kuliah_id' => $this->kelasKuliah->id,
            'id_semester' => $this->semester->id_semester,
            'ruang_id' => $ruang->id,
            'tipe_ujian' => 'UTS',
            'tanggal_ujian' => now()->addDays(10)->format('Y-m-d'),
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'tipe_waktu' => 'Universal',
        ];
        $this->actingAs($this->adminUser)->post(route('admin.ujian.store'), $dataJadwal);
        $jadwal = JadwalUjian::where('kelas_kuliah_id', $this->kelasKuliah->id)->first();
        $this->assertNotNull($jadwal, 'Jadwal harus berhasil dibuat');

        // 2. Buat mahasiswa dengan KRS ACC + kehadiran cukup
        $mhsData = $this->buatMahasiswaLengkap('Pagi', 'acc');
        $this->buatDataPresensi($mhsData['riwayat']->id, 13);

        // 3. Admin generate peserta
        $this->actingAs($this->adminUser)->post(route('admin.ujian.generate-peserta', $jadwal->id));
        $peserta = PesertaUjian::where('jadwal_ujian_id', $jadwal->id)->first();
        $this->assertNotNull($peserta);
        $this->assertTrue($peserta->is_eligible);
        $this->assertEquals(PesertaUjian::CETAK_BELUM, $peserta->status_cetak);

        // 4. Mahasiswa ajukan cetak
        $this->actingAs($mhsData['user'])->post(route('mahasiswa.ujian.ajukan-cetak', $peserta->id));
        $peserta->refresh();
        $this->assertEquals(PesertaUjian::CETAK_DIMINTA, $peserta->status_cetak);

        // 5. Admin cetak kartu
        $this->actingAs($this->adminUser)->post(route('admin.ujian.mark-printed', [$jadwal->id, $peserta->id]));
        $peserta->refresh();
        $this->assertEquals(PesertaUjian::CETAK_DICETAK, $peserta->status_cetak);
        $this->assertNotNull($peserta->dicetak_pada);
    }

    // ═════════════════════════════════════════════════════════
    // F. AUTHORIZATION & SECURITY
    // ═════════════════════════════════════════════════════════

    /**
     * Test F1: Mahasiswa tidak bisa mengakses halaman admin ujian.
     */
    public function test_mahasiswa_tidak_bisa_akses_halaman_admin_ujian(): void
    {
        $data = $this->buatMahasiswaLengkap('Pagi', 'acc');

        $response = $this->actingAs($data['user'])
            ->get(route('admin.ujian.index'));

        // Harus redirect atau forbidden (tergantung middleware)
        $this->assertTrue(
            in_array($response->status(), [302, 403]),
            'Mahasiswa harus diblokir dari halaman admin (got: ' . $response->status() . ')'
        );
    }

    /**
     * Test F2: Guest tidak bisa mengakses halaman ujian.
     */
    public function test_guest_tidak_bisa_akses_halaman_ujian(): void
    {
        $response = $this->get(route('admin.ujian.index'));
        $response->assertRedirect(route('login'));
    }
}
