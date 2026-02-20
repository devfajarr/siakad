<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MahasiswaController;
use App\Http\Controllers\Api\V1\RiwayatPendidikanController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::apiResource('mahasiswa', MahasiswaController::class);
    Route::apiResource('riwayat-pendidikan', RiwayatPendidikanController::class);

    // User Management Routes (Public for Development)
    Route::prefix('users')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\V1\UserManagementController::class, 'index']);
        Route::post('/sync', [\App\Http\Controllers\Api\V1\UserManagementController::class, 'triggerSync']);
        Route::put('/{user}/roles', [\App\Http\Controllers\Api\V1\UserManagementController::class, 'updateRoles']);
        Route::get('/{user}', [\App\Http\Controllers\Api\V1\UserManagementController::class, 'show']);
    });
});
