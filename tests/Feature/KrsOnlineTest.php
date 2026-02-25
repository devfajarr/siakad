<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\KrsPeriod;
use App\Models\Semester;
use App\Models\RiwayatPendidikan;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\MataKuliah;
use App\Models\KelasKuliah;
use App\Models\PesertaKelasKuliah;
use App\Models\PembimbingAkademik;
use Illuminate\Support\Str;

class KrsOnlineTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $mahasiswa;
    protected $semester;
    protected $prodi;
    protected $dosen;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup User
        $this->user = User::factory()->create([
            'username' => 'testuser' . uniqid(),
            'email' => 'test' . uniqid() . '@example.com'
        ]);
        $this->user->assignRole('Mahasiswa');

        // 2. Ensure Active Semester
        $this->semester = Semester::where('a_periode_aktif', '1')->first();
        if (!$this->semester) {
            $this->semester = Semester::create([
                'id_semester' => '20231',
                'nama_semester' => '2023/2024 Ganjil',
                'id_tahun_ajaran' => '2023',
                'a_periode_aktif' => '1'
            ]);
        }

        // 3. Ensure Program Studi
        $this->prodi = ProgramStudi::first(); // Use any existing prodi
        if (!$this->prodi) {
            $this->prodi = ProgramStudi::create([
                'id_prodi' => (string) Str::uuid(),
                'nama_program_studi' => 'Test Prodi',
                'kode_program_studi' => 'TEST'
            ]);
        }

        // 4. Setup Dosen PA
        $dosenUser = User::factory()->create(['username' => 'dosen' . uniqid()]);
        $this->dosen = Dosen::create([
            'user_id' => $dosenUser->id,
            'nama' => 'Dosen PA Test',
            'nidn' => 'TEST' . rand(1000, 9999),
            'email' => $dosenUser->email,
        ]);

        PembimbingAkademik::create([
            'id_dosen' => $this->dosen->id,
            'id_prodi' => $this->prodi->id_prodi,
            'id_semester' => $this->semester->id_semester
        ]);

        // 5. Setup Mahasiswa
        $this->mahasiswa = Mahasiswa::create([
            'user_id' => $this->user->id,
            'nama_mahasiswa' => 'Test Student',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'id_agama' => 1,
            'nik' => '12345' . rand(10000, 99999) . '678',
            'nisn' => '1234' . rand(1000, 9999) . '0',
            'nama_ibu_kandung' => 'Mother Name',
            'id_wilayah' => '000000',
            'kelurahan' => 'Test Kelurahan',
            'handphone' => '0812' . rand(1000, 9999) . '90',
            'email' => $this->user->email,
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local'
        ]);

        $riwayat = RiwayatPendidikan::create([
            'id_mahasiswa' => $this->mahasiswa->id,
            'nim' => 'TEST' . rand(1000, 9999),
            'id_jenis_daftar' => '1',
            'id_periode_masuk' => $this->semester->id_semester,
            'tanggal_daftar' => now()->format('Y-m-d'),
            'id_prodi' => $this->prodi->id_prodi,
            'status_sinkronisasi' => 'lokal',
            'sumber_data' => 'lokal'
        ]);

        // 6. Setup Kelas & Enrollment (Paket)
        $mk = MataKuliah::create([
            'id_matkul' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'kode_mk' => 'MK' . rand(100, 999),
            'nama_mk' => 'Test Mata Kuliah',
            'sks' => 2,
            'status_aktif' => true,
        ]);

        $kelas = KelasKuliah::create([
            'id_kelas_kuliah' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'id_semester' => $this->semester->id_semester,
            'id_matkul' => $mk->id_matkul,
            'nama_kelas_kuliah' => 'A',
            'sks_mk' => 2,
            'kapasitas' => 40
        ]);

        PesertaKelasKuliah::create([
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'riwayat_pendidikan_id' => $riwayat->id,
            'status_krs' => 'paket',
            'sumber_data' => 'lokal'
        ]);
    }

    public function test_student_cannot_submit_when_period_closed(): void
    {
        KrsPeriod::updateOrCreate(
            ['id_semester' => $this->semester->id_semester],
            [
                'is_active' => true,
                'tgl_mulai' => now()->subDays(10),
                'tgl_selesai' => now()->subDays(5),
                'nama_periode' => 'KRS Closed'
            ]
        );

        $response = $this->actingAs($this->user)
            ->post(route('mahasiswa.krs.submit'), [
                'id_semester' => $this->semester->id_semester
            ]);

        $response->assertSessionHas('error');
        $this->assertTrue(str_contains(session('error'), 'ditutup'));
    }

    public function test_student_can_submit_when_period_open(): void
    {
        KrsPeriod::updateOrCreate(
            ['id_semester' => $this->semester->id_semester],
            [
                'is_active' => true,
                'tgl_mulai' => now()->subDay(),
                'tgl_selesai' => now()->addDay(),
                'nama_periode' => 'KRS Open'
            ]
        );

        $response = $this->actingAs($this->user)
            ->post(route('mahasiswa.krs.submit'), [
                'id_semester' => $this->semester->id_semester
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_student_can_submit_via_bypass_when_period_closed(): void
    {
        KrsPeriod::updateOrCreate(
            ['id_semester' => $this->semester->id_semester],
            [
                'is_active' => true,
                'tgl_mulai' => now()->subDays(10),
                'tgl_selesai' => now()->subDays(5),
                'nama_periode' => 'KRS Closed'
            ]
        );

        $this->mahasiswa->refresh();
        $this->mahasiswa->update(['bypass_krs_until' => now()->addDay()]);

        $response = $this->actingAs($this->user)
            ->post(route('mahasiswa.krs.submit'), [
                'id_semester' => $this->semester->id_semester
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
