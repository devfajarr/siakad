<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Dosen;
use App\Models\KelasKuliah;
use App\Models\MataKuliah;
use App\Models\PresensiPertemuan;
use App\Models\DosenPengajarKelasKuliah;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class MonitoringPerkuliahanKeuanganTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan Master Role
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Keuangan', 'guard_name' => 'web']);
    }

    public function test_halaman_monitoring_hanya_bisa_diakses_role_tertentu()
    {
        // Setup User Biasa
        $userBiasa = User::factory()->create(['username' => 'user_biasa_' . uniqid()]);

        $this->actingAs($userBiasa)->get(route('admin.keuangan-modul.monitoring-perkuliahan.index'))
            ->assertStatus(403);

        // Setup User Keuangan
        $userKeuangan = User::factory()->create(['username' => 'user_keu_' . uniqid()]);
        $userKeuangan->assignRole('Keuangan');

        $this->actingAs($userKeuangan)->get(route('admin.keuangan-modul.monitoring-perkuliahan.index'))
            ->assertStatus(200)
            ->assertSee('Monitoring Perkuliahan');
    }

    public function test_honor_kalkulasi_hanya_menghitung_status_terverifikasi()
    {
        $userAdmin = User::factory()->create(['username' => 'admin_test_' . uniqid()]);
        $userAdmin->assignRole('admin');

        // Buat Dosen dan Kelas Manual karena belum ada Factory MatKul / Kelas Kuliah
        $dosen = Dosen::create([
            'nama' => 'Dr. Rino Pengajar',
            'nidn' => '0011223344',
            'is_active' => true,
            'status_sinkronisasi' => 'pusat'
        ]);

        $uuidProdi = Str::uuid()->toString();
        $uuidMatkul = Str::uuid()->toString();
        $uuidKelas = Str::uuid()->toString();

        $mataKuliah = MataKuliah::create([
            'id_matkul' => $uuidMatkul,
            'kode_mk' => 'MK-TEST-001',
            'nama_mk' => 'Matematika Dasar',
            'sks_mata_kuliah' => '3',
            'id_prodi' => $uuidProdi
        ]);

        $kelas = KelasKuliah::create([
            'id_kelas_kuliah' => $uuidKelas,
            'nama_kelas_kuliah' => 'Kelas A',
            'id_matkul' => $uuidMatkul,
            'id_prodi' => $uuidProdi,
            'id_semester' => '20251'
        ]);

        DosenPengajarKelasKuliah::create([
            'id_dosen' => $dosen->id,
            'id_kelas_kuliah' => $uuidKelas,
        ]);

        // Buat 3 Pertemuan dengan Status Berbeda (Bulan Sama)
        $tanggalIni = now()->format('Y-m-d');
        PresensiPertemuan::create([
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'id_dosen' => $dosen->id,
            'pertemuan_ke' => 1,
            'tanggal' => $tanggalIni,
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'status_verifikasi' => PresensiPertemuan::STATUS_TERVERIFIKASI
        ]);

        PresensiPertemuan::create([
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'id_dosen' => $dosen->id,
            'pertemuan_ke' => 2,
            'tanggal' => $tanggalIni,
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'status_verifikasi' => PresensiPertemuan::STATUS_PENDING
        ]);

        PresensiPertemuan::create([
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'id_dosen' => $dosen->id,
            'pertemuan_ke' => 3,
            'tanggal' => $tanggalIni,
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'status_verifikasi' => PresensiPertemuan::STATUS_DITOLAK
        ]);

        // Aksi Load Halaman & Render Blade
        $response = $this->actingAs($userAdmin)->get(route('admin.keuangan-modul.monitoring-perkuliahan.index', ['bulan_tahun' => now()->format('Y-m')]));

        $response->assertStatus(200);

        // Harus tercetak "1" Total Sah 
        $response->assertSee('Dr. Rino Pengajar');

        // Assert view value
        $dosenRekap = $response->viewData('dosenRekap');
        $this->assertCount(1, $dosenRekap);

        $rekapRino = collect($dosenRekap)->firstWhere('nama_dosen', 'Dr. Rino Pengajar');
        $this->assertEquals(3, $rekapRino['total_pertemuan']);
        $this->assertEquals(1, $rekapRino['total_terverifikasi']);
        $this->assertEquals(2, $rekapRino['total_pending']); // Pending = selain terverifikasi didalam view function
        $this->assertEquals(100000, $rekapRino['estimasi_honor']); // 1 terverifikasi x 100000
    }

    public function test_unduh_laporan_excel_rekapitulasi_keuangan()
    {
        $userKeuangan = User::factory()->create(['username' => 'export_keu_' . uniqid()]);
        $userKeuangan->assignRole('Keuangan');

        $response = $this->actingAs($userKeuangan)->get(route('admin.keuangan-modul.monitoring-perkuliahan.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=Rekapitulasi_Honor_Dosen_All.xlsx');
    }
}
