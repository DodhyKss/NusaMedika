<?php

use App\Http\Controllers\API\ApiPasienController;
use App\Http\Controllers\Pendaftaran\ListPelayananController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    # ========= MODUL REGISTRASI =============
    Route::get('/list_pelayanan_pasien', [ListPelayananController::class, 'index'])->name('list_pelayanan_pasien.index');
    Route::delete('/list_pelayanan_pasien/{id}', [ListPelayananController::class, 'destroy'])->name('list_pelayanan_pasien.destroy');
    
    # ========= API ROUTES =============
    Route::get('/api/pasien/search', [ApiPasienController::class, 'searchPasien'])->name('api.pasien.search');
});
