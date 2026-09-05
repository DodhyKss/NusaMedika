<?php

use App\Http\Controllers\API\ApiIcdController;
use App\Http\Controllers\API\ApiNasabahController;
use App\Http\Controllers\API\ApiPasienController;
use App\Http\Controllers\API\ApiPegawaiController;
use App\Http\Controllers\API\ApiWilayahController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\EMR\DynamicFormController;
use App\Http\Controllers\EMR\EmrDashboard\EmrDashboardController;
use App\Http\Controllers\EMR\PengkajianAwalKeperawatan\PengkajianAwalKeperawatanController;
use App\Http\Controllers\EMR\Soap\SoapController;
use Illuminate\Support\Facades\Route;

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

    // ===== MODUL REGISTRASI (otomatis dari sub_menu) =====

    // ======================================= #
    //             MODUL EMR                   #
    // ======================================= #
    // ============ DASHBOARD PASIEN ========= #
    // GET
    Route::get('/dashboard_pasien/{registrasi_detail_id}', [EmrDashboardController::class, 'index'])->name('dashboard_pasien.index');

    // ============ DYNAMIC EMR ROUTE ======== #
    Route::get('/emr/form/{form_name}/{registrasi_detail_id}/{emr_id?}', [DynamicFormController::class, 'index'])->name('emr.dynamic.index');

    // ============ SOAP (CPPT) ============== #
    Route::get('/emr/soap/print/{emr_id}', [SoapController::class, 'print'])->name('emr.soap.print');
    // Route::get('/emr/{registrasi_detail_id}/soap/{emr_id?}', [\App\Http\Controllers\EMR\Soap\SoapController::class, 'index'])->name('emr.soap.index');
    Route::post('/emr/{registrasi_detail_id}/soap', [SoapController::class, 'store'])->name('emr.soap.store');
    Route::put('/emr/{registrasi_detail_id}/soap/{emr_id}', [SoapController::class, 'update'])->name('emr.soap.update');
    Route::delete('/emr/{registrasi_detail_id}/soap/{emr_id}', [SoapController::class, 'destroy'])->name('emr.soap.destroy');

    // ============ PENGKAJIAN AWAL KEPERAWATAN ============ #
    // Route::get('/emr/{registrasi_detail_id}/pengkajian_awal_keperawatan/{emr_id?}', [\App\Http\Controllers\EMR\PengkajianAwalKeperawatan\PengkajianAwalKeperawatanController::class, 'index'])->name('emr.pengkajian_awal_keperawatan.index');
    Route::post('/emr/{registrasi_detail_id}/pengkajian_awal_keperawatan', [PengkajianAwalKeperawatanController::class, 'store'])->name('emr.pengkajian_awal_keperawatan.store');
    Route::put('/emr/{registrasi_detail_id}/pengkajian_awal_keperawatan/{emr_id}', [PengkajianAwalKeperawatanController::class, 'update'])->name('emr.pengkajian_awal_keperawatan.update');
    Route::delete('/emr/{registrasi_detail_id}/pengkajian_awal_keperawatan/{emr_id}', [PengkajianAwalKeperawatanController::class, 'destroy'])->name('emr.pengkajian_awal_keperawatan.destroy');

    // ======================================= #
    //   MODUL RAWAT JALAN / INAP / GD         #
    //   (otomatis dari sub_menu)              #
    // ======================================= #

    // ========= API ROUTES =============
    Route::get('/api/pasien/search', [ApiPasienController::class, 'searchPasien'])->name('api.pasien.search');
    Route::get('/api/wilayah/provinsi', [ApiWilayahController::class, 'provinsi'])->name('api.wilayah.provinsi');
    Route::get('/api/wilayah/kabupaten', [ApiWilayahController::class, 'kabupaten'])->name('api.wilayah.kabupaten');
    Route::get('/api/wilayah/kecamatan', [ApiWilayahController::class, 'kecamatan'])->name('api.wilayah.kecamatan');
    Route::get('/api/wilayah/kelurahan', [ApiWilayahController::class, 'kelurahan'])->name('api.wilayah.kelurahan');
    Route::get('/api/icd/search', [ApiIcdController::class, 'searchIcd'])->name('api.icd.search');
    Route::get('/api/nasabah/search', [ApiNasabahController::class, 'searchNasabah'])->name('api.nasabah.search');
    Route::get('/api/pegawai/search', [ApiPegawaiController::class, 'searchPegawai'])->name('api.pegawai.search');
});
