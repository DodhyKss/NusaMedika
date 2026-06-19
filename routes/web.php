<?php

use App\Http\Controllers\API\ApiPasienController;
use App\Http\Controllers\EMR\EmrDashboard\EmrDashboardController;
use App\Http\Controllers\GawatDarurat\Pasien\ListPasienIGD\ListPasienIGDController;
use App\Http\Controllers\RawatInap\Pasien\ListPasien\ListPasienRanapController;
use App\Http\Controllers\RawatJalan\Pasien\ListPasien\ListPasienRajalController;
use App\Http\Controllers\Registrasi\Pasien\DataPasienController;
use App\Http\Controllers\Registrasi\Pasien\NasabahPasienController;
use App\Http\Controllers\Registrasi\Pendaftaran\DaftarIGD\DaftarIGDController;
use App\Http\Controllers\Registrasi\Pendaftaran\DaftarIGDObgyn\DaftarIGDObgynController;
use App\Http\Controllers\Registrasi\Pendaftaran\DaftarRajal\DaftarRajalController;
use App\Http\Controllers\Registrasi\Pendaftaran\DaftarRanap\DaftarRanapController;
use App\Http\Controllers\Registrasi\Pendaftaran\ListPelayanan\ListPelayananController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;

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

    # ======================================= #
    #             MODUL REGISTRASI            #
    # ======================================= #
    # ========= MENU PASIEN ================== #
    // GET
    Route::get('/daftar_pasien', [DataPasienController::class, 'index'])->name('daftar_pasien.index');
    Route::get('/nasabah_pasien', [NasabahPasienController::class, 'index'])->name('nasabah_pasien.index');

    # ========= MENU PENDAFTARAN ============= #
    // GET
    Route::get('/list_pelayanan_pasien', [ListPelayananController::class, 'index'])->name('list_pelayanan_pasien.index');
    Route::get('/daftar_rajal', [DaftarRajalController::class, 'index'])->name('daftar_rajal.index');
    Route::get('/daftar_ranap', [DaftarRanapController::class, 'index'])->name('daftar_ranap.index');
    Route::get('/registrasi_igd', [DaftarIGDController::class, 'index'])->name('registrasi_igd.index');
    Route::get('/registrasi_igd_obgyn', [DaftarIGDObgynController::class, 'index'])->name('registrasi_igd_obgyn.index');
    // DELETE 
    Route::delete('/list_pelayanan_pasien/{id}', [ListPelayananController::class, 'destroy'])->name('list_pelayanan_pasien.destroy');

    # ======================================= #
    #             MODUL EMR                   #
    # ======================================= # 
    # ============ DASHBOARD PASIEN ========= #
    // GET
    Route::get('/dashboard_pasien/{registrasi_detail_id}', [EmrDashboardController::class, 'index'])->name('dashboard_pasien.index');

    # ======================================= #
    #             MODUL RAWAT JALAN           #
    # ======================================= # 
    # ========== MENU PASIEN ================ #
    // GET
    Route::get('/list_pasien_dokter', [ListPasienRajalController::class, 'index'])->name('list_pasien_dokter.index');

    # ======================================= #
    #             MODUL RAWAT INAP            #
    # ======================================= # 
    # ========== MENU PASIEN ================ #
    Route::get('/list_pasien_ranap', [ListPasienRanapController::class, 'index'])->name('list_pasien_ranap.index');

    # ======================================= #
    #             MODUL GAWAT DARURAT         #
    # ======================================= # 
    # ========== MENU PASIEN ================ #
    Route::get('/list_pasien_igd', [ListPasienIGDController::class, 'index'])->name('list_pasien_igd.index');

    # ========= API ROUTES =============
    Route::get('/api/pasien/search', [ApiPasienController::class, 'searchPasien'])->name('api.pasien.search');
});
