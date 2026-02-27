<?php

use App\Http\Controllers\DosenController;
use App\Http\Controllers\DosenPengajarKelasController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\RiwayatPendidikanMahasiswaController;
use App\Http\Controllers\JadwalGlobalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        // Init active role if not set
        if (!session()->has('active_role') && auth()->user()->roles->count() > 0) {
            session(['active_role' => auth()->user()->roles->first()->name]);
        }

        $activeRole = session('active_role');
        $routeName = match ($activeRole) {
            'admin' => 'admin.dashboard',
            'Dosen' => 'dosen.dashboard',
            'Mahasiswa' => 'mahasiswa.dashboard',
            'Kaprodi' => 'kaprodi.dashboard',
            default => null,
        };

        if ($routeName && Route::has($routeName)) {
            return redirect()->route($routeName);
        }

        return view('dashboard.index');
    })->name('dashboard');

    Route::post('/switch-role', [\App\Http\Controllers\ActiveRoleController::class, 'switchRole'])->name('role.switch');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.admin');
    })->name('dashboard');

    Route::get('/mahasiswa/random', [MahasiswaController::class, 'random'])->name('mahasiswa.random');

    // Mahasiswa Detail Sub-menus
    Route::get('/mahasiswa/{id}/detail', [MahasiswaController::class, 'detail'])->name('mahasiswa.detail');
    Route::get('/mahasiswa/{id}/histori', [MahasiswaController::class, 'histori'])->name('mahasiswa.histori');
    Route::get('/mahasiswa/{id}/krs', [MahasiswaController::class, 'krs'])->name('mahasiswa.krs');
    Route::get('/mahasiswa/{id}/akun', [MahasiswaController::class, 'akun'])->name('mahasiswa.akun');

    // Mahasiswa Sync & CRUD
    Route::post('mahasiswa/generate-user/{mahasiswa}', [MahasiswaController::class, 'generateUser'])->name('mahasiswa.generate-user');
    Route::post('mahasiswa/bulk-generate-users', [MahasiswaController::class, 'bulkGenerateUsers'])->name('mahasiswa.bulk-generate-users');
    Route::resource('mahasiswa', MahasiswaController::class);

    // Riwayat Pendidikan CRUD (store, edit, update, destroy)
    Route::resource('riwayat-pendidikan', RiwayatPendidikanMahasiswaController::class)
        ->only(['store', 'edit', 'update', 'destroy']);

    // Dosen Sync & CRUD
    Route::post('dosen/sync', [DosenController::class, 'sync'])->name('dosen.sync');
    Route::post('dosen/generate-user/{dosen}', [DosenController::class, 'generateUser'])->name('dosen.generate-user');
    Route::post('dosen/bulk-generate-users', [DosenController::class, 'bulkGenerateUsers'])->name('dosen.bulk-generate-users');
    Route::resource('dosen', DosenController::class);
    // Mata Kuliah
    Route::resource('mata-kuliah', MataKuliahController::class);

    // Kurikulum
    Route::post('kurikulum/sync', [KurikulumController::class, 'sync'])->name('kurikulum.sync');
    Route::post('kurikulum/{id}/matkul', [KurikulumController::class, 'storeMatkul'])->name('kurikulum.matkul.store');
    Route::delete('kurikulum/{id}/matkul/{id_matkul}', [KurikulumController::class, 'destroyMatkul'])->name('kurikulum.matkul.destroy');
    Route::resource('kurikulum', KurikulumController::class);

    // Kelas Kuliah & Peserta
    Route::resource('kelas-kuliah', \App\Http\Controllers\KelasKuliahController::class);

    Route::post('peserta-kelas-kuliah/kolektif', [\App\Http\Controllers\PesertaKelasKuliahController::class, 'storeKolektif'])
        ->name('peserta-kelas-kuliah.store-kolektif');

    Route::resource('peserta-kelas-kuliah', \App\Http\Controllers\PesertaKelasKuliahController::class)
        ->only(['store', 'destroy']);
    Route::resource('users', \App\Http\Controllers\Admin\StaffUserController::class);
    Route::post('users/{user}/assign-role', [\App\Http\Controllers\Admin\StaffUserController::class, 'assignRole'])->name('users.assign-role');

    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);

    Route::get('/api/prodi-by-pt/{id_perguruan_tinggi}', [RiwayatPendidikanMahasiswaController::class, 'getProdiByPt'])
        ->name('api.prodi-by-pt');

    // Wilayah Routes (AJAX)
    Route::controller(App\Http\Controllers\Admin\WilayahController::class)
        ->prefix('wilayah')
        ->name('wilayah.')
        ->group(function () {
            Route::get('/kabupaten/{provinsi}', 'getKabupaten')->name('kabupaten');
            Route::get('/kecamatan/{kabupaten}', 'getKecamatan')->name('kecamatan');
            Route::get('/search/negara', 'searchNegara')->name('search.negara');
        });

    Route::get('/monitoring/perkuliahan', [\App\Http\Controllers\Admin\MonitoringPerkuliahanController::class, 'index'])->name('monitoring.perkuliahan');

    // Pembimbing Akademik mapping (Collective)
    Route::get('pembimbing-akademik/dosen-by-prodi/{id_prodi}', [\App\Http\Controllers\Admin\PembimbingAkademikController::class, 'getDosenByProdi'])->name('pembimbing-akademik.dosen-by-prodi');
    Route::post('pembimbing-akademik/copy-semester', [\App\Http\Controllers\Admin\PembimbingAkademikController::class, 'copySemester'])->name('pembimbing-akademik.copy-semester');
    Route::resource('pembimbing-akademik', \App\Http\Controllers\Admin\PembimbingAkademikController::class);

    // KRS Period Settings
    Route::resource('krs-period', \App\Http\Controllers\Admin\KrsPeriodController::class);

    // Manajemen Kaprodi
    Route::get('kaprodi/search-dosen', [\App\Http\Controllers\Admin\KaprodiController::class, 'searchDosen'])->name('kaprodi.search-dosen');
    Route::resource('kaprodi', \App\Http\Controllers\Admin\KaprodiController::class);

});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::post('kelas-dosen', [DosenPengajarKelasController::class, 'store'])->name('kelas.dosen.store');
    Route::delete('kelas-dosen/{dosen_pengajar}', [DosenPengajarKelasController::class, 'destroy'])->name('kelas.dosen.destroy');

    // Route untuk Peserta Kelas Kuliah (KRS)
    Route::post('peserta-kelas/{kelasKuliah}', [\App\Http\Controllers\PesertaKelasKuliahController::class, 'store'])->name('peserta-kelas.store');
    Route::delete('peserta-kelas/{pesertaKelasKuliah}', [\App\Http\Controllers\PesertaKelasKuliahController::class, 'destroy'])->name('peserta-kelas.destroy');

    // Route Transaksi Jadwal Kuliah (Pencegahan Double Booking / Double Teaching)
    Route::post('jadwal-kuliah', [\App\Http\Controllers\JadwalKuliahController::class, 'store'])->name('admin.jadwal-kuliah.store');
    Route::put('jadwal-kuliah/{id}', [\App\Http\Controllers\JadwalKuliahController::class, 'update'])->name('admin.jadwal-kuliah.update');
    Route::delete('jadwal-kuliah/{id}', [\App\Http\Controllers\JadwalKuliahController::class, 'destroy'])->name('admin.jadwal-kuliah.destroy');

    // Route Master Ruangan
    Route::resource('ruangan', \App\Http\Controllers\RuangController::class)->except(['create', 'show', 'edit'])->names([
        'index' => 'admin.ruangan.index',
        'store' => 'admin.ruangan.store',
        'update' => 'admin.ruangan.update',
        'destroy' => 'admin.ruangan.destroy',
    ]);

    // Manajemen Jadwal Terpadu (Admin)
    Route::get('/jadwal-global', [JadwalGlobalController::class, 'index'])->name('admin.jadwal-global.index');
    Route::post('/jadwal-global', [JadwalGlobalController::class, 'store'])->name('admin.jadwal-global.store');
    Route::get('/jadwal-global/{id}/edit', [JadwalGlobalController::class, 'edit'])->name('admin.jadwal-global.edit');
    Route::put('/jadwal-global/{id}/update', [JadwalGlobalController::class, 'update'])->name('admin.jadwal-global.update');
    Route::get('/jadwal-global/kelas-by-semester', [JadwalGlobalController::class, 'getKelasBySemester'])->name('admin.jadwal-global.kelas-by-semester');

    // Master Semester (Routing Aktivasi Global)
    Route::get('semester', [\App\Http\Controllers\SemesterController::class, 'index'])->name('admin.semester.index');
    Route::post('semester/set-active/{id}', [\App\Http\Controllers\SemesterController::class, 'setActive'])->name('admin.semester.set-active');
});

