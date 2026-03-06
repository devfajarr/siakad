<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
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
use App\Models\KomponenBiaya;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Services\TagihanService;
use App\Notifications\PengajuanKrsNotification;
use App\Notifications\PersetujuanKrsNotification;
use App\Notifications\UploadPembayaranNotification;
use App\Notifications\PembayaranDisetujuiNotification;
use App\Notifications\PembayaranDitolakNotification;
use Illuminate\Support\Str;

class NotificationSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected User $studentUser;
    protected User $dosenUser;
    protected User $adminUser;
    protected Mahasiswa $mahasiswa;
    protected Dosen $dosen;
    protected Semester $semester;
    protected ProgramStudi $prodi;
    protected TagihanService $tagihanService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tagihanService = app(TagihanService::class);

        // ── Admin / Keuangan User ──
        $this->adminUser = User::factory()->create([
            'username' => 'notif_admin_' . uniqid(),
            'email' => 'notif_admin_' . uniqid() . '@example.com'
        ]);
        $this->adminUser->assignRole('admin');

        // ── Dosen PA User ──
        $this->dosenUser = User::factory()->create([
            'username' => 'notif_dosen_' . uniqid(),
            'email' => 'notif_dosen_' . uniqid() . '@example.com'
        ]);
        $this->dosenUser->assignRole('Dosen');

        // ── Student User ──
        $this->studentUser = User::factory()->create([
            'username' => 'notif_mhs_' . uniqid(),
            'email' => 'notif_mhs_' . uniqid() . '@example.com'
        ]);
        $this->studentUser->assignRole('Mahasiswa');

        // ── Semester Aktif ──
        $this->semester = Semester::where('a_periode_aktif', '1')->first();
        if (!$this->semester) {
            $this->semester = Semester::create([
                'id_semester' => '20241',
                'nama_semester' => '2024/2025 Ganjil',
                'id_tahun_ajaran' => '2024',
                'a_periode_aktif' => '1'
            ]);
        }

        // ── Program Studi ──
        $this->prodi = ProgramStudi::create([
            'id_prodi' => (string) Str::uuid(),
            'nama_program_studi' => 'Prodi Notif Test ' . uniqid(),
            'kode_program_studi' => 'NT' . rand(100, 999)
        ]);

        // ── Dosen PA ──
        $this->dosen = Dosen::create([
            'user_id' => $this->dosenUser->id,
            'nama' => 'Dosen Notif Test',
            'nidn' => 'NTF' . rand(1000, 9999),
            'email' => $this->dosenUser->email,
        ]);

        PembimbingAkademik::create([
            'id_dosen' => $this->dosen->id,
            'id_prodi' => $this->prodi->id_prodi,
            'id_semester' => $this->semester->id_semester
        ]);

        // ── Mahasiswa ──
        $this->mahasiswa = Mahasiswa::create([
            'user_id' => $this->studentUser->id,
            'nama_mahasiswa' => 'Mahasiswa Notif Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2000-01-01',
            'id_agama' => 1,
            'nik' => '1234' . rand(100000, 999999) . str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT),
            'nisn' => '12' . rand(10000000, 99999999),
            'nama_ibu_kandung' => 'Ibu Notif',
            'id_wilayah' => '000000',
            'kelurahan' => 'Test Kel',
            'handphone' => '0812' . rand(10000000, 99999999),
            'email' => $this->studentUser->email,
            'sumber_data' => 'lokal',
            'status_sinkronisasi' => 'created_local'
        ]);

        RiwayatPendidikan::create([
            'id_mahasiswa' => $this->mahasiswa->id,
            'nim' => 'NTF' . rand(10000, 99999),
            'id_jenis_daftar' => '1',
            'id_periode_masuk' => $this->semester->id_semester,
            'tanggal_daftar' => now()->format('Y-m-d'),
            'id_prodi' => $this->prodi->id_prodi,
            'status_sinkronisasi' => 'lokal',
            'sumber_data' => 'lokal'
        ]);
    }

    // ═══════════════════════════════════════════════════════
    //  TEST 1: Pengajuan KRS -> Notifikasi ke Dosen PA
    // ═══════════════════════════════════════════════════════

    public function test_submit_krs_sends_notification_to_dosen_pa(): void
    {
        Notification::fake();

        // Setup KRS Period terbuka
        KrsPeriod::updateOrCreate(
            ['id_semester' => $this->semester->id_semester],
            [
                'is_active' => true,
                'tgl_mulai' => now()->subDay(),
                'tgl_selesai' => now()->addDay(),
                'nama_periode' => 'KRS Notif Test'
            ]
        );

        // Setup kelas + enrollment
        $mk = MataKuliah::create([
            'id_matkul' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'kode_mk' => 'NF' . rand(100, 999),
            'nama_mk' => 'MK Notif Test',
            'sks' => 3,
            'status_aktif' => true,
        ]);

        $kelas = KelasKuliah::create([
            'id_kelas_kuliah' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'id_semester' => $this->semester->id_semester,
            'id_matkul' => $mk->id_matkul,
            'nama_kelas_kuliah' => 'A-NF',
            'sks_mk' => 3,
            'kapasitas' => 40,
        ]);

        $riwayat = RiwayatPendidikan::where('id_mahasiswa', $this->mahasiswa->id)->first();
        PesertaKelasKuliah::create([
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'riwayat_pendidikan_id' => $riwayat->id,
            'status_krs' => 'paket',
            'sumber_data' => 'lokal',
        ]);

        // Submit KRS
        $response = $this->actingAs($this->studentUser)
            ->post(route('mahasiswa.krs.submit'), [
                'id_semester' => $this->semester->id_semester
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert notifikasi dikirim ke Dosen PA
        Notification::assertSentTo(
            $this->dosenUser,
            PengajuanKrsNotification::class,
            function ($notification, $channels) {
                $this->assertContains('database', $channels);
                return true;
            }
        );
    }

    // ═══════════════════════════════════════════════════════
    //  TEST 2: ACC KRS oleh Dosen -> Notifikasi ke Mahasiswa
    // ═══════════════════════════════════════════════════════

    public function test_approve_krs_sends_notification_to_mahasiswa(): void
    {
        Notification::fake();

        // Setup kelas + enrollment dengan status pending
        $mk = MataKuliah::create([
            'id_matkul' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'kode_mk' => 'AC' . rand(100, 999),
            'nama_mk' => 'MK ACC Test',
            'sks' => 2,
            'status_aktif' => true,
        ]);

        $kelas = KelasKuliah::create([
            'id_kelas_kuliah' => (string) Str::uuid(),
            'id_prodi' => $this->prodi->id_prodi,
            'id_semester' => $this->semester->id_semester,
            'id_matkul' => $mk->id_matkul,
            'nama_kelas_kuliah' => 'A-AC',
            'sks_mk' => 2,
            'kapasitas' => 40,
        ]);

        $riwayat = RiwayatPendidikan::where('id_mahasiswa', $this->mahasiswa->id)->first();
        PesertaKelasKuliah::create([
            'id_kelas_kuliah' => $kelas->id_kelas_kuliah,
            'riwayat_pendidikan_id' => $riwayat->id,
            'status_krs' => 'pending',
            'sumber_data' => 'lokal',
        ]);

        // Dosen PA approve KRS
        $response = $this->actingAs($this->dosenUser)
            ->post(route('dosen.perwalian.approve', $this->mahasiswa->id), [
                'id_semester' => $this->semester->id_semester
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert notifikasi dikirim ke Mahasiswa
        Notification::assertSentTo(
            $this->studentUser,
            PersetujuanKrsNotification::class,
            function ($notification, $channels) {
                $this->assertContains('database', $channels);
                return true;
            }
        );
    }

    // ═══════════════════════════════════════════════════════
    //  TEST 3: Upload Bukti Bayar -> Notifikasi Broadcast ke Admin/Keuangan
    // ═══════════════════════════════════════════════════════

    public function test_upload_pembayaran_sends_notification_to_admin_keuangan(): void
    {
        Notification::fake();

        // Buat tagihan dulu
        KomponenBiaya::create([
            'kode_komponen' => 'NTF-T' . rand(100, 999),
            'nama_komponen' => 'SPP Notif Test',
            'kategori' => 'per_semester',
            'nominal_standar' => 3000000,
            'is_wajib_krs' => true,
            'is_active' => true,
            'id_prodi' => $this->prodi->id_prodi,
        ]);

        $tagihan = $this->tagihanService->terbitkanTagihan(
            $this->mahasiswa,
            $this->semester->id_semester,
            $this->prodi->id_prodi
        );

        // Upload bukti (Fake file upload)
        $file = \Illuminate\Http\UploadedFile::fake()->image('bukti_bayar.jpg', 600, 400);

        $response = $this->actingAs($this->studentUser)
            ->post(route('mahasiswa.keuangan.upload', $tagihan->id), [
                'jumlah_bayar' => 1000000,
                'tanggal_bayar' => now()->format('Y-m-d'),
                'bukti_bayar' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert notifikasi dikirim ke Admin
        Notification::assertSentTo(
            $this->adminUser,
            UploadPembayaranNotification::class,
            function ($notification, $channels) {
                $this->assertContains('database', $channels);
                return true;
            }
        );
    }

    // ═══════════════════════════════════════════════════════
    //  TEST 4: Verifikasi Pembayaran Disetujui -> Notifikasi ke Mahasiswa
    // ═══════════════════════════════════════════════════════

    public function test_approve_pembayaran_sends_notification_to_mahasiswa(): void
    {
        Notification::fake();

        $tagihan = Tagihan::create([
            'nomor_tagihan' => 'INV/TEST/' . rand(10000, 99999),
            'id_mahasiswa' => $this->mahasiswa->id,
            'id_semester' => $this->semester->id_semester,
            'total_tagihan' => 2000000,
            'status' => 'belum_bayar',
        ]);

        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => 2000000,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'bukti_bayar' => 'private/bukti-bayar/notif_test.jpg',
            'status_verifikasi' => Pembayaran::STATUS_PENDING,
        ]);

        // Admin verifikasi: setujui
        $this->tagihanService->verifikasiPembayaran($pembayaran, true, null, $this->adminUser);

        // Assert notifikasi DISETUJUI dikirim ke Mahasiswa
        Notification::assertSentTo(
            $this->studentUser,
            PembayaranDisetujuiNotification::class,
            function ($notification, $channels) {
                $this->assertContains('database', $channels);
                return true;
            }
        );
    }

    // ═══════════════════════════════════════════════════════
    //  TEST 5: Verifikasi Pembayaran Ditolak -> Notifikasi ke Mahasiswa
    // ═══════════════════════════════════════════════════════

    public function test_reject_pembayaran_sends_notification_to_mahasiswa(): void
    {
        Notification::fake();

        $tagihan = Tagihan::create([
            'nomor_tagihan' => 'INV/TEST/' . rand(10000, 99999),
            'id_mahasiswa' => $this->mahasiswa->id,
            'id_semester' => $this->semester->id_semester,
            'total_tagihan' => 1000000,
            'status' => 'belum_bayar',
        ]);

        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'jumlah_bayar' => 1000000,
            'tanggal_bayar' => now()->format('Y-m-d'),
            'bukti_bayar' => 'private/bukti-bayar/notif_reject.jpg',
            'status_verifikasi' => Pembayaran::STATUS_PENDING,
        ]);

        // Admin verifikasi: tolak
        $this->tagihanService->verifikasiPembayaran($pembayaran, false, 'Bukti tidak valid', $this->adminUser);

        // Assert notifikasi DITOLAK dikirim ke Mahasiswa
        Notification::assertSentTo(
            $this->studentUser,
            PembayaranDitolakNotification::class,
            function ($notification, $channels) {
                $this->assertContains('database', $channels);
                return true;
            }
        );
    }

    // ═══════════════════════════════════════════════════════
    //  TEST 6: Halaman Notifikasi Bisa Diakses Semua Role
    // ═══════════════════════════════════════════════════════

    public function test_mahasiswa_can_access_notification_page(): void
    {
        $response = $this->actingAs($this->studentUser)
            ->get(route('notifikasi.index'));

        $response->assertStatus(200);
    }

    public function test_dosen_can_access_notification_page(): void
    {
        $response = $this->actingAs($this->dosenUser)
            ->get(route('notifikasi.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_access_notification_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('notifikasi.index'));

        $response->assertStatus(200);
    }

    // ═══════════════════════════════════════════════════════
    //  TEST 7: Mark as Read & Mark All as Read
    // ═══════════════════════════════════════════════════════

    public function test_user_can_mark_notification_as_read(): void
    {
        // Kirim notifikasi ke student user secara manual
        $this->studentUser->notify(new PersetujuanKrsNotification(
            $this->semester->id_semester,
            'Dosen Test'
        ));

        $notification = $this->studentUser->unreadNotifications()->first();
        $this->assertNotNull($notification);

        $response = $this->actingAs($this->studentUser)
            ->post(route('notifikasi.read', $notification->id));

        $response->assertRedirect();

        // Reload dan cek sudah terbaca
        $notification->refresh();
        $this->assertNotNull($notification->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        // Kirim 3 notifikasi
        $this->studentUser->notify(new PersetujuanKrsNotification($this->semester->id_semester, 'Dosen A'));
        $this->studentUser->notify(new PersetujuanKrsNotification($this->semester->id_semester, 'Dosen B'));
        $this->studentUser->notify(new PersetujuanKrsNotification($this->semester->id_semester, 'Dosen C'));

        $this->assertEquals(3, $this->studentUser->unreadNotifications()->count());

        $response = $this->actingAs($this->studentUser)
            ->post(route('notifikasi.read-all'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Semua sudah terbaca
        $this->assertEquals(0, $this->studentUser->fresh()->unreadNotifications()->count());
    }
}
