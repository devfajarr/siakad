<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Kaprodi;
use App\Models\KelasKuliah;
use App\Models\MataKuliah;
use App\Models\PresensiPertemuan;
use App\Models\ProgramStudi;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Str;

class KaprodiMonitoringTest extends TestCase
{
    use DatabaseTransactions;

    protected $kaprodiA;
    protected $prodiA;
    protected $prodiB;
    protected $semesterActive;
    protected $userKaprodi;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Role::firstOrCreate(['name' => 'Dosen', 'guard_name' => 'web']);

        // Setup Semester
        $this->semesterActive = Semester::firstOrCreate(
            ['a_periode_aktif' => '1'],
            ['id_semester' => '20261', 'nama_semester' => '2026 Ganjil', 'id_tahun_ajaran' => '2026']
        );

        // Setup Prodi
        $this->prodiA = ProgramStudi::firstOrCreate(
            ['id_prodi' => (string) Str::uuid()],
            ['nama_program_studi' => 'Teknik Informatika', 'kode_program_studi' => 'TI']
        );
        $this->prodiB = ProgramStudi::firstOrCreate(
            ['id_prodi' => (string) Str::uuid()],
            ['nama_program_studi' => 'Sistem Informasi', 'kode_program_studi' => 'SI']
        );

        // Setup Dosen yang juga Kaprodi
        $this->userKaprodi = User::factory()->create([
            'username' => 'kaprodi_testing_' . uniqid()
        ]);
        $this->userKaprodi->assignRole('Dosen');

        $this->kaprodiA = Dosen::withoutEvents(fn() => Dosen::create([
            'user_id' => $this->userKaprodi->id,
            'nama' => 'Dosen Kaprodi A',
            'jenis_kelamin' => 'L',
            'status_sinkronisasi' => 'lokal'
        ]));

        // Jadikan Dosen KaprodiA hanya sebagai Kaprodi di ProdiA
        Kaprodi::withoutEvents(fn() => Kaprodi::create([
            'dosen_id' => $this->kaprodiA->id,
            'id_prodi' => $this->prodiA->id_prodi
        ]));
    }

    /** @test */
    public function kaprodi_hanya_bisa_melihat_kelas_di_prodinya_sendiri()
    {
        $mkA = MataKuliah::firstOrCreate(['id_matkul' => (string) Str::uuid()], ['kode_mk' => 'MKA', 'nama_mk' => 'MK A', 'sks_mk' => 3, 'id_prodi' => $this->prodiA->id_prodi]);
        $mkB = MataKuliah::firstOrCreate(['id_matkul' => (string) Str::uuid()], ['kode_mk' => 'MKB', 'nama_mk' => 'MK B', 'sks_mk' => 3, 'id_prodi' => $this->prodiB->id_prodi]);

        // Buat kelas di Prodi A
        $kelasProdiA = KelasKuliah::withoutEvents(fn() => KelasKuliah::create([
            'id_kelas_kuliah' => (string) Str::uuid(),
            'id_prodi' => $this->prodiA->id_prodi,
            'id_semester' => $this->semesterActive->id_semester,
            'id_matkul' => $mkA->id_matkul,
            'nama_kelas_kuliah' => 'Kelas TI-A',
            'status_sinkronisasi' => 'lokal',
            'sumber_data' => 'lokal'
        ]));

        // Buat kelas di Prodi B
        $kelasProdiB = KelasKuliah::withoutEvents(fn() => KelasKuliah::create([
            'id_kelas_kuliah' => (string) Str::uuid(),
            'id_prodi' => $this->prodiB->id_prodi,
            'id_semester' => $this->semesterActive->id_semester,
            'id_matkul' => $mkB->id_matkul,
            'nama_kelas_kuliah' => 'Kelas SI-A',
            'status_sinkronisasi' => 'lokal',
            'sumber_data' => 'lokal'
        ]));

        // Login Kaprodi (User A)
        $this->actingAs($this->userKaprodi);

        // Akses halaman Index Monitoring
        $response = $this->get(route('dosen.monitoring-kaprodi.index'));

        // Assert OK
        $response->assertStatus(200);

        // Assert hanya melihat kelas dari Prodi A, dan tidak melihat kelas Prodi B
        $response->assertSee('Kelas TI-A');
        $response->assertDontSee('Kelas SI-A');

        // ==== Tes Akses Detail Kelas ====

        // Kaprodi mengakses kelas prodi A (Boleh)
        $responseDetailA = $this->get(route('dosen.monitoring-kaprodi.show', $kelasProdiA->id_kelas_kuliah));
        $responseDetailA->assertStatus(200);

        // Kaprodi mengakses kelas prodi B (Dilarang, 404/403 karena findOrFail / abort via Scope)
        $responseDetailB = $this->get(route('dosen.monitoring-kaprodi.show', $kelasProdiB->id_kelas_kuliah));
        $responseDetailB->assertStatus(404);
    }

    /** @test */
    public function kalkulasi_progress_bar_kelas_akurat()
    {
        $mkA = MataKuliah::firstOrCreate(['id_matkul' => (string) Str::uuid()], ['kode_mk' => 'MKP', 'nama_mk' => 'MK P', 'sks_mk' => 3, 'id_prodi' => $this->prodiA->id_prodi]);

        // Buat kelas di Prodi A
        $kelasProdiA = KelasKuliah::withoutEvents(fn() => KelasKuliah::create([
            'id_kelas_kuliah' => (string) Str::uuid(),
            'id_prodi' => $this->prodiA->id_prodi,
            'id_semester' => $this->semesterActive->id_semester,
            'id_matkul' => $mkA->id_matkul,
            'nama_kelas_kuliah' => 'Kelas Prog-A',
            'status_sinkronisasi' => 'lokal',
            'sumber_data' => 'lokal'
        ]));

        // Simulasikan input jurnal / presensi sebanyak 5 kali
        for ($i = 1; $i <= 5; $i++) {
            PresensiPertemuan::create([
                'id_kelas_kuliah' => $kelasProdiA->id_kelas_kuliah,
                'pertemuan_ke' => $i,
                'tanggal' => now()->subDays(10 - $i),
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
                'id_dosen' => $this->kaprodiA->id
            ]);
        }

        $this->actingAs($this->userKaprodi);

        // Akses halaman Index Monitoring
        $response = $this->get(route('dosen.monitoring-kaprodi.index'));

        // Persentase yang diharap = 5 pertemuan
        $expectedCountText = '5/' . config('academic.target_pertemuan', 14);
        $response->assertSee($expectedCountText);

        // Cek Badge Merah ('Tertinggal')
        // badge bg-label-danger -> karena 5 < 7
        $response->assertSee('bg-label-danger');
        $response->assertSee('Tertinggal');

        // Tambah lagi 8 pertemuan (total 13) - Ini masuk 'Selesai/Mendekati' (Mendekati target <=14, >=13)
        for ($i = 6; $i <= 13; $i++) {
            PresensiPertemuan::create([
                'id_kelas_kuliah' => $kelasProdiA->id_kelas_kuliah,
                'pertemuan_ke' => $i,
                'tanggal' => now()->addDays($i),
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
                'id_dosen' => $this->kaprodiA->id
            ]);
        }

        $responseSelesai = $this->get(route('dosen.monitoring-kaprodi.index'));
        $expectedCountText2 = '13/' . config('academic.target_pertemuan', 14);
        $responseSelesai->assertSee($expectedCountText2);

        $responseSelesai->assertSee('bg-label-success');
        $responseSelesai->assertSee('Selesai/Mendekati');
    }
}
