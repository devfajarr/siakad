<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\RiwayatPendidikanMahasiswaController;

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