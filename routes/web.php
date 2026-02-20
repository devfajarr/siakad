<?php

use App\Http\Controllers\DosenController;
use App\Http\Controllers\DosenPengajarKelasController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\RiwayatPendidikanMahasiswaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.admin');
    })->name('dashboard');

    Route::get('/mahasiswa/random', [MahasiswaController::class, 'random'])->name('mahasiswa.random');

    // Mahasiswa Detail Sub-menus
    Route::get('/mahasiswa/{id}/detail', [MahasiswaController::class, 'detail'])->name('mahasiswa.detail');
    Route::get('/mahasiswa/{id}/histori', [MahasiswaController::class, 'histori'])->name('mahasiswa.histori');
    Route::get('/mahasiswa/{id}/krs', [MahasiswaController::class, 'krs'])->name('mahasiswa.krs');

    Route::resource('mahasiswa', MahasiswaController::class);

    // Riwayat Pendidikan CRUD (store, edit, update, destroy)
    Route::resource('riwayat-pendidikan', RiwayatPendidikanMahasiswaController::class)
        ->only(['store', 'edit', 'update', 'destroy']);

    // Dosen Sync & CRUD
    Route::post('dosen/sync', [DosenController::class, 'sync'])->name('dosen.sync');
    Route::resource('dosen', DosenController::class);
    // Mata Kuliah
    Route::resource('mata-kuliah', MataKuliahController::class);

    // Kurikulum
    Route::post('kurikulum/sync', [KurikulumController::class, 'sync'])->name('kurikulum.sync');
    Route::post('kurikulum/{id}/matkul', [KurikulumController::class, 'storeMatkul'])->name('kurikulum.matkul.store');
    Route::delete('kurikulum/{id}/matkul/{id_matkul}', [KurikulumController::class, 'destroyMatkul'])->name('kurikulum.matkul.destroy');
    Route::resource('kurikulum', KurikulumController::class);

    // Kelas Kuliah
    Route::resource('kelas-kuliah', \App\Http\Controllers\KelasKuliahController::class);

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

});

Route::prefix('admin')->group(function () {
    Route::post('kelas-dosen', [DosenPengajarKelasController::class, 'store'])->name('kelas.dosen.store');
    Route::delete('kelas-dosen/{kelas_dosen}', [DosenPengajarKelasController::class, 'destroy'])->name('kelas.dosen.destroy');
});
