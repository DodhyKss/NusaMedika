<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\EMR\DynamicFormController;
use App\Http\Controllers\EMR\EmrDashboard\EmrDashboardController;
use App\Http\Controllers\EMR\Soap\SoapController;
use Illuminate\Support\Facades\Route;

# ================ DESKRIPSI ============== #
/*
ROUTE PAGE DAN CRUD TIDAK PERLU DIDAFTARKAN DI SINI, KARENA ROUTE PAGE AKAN OTOMATIS TERDAFTAR SESUAI DENGAN NAMA FOLDERNYA, KECUALI JIKA ADA ROUTE CUSTOM.
BEGITUPUN DENGAN ROUTE EMR, ROUTE AKAN OTOMATIS TERDAFTAR SESUAI DENGAN NAMA FORMNYA.
*/

# ================ ROUTE DEFAULT ============== #
Route::get('/', function () { return redirect()->route('dashboard'); });

# ================ ROUTE AUTHENTICATION ============== #
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    # ============ DYNAMIC EMR ROUTE ======== #
    # TIDAK PERLU DIUBAH-UBAH INI PATENN YAA  #
    # ======================================= #
    Route::get('/dashboard_pasien/{registrasi_detail_id}', [EmrDashboardController::class, 'index'])->name('dashboard_pasien.index');
    Route::get('/emr/form/{form_name}/{registrasi_detail_id}/{emr_id?}', [DynamicFormController::class, 'index'])->name('emr.dynamic.index');
    Route::post('/emr/form-store/{form_name}/{registrasi_detail_id}', [DynamicFormController::class, 'store'])->name('emr.form.store');
    Route::put('/emr/form-update/{form_name}/{registrasi_detail_id}/{emr_id}', [DynamicFormController::class, 'update'])->name('emr.form.update');
    Route::delete('/emr/form-delete/{form_name}/{registrasi_detail_id}/{emr_id}', [DynamicFormController::class, 'destroy'])->name('emr.form.destroy');

    # ============ ROUTE CUSTOM ============== #
    # DI SINI KALAU MAU TAMBAH ROUTE CUSTOM    #
    # ======================================== #
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    # ============ ROUTE SOAP ================= #
    Route::get('/emr/soap/print/{emr_id}', [SoapController::class, 'print'])->name('emr.soap.print');
});
