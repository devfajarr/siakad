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
use App\Models\JadwalKuliah;
use App\Models\Ruang;
use Illuminate\Support\Str;

/**
 * Test: Filtering Jadwal Kuliah berdasarkan Tipe Kelas Mahasiswa (Pagi/Sore).
 *
 * Aturan bisnis:
 * - Mahasiswa Pagi -> melihat jadwal tipe_waktu = 'Pagi' dan 'Universal'
 * - Mahasiswa Sore -> melihat jadwal tipe_waktu = 'Sore' dan 'Universal'
 * - Jadwal 'Pagi' TIDAK boleh tampil untuk mahasiswa Sore, dan sebaliknya.
 */
class JadwalTipeKelasFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected $semester;
    protected $prodi;
    protected $kelas;
    protected $ruang;
    protected $jadwalPagi;
    protected $jadwalSore;
    protected $jadwalUniversal;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Pastikan Semester Aktif
        $this->semester = Semester::where('a_periode_aktif', '1')->first();
        if (!$this->semester) {
            $this->semester = Semester::create([
                'id_semester' => '20251',
                'nama_semester' => '2025/2026 Ganjil',
                'id_tahun_ajaran' => '2025',
                'a_periode_aktif' => '1'
            ]);
        }

        // 2. Pastikan Program Studi
        $this->prodi = ProgramStudi::first();
        if (!$this->prodi) {
            $this->prodi = ProgramStudi::create([
                'id_prodi' => (string) Str::uuid(),
                'nama_program_studi' => 'Prodi Test Jadwal',
                'kode_program_studi' => 'PTJ'
            ]);
        }

        // 3. Setup Mata Kuliah & Kelas Kuliah
        $mk = MataKuliah::create([
            'id_matkul' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'kode_mk' => 'MK' . rand(100, 999),
            'nama_mk' => 'Mata Kuliah Testing Jadwal',
            'sks' => 3,
            'status_aktif' => true,
        ]);

        $this->kelas = KelasKuliah::create([
            'id_kelas_kuliah' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'id_semester' => $this->semester->id_semester,
            'id_matkul' => $mk->id_matkul,
            'nama_kelas_kuliah' => 'Kelas Test Tipe Waktu',
            'sks_mk' => 3,
            'kapasitas' => 40,
        ]);

        // 4. Setup Ruangan
        $this->ruang = Ruang::create([
            'kode_ruang' => 'RTW' . rand(100, 999),
            'nama_ruang' => 'Ruang Test Tipe Waktu',
            'kapasitas' => 40,
        ]);

        // 5. Buat 3 Jadwal dengan tipe_waktu berbeda pada kelas yang sama
        $this->jadwalPagi = JadwalKuliah::create([
            'kelas_kuliah_id' => $this->kelas->id,
            'ruang_id' => $this->ruang->id,
            'hari' => 1,
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'jenis_pertemuan' => 'Tatap Muka',
            'tipe_waktu' => 'Pagi',
        ]);

        $this->jadwalSore = JadwalKuliah::create([
            'kelas_kuliah_id' => $this->kelas->id,
            'ruang_id' => $this->ruang->id,
            'hari' => 2,
            'jam_mulai' => '15:00',
            'jam_selesai' => '17:00',
            'jenis_pertemuan' => 'Tatap Muka',
            'tipe_waktu' => 'Sore',
        ]);

        $this->jadwalUniversal = JadwalKuliah::create([
            'kelas_kuliah_id' => $this->kelas->id,
            'ruang_id' => $this->ruang->id,
            'hari' => 3,
            'jam_mulai' => '10:00',
            'jam_selesai' => '12:00',
            'jenis_pertemuan' => 'Tatap Muka',
            'tipe_waktu' => 'Universal',
        ]);
    }

    /**
     * Helper: Buat user mahasiswa lengkap dengan riwayat dan enrollment KRS.
     */
    private function buatMahasiswaDenganTipeKelas(string $tipeKelas): array
    {
        $user = User::factory()->create([
            'username' => 'mhs_' . strtolower($tipeKelas) . '_' . uniqid(),
            'email' => strtolower($tipeKelas) . '_' . uniqid() . '@test.com',
        ]);
        $user->assignRole('Mahasiswa');

        $mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nama_mahasiswa' => 'Mahasiswa ' . $tipeKelas . ' Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2002-01-01',
            'id_agama' => 1,
            'nik' => rand(1000000000000000, 9999999999999999),
            'nisn' => str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
            'nama_ibu_kandung' => 'Ibu ' . $tipeKelas,
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

        // Daftarkan ke kelas kuliah (KRS paket)
        PesertaKelasKuliah::create([
            'id_kelas_kuliah' => $this->kelas->id_kelas_kuliah,
            'riwayat_pendidikan_id' => $riwayat->id,
            'status_krs' => 'paket',
            'sumber_data' => 'lokal',
        ]);

        return ['user' => $user, 'mahasiswa' => $mahasiswa];
    }

    /**
     * Helper: Akses halaman KRS dan ambil data jadwal yang tampil untuk mahasiswa.
     *
     * @return \Illuminate\Support\Collection ID jadwal yang tampil
     */
    private function getJadwalIdsDariKrs(User $user): \Illuminate\Support\Collection
    {
        $response = $this->actingAs($user)->get(route('mahasiswa.krs.index'));
        $response->assertStatus(200);

        $krsItems = $response->viewData('krsItems');
        $this->assertNotNull($krsItems, 'krsItems tidak boleh null');

        // Kumpulkan semua ID jadwal dari semua kelas di KRS
        $jadwalIds = collect();
        foreach ($krsItems as $item) {
            foreach ($item->kelasKuliah->jadwalKuliahs as $jadwal) {
                $jadwalIds->push($jadwal->id);
            }
        }

        return $jadwalIds;
    }

    // =====================================================================
    // TEST CASE MAHASISWA PAGI
    // =====================================================================

    /**
     * Test 1: Mahasiswa Pagi melihat jadwal tipe_waktu = 'Pagi'
     */
    public function test_mahasiswa_pagi_melihat_jadwal_pagi(): void
    {
        $data = $this->buatMahasiswaDenganTipeKelas('Pagi');
        $jadwalIds = $this->getJadwalIdsDariKrs($data['user']);

        $this->assertTrue(
            $jadwalIds->contains($this->jadwalPagi->id),
            'Jadwal Pagi HARUS tampil untuk Mahasiswa Pagi'
        );
    }

    /**
     * Test 2: Mahasiswa Pagi melihat jadwal tipe_waktu = 'Universal'
     */
    public function test_mahasiswa_pagi_melihat_jadwal_universal(): void
    {
        $data = $this->buatMahasiswaDenganTipeKelas('Pagi');
        $jadwalIds = $this->getJadwalIdsDariKrs($data['user']);

        $this->assertTrue(
            $jadwalIds->contains($this->jadwalUniversal->id),
            'Jadwal Universal HARUS tampil untuk Mahasiswa Pagi'
        );
    }

    /**
     * Test 3: Mahasiswa Pagi TIDAK melihat jadwal tipe_waktu = 'Sore'
     */
    public function test_mahasiswa_pagi_tidak_melihat_jadwal_sore(): void
    {
        $data = $this->buatMahasiswaDenganTipeKelas('Pagi');
        $jadwalIds = $this->getJadwalIdsDariKrs($data['user']);

        $this->assertFalse(
            $jadwalIds->contains($this->jadwalSore->id),
            'Jadwal Sore TIDAK BOLEH tampil untuk Mahasiswa Pagi'
        );
    }

    // =====================================================================
    // TEST CASE MAHASISWA SORE
    // =====================================================================

    /**
     * Test 4: Mahasiswa Sore melihat jadwal tipe_waktu = 'Sore'
     */
    public function test_mahasiswa_sore_melihat_jadwal_sore(): void
    {
        $data = $this->buatMahasiswaDenganTipeKelas('Sore');
        $jadwalIds = $this->getJadwalIdsDariKrs($data['user']);

        $this->assertTrue(
            $jadwalIds->contains($this->jadwalSore->id),
            'Jadwal Sore HARUS tampil untuk Mahasiswa Sore'
        );
    }

    /**
     * Test 5: Mahasiswa Sore melihat jadwal tipe_waktu = 'Universal'
     */
    public function test_mahasiswa_sore_melihat_jadwal_universal(): void
    {
        $data = $this->buatMahasiswaDenganTipeKelas('Sore');
        $jadwalIds = $this->getJadwalIdsDariKrs($data['user']);

        $this->assertTrue(
            $jadwalIds->contains($this->jadwalUniversal->id),
            'Jadwal Universal HARUS tampil untuk Mahasiswa Sore'
        );
    }

    /**
     * Test 6: Mahasiswa Sore TIDAK melihat jadwal tipe_waktu = 'Pagi'
     */
    public function test_mahasiswa_sore_tidak_melihat_jadwal_pagi(): void
    {
        $data = $this->buatMahasiswaDenganTipeKelas('Sore');
        $jadwalIds = $this->getJadwalIdsDariKrs($data['user']);

        $this->assertFalse(
            $jadwalIds->contains($this->jadwalPagi->id),
            'Jadwal Pagi TIDAK BOLEH tampil untuk Mahasiswa Sore'
        );
    }
}
