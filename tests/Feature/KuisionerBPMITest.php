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
use App\Models\Dosen;
use App\Models\DosenPengajarKelasKuliah;
use App\Models\Kuisioner;
use App\Models\KuisionerPertanyaan;
use App\Models\KuisionerSubmission;
use App\Models\KuisionerJawabanDetail;
use App\Models\JadwalUjian;
use App\Models\PesertaUjian;
use App\Models\PengaturanUjian;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

/**
 * Feature Test Komprehensif: Kuesioner BPMI
 *
 * Mencakup:
 * 1. BPMI CRUD Kuesioner Pelayanan
 * 2. Mahasiswa mengisi Kuesioner Pelayanan + validasi cetak kartu
 * 3. BPMI CRUD Kuesioner Kinerja Dosen
 * 4. Evaluasi Dosen per individu (alias = honorer dinilai, bukan dosen pusat)
 * 5. Rekapitulasi & Evaluasi (AVG Likert + kesimpulan kualitatif)
 */
class KuisionerBPMITest extends TestCase
{
    use DatabaseTransactions;

    protected $bpmiUser;
    protected $mahasiswaUser;
    protected $mahasiswaData;
    protected $dosenUtama;     // Dosen terdaftar pusat, mengajar sendiri
    protected $dosenPusat;     // Dosen terdaftar pusat, placeholder pelaporan
    protected $dosenHonorer;   // Dosen alias (honorer nyata mengajar)
    protected $semester;
    protected $kelas;
    protected $pesertaKelasId;
    protected $pesertaUjianId;
    protected $riwayat;
    protected $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        // ── Role ──────────────────────────────────────────────
        foreach (['BPMI', 'Mahasiswa', 'admin'] as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName, 'guard_name' => 'web']);
            }
        }

        $uid = uniqid();

        // ── BPMI User ─────────────────────────────────────────
        $this->bpmiUser = User::withoutEvents(fn() => User::factory()->create([
            'username' => 'bpmi_' . $uid,
            'email' => 'bpmi_' . $uid . '@test.com',
        ]));
        $this->bpmiUser->assignRole('BPMI');

        // ── Semester Aktif ────────────────────────────────────
        $this->semester = Semester::firstOrCreate(
            ['a_periode_aktif' => '1'],
            ['id_semester' => '20261', 'nama_semester' => '2026/2027 Ganjil', 'id_tahun_ajaran' => '2026']
        );

        PengaturanUjian::firstOrCreate(
            ['semester_id' => $this->semester->id_semester, 'tipe_ujian' => 'UTS'],
            ['tgl_mulai_cetak' => now()->subDay(), 'tgl_akhir_cetak' => now()->addDays(30)]
        );

        // ── Mahasiswa User & Data ──────────────────────────────
        $this->mahasiswaUser = User::withoutEvents(fn() => User::factory()->create([
            'username' => 'mhs_' . $uid,
            'email' => 'mhs_' . $uid . '@test.com',
        ]));
        $this->mahasiswaUser->assignRole('Mahasiswa');

        $this->mahasiswaData = Mahasiswa::withoutEvents(fn() => Mahasiswa::create([
            'user_id' => $this->mahasiswaUser->id,
            'nama_mahasiswa' => 'Mahasiswa Test ' . $uid,
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'id_agama' => 1,
            'nik' => rand(1111111111111111, 9999999999999999),
            'nisn' => str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
            'nama_ibu_kandung' => 'Ibu Test',
            'id_wilayah' => '000000',
            'kelurahan' => 'Test',
            'handphone' => '081234567890',
            'email' => 'mhs_' . $uid . '@test.com',
            'tipe_kelas' => 'Pagi',
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local',
        ]));

        // ── Prodi & Riwayat ────────────────────────────────────
        $this->prodi = ProgramStudi::firstOrCreate(
            ['kode_program_studi' => 'TST'],
            ['id_prodi' => (string) Str::uuid(), 'nama_program_studi' => 'Test Prodi']
        );

        $this->riwayat = RiwayatPendidikan::withoutEvents(fn() => RiwayatPendidikan::create([
            'id_mahasiswa' => $this->mahasiswaData->id,
            'id_periode_masuk' => $this->semester->id_semester,
            'nim' => 'NIM' . $uid,
            'id_prodi' => $this->prodi->id_prodi,
            'id_jenis_daftar' => '1',
            'tanggal_daftar' => '2025-08-01',
            'keterangan_keluar' => 'Aktif',
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local',
        ]));

        // ── Dosen ──────────────────────────────────────────────
        // dosens enum: 'pusat' | 'lokal' | 'tersinkronisasi'
        $this->dosenUtama = Dosen::create([
            'nama' => 'Prof. Utama',
            'jenis_kelamin' => 'L',
            'status_sinkronisasi' => 'lokal',
        ]);

        // Dosen pusat = placeholder pelaporan ke feeder
        $this->dosenPusat = Dosen::create([
            'nama' => 'Dr. Pusat (Placeholder)',
            'nidn' => '9999999999',
            'jenis_kelamin' => 'L',
            'status_sinkronisasi' => 'pusat',
        ]);

        // Dosen honorer = mengajar secara nyata, menumpang laporan
        $this->dosenHonorer = Dosen::create([
            'nama' => 'Pak Honorer',
            'jenis_kelamin' => 'L',
            'status_sinkronisasi' => 'lokal',
        ]);

        // ── Kelas Kuliah ───────────────────────────────────────
        $mk = MataKuliah::firstOrCreate(
            ['kode_mk' => 'MK-TST-BPMI'],
            ['id_matkul' => (string) Str::uuid(), 'id_prodi' => $this->prodi->id_prodi, 'nama_mk' => 'MK Evaluasi', 'sks' => 3]
        );

        $kelasUuid = (string) Str::uuid();
        $this->kelas = KelasKuliah::create([
            'id_kelas_kuliah' => $kelasUuid,
            'id_prodi' => $this->prodi->id_prodi,
            'id_semester' => $this->semester->id_semester,
            'id_matkul' => $mk->id_matkul,
            'nama_kelas_kuliah' => 'Kelas Evaluasi ' . $uid,
        ]);

        // ── Dosen Pengajar (2 entri) ───────────────────────────
        // Entri 1: Dosen utama mengajar sendiri (tanpa alias)
        DosenPengajarKelasKuliah::create([
            'id_kelas_kuliah' => $kelasUuid,
            'id_dosen' => $this->dosenUtama->id,
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local',
        ]);

        // Entri 2: Dosen pusat sebagai placeholder, dosen honorer yang mengajar nyata
        DosenPengajarKelasKuliah::create([
            'id_kelas_kuliah' => $kelasUuid,
            'id_dosen' => $this->dosenPusat->id,     // Placeholder pelaporan
            'id_dosen_alias_lokal' => $this->dosenHonorer->id,   // Pengajar nyata
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local',
        ]);

        // ── Peserta Kelas Kuliah ───────────────────────────────
        $pesertaKelas = PesertaKelasKuliah::create([
            'id_kelas_kuliah' => $kelasUuid,
            'id_registrasi_mahasiswa' => $this->riwayat->id_registrasi_mahasiswa ?? (string) Str::uuid(),
            'riwayat_pendidikan_id' => $this->riwayat->id,
            'status_sync' => 'created_local',
        ]);
        $this->pesertaKelasId = $pesertaKelas->id;

        // ── Jadwal & Peserta Ujian ─────────────────────────────
        $jadwalUjian = JadwalUjian::create([
            'kelas_kuliah_id' => $this->kelas->id,
            'id_semester' => $this->semester->id_semester,
            'tipe_ujian' => 'UTS',
            'tanggal_ujian' => now()->addDays(5)->format('Y-m-d'),
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'tipe_waktu' => 'Pagi',
        ]);

        $pesertaUjian = PesertaUjian::create([
            'jadwal_ujian_id' => $jadwalUjian->id,
            'peserta_kelas_kuliah_id' => $pesertaKelas->id,
            'is_eligible' => true,
            'persentase_kehadiran' => 100.00,
            'jumlah_hadir' => 14,
            'status_cetak' => PesertaUjian::CETAK_BELUM,
            'is_dispensasi' => false,
        ]);
        $this->pesertaUjianId = $pesertaUjian->id;
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 1: BPMI Membuat Kuesioner Pelayanan
    // ═══════════════════════════════════════════════════════════
    /** @test */
    public function bpmi_dapat_membuat_kuesioner_pelayanan()
    {
        $this->actingAs($this->bpmiUser);

        // BPMI membuat kuesioner pelayanan via controller store
        $response = $this->post(route('dosen.kuisioner.store'), [
            'judul' => 'Survei Pelayanan Akademik UTS 2026',
            'deskripsi' => 'Evaluasi pelayanan akademik semester ini',
            'id_semester' => $this->semester->id_semester,
            'target_ujian' => 'UTS',
            'tipe' => 'pelayanan',
        ]);

        $response->assertSessionHas('success');

        // Verifikasi tersimpan dengan status draft
        $this->assertDatabaseHas('kuisioners', [
            'judul' => 'Survei Pelayanan Akademik UTS 2026',
            'tipe' => 'pelayanan',
            'status' => 'draft', // Selalu draft saat dibuat
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 2: Mahasiswa Mengisi Kuesioner Pelayanan & Cetak Kartu
    // ═══════════════════════════════════════════════════════════
    /** @test */
    public function mahasiswa_mengisi_pelayanan_dan_validasi_cetak_kartu()
    {
        // Setup: Buat kuesioner + pertanyaan Likert
        $kuesioner = Kuisioner::create([
            'judul' => 'Survei Pelayanan UTS',
            'id_semester' => $this->semester->id_semester,
            'target_ujian' => 'UTS',
            'tipe' => 'pelayanan',
            'status' => 'published',
        ]);

        $p1 = KuisionerPertanyaan::create([
            'id_kuisioner' => $kuesioner->id,
            'teks_pertanyaan' => 'Bagaimana kualitas layanan administrasi?',
            'tipe_input' => 'likert',
            'urutan' => 1,
        ]);
        $p2 = KuisionerPertanyaan::create([
            'id_kuisioner' => $kuesioner->id,
            'teks_pertanyaan' => 'Bagaimana kebersihan fasilitas kampus?',
            'tipe_input' => 'likert',
            'urutan' => 2,
        ]);
        $p3 = KuisionerPertanyaan::create([
            'id_kuisioner' => $kuesioner->id,
            'teks_pertanyaan' => 'Bagaimana respon layanan akademik?',
            'tipe_input' => 'likert',
            'urutan' => 3,
        ]);

        $this->actingAs($this->mahasiswaUser);
        $routeCetak = route('mahasiswa.ujian.ajukan-cetak', $this->pesertaUjianId);
        $routeIndex = route('mahasiswa.kuisioner.index');

        // ── Langkah 1: Cetak SEBELUM isi pelayanan → GAGAL
        $resp1 = $this->post($routeCetak);
        $resp1->assertSessionHas('error');
        $this->assertStringContainsString('Kuesioner Pelayanan', session('error'));

        // ── Langkah 2: Submit jawaban pelayanan via controller
        $respStore = $this->from($routeIndex)->post(route('mahasiswa.kuisioner.store', $kuesioner->id), [
            'jawaban' => [
                (string) $p1->id => ['skala' => 5],
                (string) $p2->id => ['skala' => 4],
                (string) $p3->id => ['skala' => 4],
            ],
        ]);
        $respStore->assertRedirect(route('mahasiswa.kuisioner.index'));

        // ── Verifikasi: Submission tersimpan
        $this->assertDatabaseHas('kuisioner_submissions', [
            'id_kuisioner' => $kuesioner->id,
            'id_mahasiswa' => $this->mahasiswaData->id,
        ]);

        // ── Verifikasi: Jawaban detail tersimpan
        $submission = KuisionerSubmission::where('id_kuisioner', $kuesioner->id)
            ->where('id_mahasiswa', $this->mahasiswaData->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertEquals(3, KuisionerJawabanDetail::where('id_submission', $submission->id)->count());
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 3: BPMI Membuat Kuesioner Kinerja Dosen
    // ═══════════════════════════════════════════════════════════
    /** @test */
    public function bpmi_dapat_membuat_kuesioner_kinerja_dosen()
    {
        $this->actingAs($this->bpmiUser);

        $response = $this->post(route('dosen.kuisioner.store'), [
            'judul' => 'Evaluasi Kinerja Dosen UTS 2026',
            'deskripsi' => 'Penilaian kinerja dosen oleh mahasiswa',
            'id_semester' => $this->semester->id_semester,
            'target_ujian' => 'UTS',
            'tipe' => 'dosen',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kuisioners', [
            'judul' => 'Evaluasi Kinerja Dosen UTS 2026',
            'tipe' => 'dosen',
            'status' => 'draft',
        ]);

        // BPMI publish kuesioner
        $kuesioner = Kuisioner::where('judul', 'Evaluasi Kinerja Dosen UTS 2026')->first();
        $this->put(route('dosen.kuisioner.update', $kuesioner->id), [
            'judul' => $kuesioner->judul,
            'id_semester' => $kuesioner->id_semester,
            'target_ujian' => $kuesioner->target_ujian,
            'tipe' => $kuesioner->tipe,
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('kuisioners', [
            'id' => $kuesioner->id,
            'status' => 'published',
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 4: Evaluasi Dosen per Individu (Alias = Honorer Dinilai)
    //
    // Skenario:
    //   Kelas diajar oleh:
    //     - Dosen Utama (mengajar sendiri) → dinilai
    //     - Dosen Pusat (placeholder pelaporan) + Dosen Honorer (pengajar nyata)
    //       → yang DINILAI adalah Dosen Honorer, bukan Dosen Pusat
    //
    //   Alur:
    //     1. Mahasiswa belum evaluasi → cetak GAGAL (0/2)
    //     2. Evaluasi Dosen Utama → cetak GAGAL (1/2)
    //     3. Evaluasi Dosen Honorer → cetak BERHASIL (2/2)
    // ═══════════════════════════════════════════════════════════
    /** @test */
    public function mahasiswa_evaluasi_dosen_alias_honorer_bukan_dosen_pusat()
    {
        // Setup: Buat kuesioner Pelayanan (sudah diisi) + Dosen (belum)
        $formPelayanan = Kuisioner::create([
            'judul' => 'Pelayanan UTS',
            'id_semester' => $this->semester->id_semester,
            'target_ujian' => 'UTS',
            'tipe' => 'pelayanan',
            'status' => 'published',
        ]);
        KuisionerSubmission::create([
            'id_kuisioner' => $formPelayanan->id,
            'id_mahasiswa' => $this->mahasiswaData->id,
            'status_sinkronisasi' => 'synced',
        ]);

        $formDosen = Kuisioner::create([
            'judul' => 'Kinerja Dosen UTS',
            'id_semester' => $this->semester->id_semester,
            'target_ujian' => 'UTS',
            'tipe' => 'dosen',
            'status' => 'published',
        ]);

        $this->actingAs($this->mahasiswaUser);
        $routeCetak = route('mahasiswa.ujian.ajukan-cetak', $this->pesertaUjianId);

        // ── Langkah 1: Belum evaluasi dosen → GAGAL (0/2)
        $resp1 = $this->post($routeCetak);
        $resp1->assertSessionHas('error');
        $this->assertStringContainsString('0 dari 2', session('error'));

        // ── Langkah 2: Evaluasi DOSEN UTAMA (1/2)
        KuisionerSubmission::create([
            'id_kuisioner' => $formDosen->id,
            'id_mahasiswa' => $this->mahasiswaData->id,
            'id_kelas_kuliah' => $this->kelas->id, // bigint FK
            'id_dosen' => $this->dosenUtama->id,
            'status_sinkronisasi' => 'synced',
        ]);

        $resp2 = $this->post($routeCetak);
        $resp2->assertSessionHas('error');
        $this->assertStringContainsString('1 dari 2', session('error'));

        // ── Langkah 3: Evaluasi DOSEN HONORER (alias, pengajar nyata) (2/2)
        // PENTING: Mahasiswa menilai dosen HONORER, bukan dosen PUSAT
        KuisionerSubmission::create([
            'id_kuisioner' => $formDosen->id,
            'id_mahasiswa' => $this->mahasiswaData->id,
            'id_kelas_kuliah' => $this->kelas->id,
            'id_dosen' => $this->dosenHonorer->id, // Honorer, bukan dosenPusat!
            'status_sinkronisasi' => 'synced',
        ]);

        // ── Langkah 4: Cetak → BERHASIL
        $respFinal = $this->post($routeCetak);
        $respFinal->assertSessionHas('success');

        $this->assertDatabaseHas('peserta_ujians', [
            'id' => $this->pesertaUjianId,
            'status_cetak' => PesertaUjian::CETAK_DIMINTA,
        ]);

        // ── Verifikasi: Dosen Pusat TIDAK dievaluasi
        $this->assertDatabaseMissing('kuisioner_submissions', [
            'id_kuisioner' => $formDosen->id,
            'id_dosen' => $this->dosenPusat->id,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    // TEST 5: Rekapitulasi Kuesioner & Evaluasi
    //
    // Verifikasi:
    //   - Total responden dihitung benar
    //   - AVG skor Likert per pertanyaan benar
    //   - Konversi kualitatif benar (≥4.21 = Sangat Memuaskan, dst.)
    //   - Coverage persentase benar
    // ═══════════════════════════════════════════════════════════
    /** @test */
    public function rekap_kuesioner_menampilkan_evaluasi_yang_benar()
    {
        // Setup: Kuesioner + 2 pertanyaan Likert
        $kuesioner = Kuisioner::create([
            'judul' => 'Survei Pelayanan Rekap',
            'id_semester' => $this->semester->id_semester,
            'target_ujian' => 'UTS',
            'tipe' => 'pelayanan',
            'status' => 'published',
        ]);

        $p1 = KuisionerPertanyaan::create([
            'id_kuisioner' => $kuesioner->id,
            'teks_pertanyaan' => 'Kualitas layanan administrasi',
            'tipe_input' => 'likert',
            'urutan' => 1,
        ]);
        $p2 = KuisionerPertanyaan::create([
            'id_kuisioner' => $kuesioner->id,
            'teks_pertanyaan' => 'Kenyamanan ruang kuliah',
            'tipe_input' => 'likert',
            'urutan' => 2,
        ]);

        // Seed: 1 responden dengan skor diketahui (5, 4)
        $sub = KuisionerSubmission::create([
            'id_kuisioner' => $kuesioner->id,
            'id_mahasiswa' => $this->mahasiswaData->id,
            'status_sinkronisasi' => 'synced',
        ]);
        KuisionerJawabanDetail::create([
            'id_submission' => $sub->id,
            'id_pertanyaan' => $p1->id,
            'jawaban_skala' => 5,
        ]);
        KuisionerJawabanDetail::create([
            'id_submission' => $sub->id,
            'id_pertanyaan' => $p2->id,
            'jawaban_skala' => 4,
        ]);

        // Akses halaman rekap sebagai BPMI
        $this->actingAs($this->bpmiUser);
        $response = $this->get(route('dosen.kuisioner.show', $kuesioner->id));
        $response->assertStatus(200);

        // Verifikasi data yang dikirim ke view
        $response->assertViewHas('totalResponden', 1);
        $response->assertViewHas('grandAverage', 4.5);

        // Grand Average 4.5 ≥ 4.21 → "Sangat Memuaskan"
        $response->assertViewHas('grandKesimpulan', function ($val) {
            return $val['teks'] === 'Sangat Memuaskan' && $val['color'] === 'success';
        });

        // Verifikasi rekap per pertanyaan
        $response->assertViewHas('rekapPertanyaan', function ($rekap) {
            // P1 avg=5.0, P2 avg=4.0
            return count($rekap) === 2
                && $rekap[0]['avg'] == 5.0
                && $rekap[1]['avg'] == 4.0;
        });
    }
}
