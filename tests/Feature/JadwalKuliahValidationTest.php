<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ruang;
use App\Models\KelasKuliah;
use App\Models\Dosen;
use App\Models\DosenPengajarKelasKuliah;
use App\Models\JadwalKuliah;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JadwalKuliahValidationTest extends TestCase
{
    // Menggunakan DatabaseTransactions agar data dummy di-\rollback/ tidak disimpan permanen setelah pengujian
    use DatabaseTransactions;

    protected $adminUser;
    protected $ruangA;
    protected $ruangB;
    protected $kelas1;
    protected $kelas2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup User Admin (Dengan Spatie Permission)
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser = User::factory()->create([
            'username' => 'admin_test_' . Str::random(5)
        ]);
        $this->adminUser->assignRole($role);

        // 2. Setup Master Ruangan
        $this->ruangA = Ruang::create([
            'kode_ruang' => 'R101',
            'nama_ruang' => 'Ruang Teori 101',
            'kapasitas' => 40
        ]);

        $this->ruangB = Ruang::create([
            'kode_ruang' => 'R102',
            'nama_ruang' => 'Ruang Teori 102',
            'kapasitas' => 40
        ]);

        // 3. Setup Master Kelas Kuliah
        $this->kelas1 = KelasKuliah::create([
            'id_kelas_kuliah' => Str::uuid()->toString(),
            'nama_kelas_kuliah' => 'Kelas X',
            'id_semester' => '20251',
            'id_prodi' => Str::uuid()->toString(),
            'id_matkul' => Str::uuid()->toString(),
            'sks_mk' => 3
        ]);

        $this->kelas2 = KelasKuliah::create([
            'id_kelas_kuliah' => Str::uuid()->toString(),
            'nama_kelas_kuliah' => 'Kelas Y',
            'id_semester' => '20251',
            'id_prodi' => Str::uuid()->toString(),
            'id_matkul' => Str::uuid()->toString(),
            'sks_mk' => 3
        ]);

        // 4. Setup Relasi Dosen ke Kelas Kuliah
        $dosen = Dosen::create([
            'nama' => 'Dosen Penguji A',
            'nidn' => '123456789',
            'status_sinkronisasi' => 'lokal',
            'is_pengajar' => true
        ]);

        // Dosen A mengajar di Kelas X (Kelas1) dan Kelas Y (Kelas2)
        DosenPengajarKelasKuliah::create([
            'id_kelas_kuliah' => $this->kelas1->id_kelas_kuliah,
            'id_dosen' => $dosen->id,
        ]);

        DosenPengajarKelasKuliah::create([
            'id_kelas_kuliah' => $this->kelas2->id_kelas_kuliah,
            'id_dosen' => $dosen->id,
        ]);
    }

    /**
     * Test 1: Skenario Input Jadwal Normal (Ruangan & Waktu Kosong)
     */
    public function test_berhasil_menyimpan_jadwal_normal()
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.jadwal-kuliah.store'), [
            'kelas_kuliah_id' => $this->kelas1->id,
            'ruang_id' => $this->ruangA->id,
            'hari' => 1, // Senin
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'jenis_pertemuan' => 'Tatap Muka'
        ]);

        // Ekspektasi berhasil redirect back dengan session 'success'
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Assert data masuk secara benar di database
        $this->assertDatabaseHas('jadwal_kuliahs', [
            'kelas_kuliah_id' => $this->kelas1->id,
            'ruang_id' => $this->ruangA->id,
            'hari' => 1,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
        ]);
    }

    /**
     * Test 2: Skenario Gagal karena Bentrok Ruangan dan Waktu
     */
    public function test_gagal_karena_bentrok_ruangan_dan_waktu()
    {
        // 1. Insert jadwal pertama untuk Kelas X di Ruang A jam 08:00 - 10:00
        JadwalKuliah::create([
            'kelas_kuliah_id' => $this->kelas1->id,
            'ruang_id' => $this->ruangA->id,
            'hari' => 1,
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
        ]);

        // 2. Coba post jadwal kedua untuk Kelas Y di ruangan dan waktu yang saling tumpang tindih
        $response = $this->actingAs($this->adminUser)->post(route('admin.jadwal-kuliah.store'), [
            'kelas_kuliah_id' => $this->kelas2->id,
            'ruang_id' => $this->ruangA->id, // BENTROK RUANG!
            'hari' => 1,
            'jam_mulai' => '09:00', // BENTROK JAM (Overlap dengan 08:00 - 10:00)
            'jam_selesai' => '11:00',
        ]);

        // Ekspektasi gagal dan mendapat return session error "Terdapat bentrok jadwal..."
        $response->assertStatus(302);
        $response->assertSessionHas('error');

        // Pastikan tidak ada jadwal kelas 2 di database
        $this->assertDatabaseMissing('jadwal_kuliahs', [
            'kelas_kuliah_id' => $this->kelas2->id,
        ]);
    }

    /**
     * Test 3: Skenario Gagal Karena Double Teaching (Dosen mengajar di tempat lain pada jam yang sama)
     */
    public function test_gagal_karena_dosen_mengajar_di_kelas_lain()
    {
        // 1. Buat jadwal pertama untuk Kelas X di Ruang A jam 08:00 - 10:00
        JadwalKuliah::create([
            'kelas_kuliah_id' => $this->kelas1->id,
            'ruang_id' => $this->ruangA->id,
            'hari' => 1,
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
        ]);

        // 2. Coba post jadwal kedua untuk Kelas Y di RUANG BERBEDA tapi JAM SAMA
        // Mengingat Kelas X dan Kelas Y keduanya diajar oleh Dosen A, maka ini menyalahi aturan logika manusia (Double Teaching).
        $response = $this->actingAs($this->adminUser)->post(route('admin.jadwal-kuliah.store'), [
            'kelas_kuliah_id' => $this->kelas2->id,
            'ruang_id' => $this->ruangB->id, // Ruang B (Tidak bentrok ruang)
            'hari' => 1,
            'jam_mulai' => '08:00', // Tapi bentrok jam
            'jam_selesai' => '10:00',
        ]);

        // Ekspektasi gagal karena Dosen A tidak mungkin mengajar 2 kelas di waktu/hari bersamaan
        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('jadwal_kuliahs', [
            'kelas_kuliah_id' => $this->kelas2->id,
            'ruang_id' => $this->ruangB->id,
        ]);
    }

    /**
     * Test 4: Skenario Berhasil Melakukan Edit/Update tanpa Menabrak Dirinya Sendiri
     */
    public function test_berhasil_update_jadwal_tanpa_menabrak_diri_sendiri()
    {
        // 1. Buat jadwal awal
        $jadwal = JadwalKuliah::create([
            'kelas_kuliah_id' => $this->kelas1->id,
            'ruang_id' => $this->ruangA->id,
            'hari' => 1,
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'jenis_pertemuan' => 'Teori'
        ]);

        // 2. Lakukan operasi Update. Ruangan dan jam tetap sama persis, hanya ubah keterangan jenis pertemuan
        $response = $this->actingAs($this->adminUser)->put(route('admin.jadwal-kuliah.update', $jadwal->id), [
            'kelas_kuliah_id' => $this->kelas1->id,
            'ruang_id' => $this->ruangA->id,
            'hari' => 1,
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'jenis_pertemuan' => 'Praktikum Ujian'
        ]);

        // Ekspektasi berhasil di-update tanpa memicu validasi bentrok (Kecerdasan $ignoreJadwalId dari Service)
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('jadwal_kuliahs', [
            'id' => $jadwal->id,
            'jenis_pertemuan' => 'Praktikum Ujian'
        ]);
    }
}