Route::middleware(['auth', 'role:Mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Mahasiswa\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/kelas', [\App\Http\Controllers\Mahasiswa\DaftarKelasMahasiswaController::class, 'index'])->name('kelas.index');
    Route::get('/kelas/{id}', [\App\Http\Controllers\Mahasiswa\DaftarKelasMahasiswaController::class, 'show'])->name('kelas.show');
    Route::get('/jadwal', [\App\Http\Controllers\Mahasiswa\JadwalController::class, 'index'])->name('jadwal.index');
    Route::get('/presensi/show/{id}', [\App\Http\Controllers\Mahasiswa\DaftarKelasMahasiswaController::class, 'presensi'])->name('presensi.show');

    // KRS Online
    Route::get('krs', [\App\Http\Controllers\Mahasiswa\KrsController::class, 'index'])->name('krs.index');
    Route::post('krs/submit', [\App\Http\Controllers\Mahasiswa\KrsController::class, 'submit'])->name('krs.submit');
    Route::get('krs/print', [\App\Http\Controllers\Mahasiswa\KrsController::class, 'print'])->name('krs.print');
});

Route::middleware(['auth', 'role:Dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Dosen\DashboardController::class, 'index'])->name('dashboard');
    Route::get('kelas', [\App\Http\Controllers\Dosen\DaftarKelasController::class, 'index'])->name('kelas.index');
    Route::get('kelas/{id}', [\App\Http\Controllers\Dosen\DaftarKelasController::class, 'show'])->name('kelas.show');
    Route::get('jadwal', [\App\Http\Controllers\Dosen\JadwalController::class, 'index'])->name('jadwal.index');

    // Presensi & Jurnal
    Route::get('presensi/{kelasId}', [\App\Http\Controllers\Dosen\PresensiController::class, 'index'])->name('presensi.index');
    Route::get('presensi/{kelasId}/create', [\App\Http\Controllers\Dosen\PresensiController::class, 'create'])->name('presensi.create');
    Route::post('presensi/{kelasId}', [\App\Http\Controllers\Dosen\PresensiController::class, 'store'])->name('presensi.store');
    Route::get('presensi/edit/{id}', [\App\Http\Controllers\Dosen\PresensiController::class, 'edit'])->name('presensi.edit');
    Route::put('presensi/update/{id}', [\App\Http\Controllers\Dosen\PresensiController::class, 'update'])->name('presensi.update');

    // Perwalian / KRS Approval
    Route::resource('perwalian', \App\Http\Controllers\Dosen\KrsApprovalController::class)->only(['index', 'show']);
    Route::post('perwalian/{id}/approve', [\App\Http\Controllers\Dosen\KrsApprovalController::class, 'approve'])->name('perwalian.approve');
    Route::get('perwalian/{id}/print', [\App\Http\Controllers\Dosen\KrsApprovalController::class, 'print'])->name('perwalian.print');

    // Monitoring Kaprodi (Integrated into Dosen namespace)
    Route::prefix('monitoring-kaprodi')->name('monitoring-kaprodi.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dosen\Kaprodi\MonitoringController::class, 'index'])->name('index');
        Route::get('/kelas/{id}', [\App\Http\Controllers\Dosen\Kaprodi\MonitoringController::class, 'show'])->name('show');
    });

    // Input Nilai Mahasiswa
    Route::prefix('nilai')->name('nilai.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Dosen\InputNilaiController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Dosen\InputNilaiController::class, 'show'])->name('show');
        Route::post('/{id}/store', [\App\Http\Controllers\Dosen\InputNilaiController::class, 'store'])->name('store');
        Route::post('/ajax-convert', [\App\Http\Controllers\Dosen\InputNilaiController::class, 'convert'])->name('ajax-convert');
    });
});


require __DIR__ . '/auth.php';
