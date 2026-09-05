<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\EMR\DynamicFormController;
use App\Http\Controllers\EMR\EmrDashboard\EmrDashboardController;
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

    // ============ DYNAMIC EMR ROUTE ======== #
    Route::get('/dashboard_pasien/{registrasi_detail_id}', [EmrDashboardController::class, 'index'])->name('dashboard_pasien.index');
    Route::get('/emr/form/{form_name}/{registrasi_detail_id}/{emr_id?}', [DynamicFormController::class, 'index'])->name('emr.dynamic.index');
    Route::post('/emr/form-store/{form_name}/{registrasi_detail_id}', [DynamicFormController::class, 'store'])->name('emr.form.store');
    Route::put('/emr/form-update/{form_name}/{registrasi_detail_id}/{emr_id}', [DynamicFormController::class, 'update'])->name('emr.form.update');
    Route::delete('/emr/form-delete/{form_name}/{registrasi_detail_id}/{emr_id}', [DynamicFormController::class, 'destroy'])->name('emr.form.destroy');

    // ============ SOAP (CPPT) ============== #
    Route::get('/emr/soap/print/{emr_id}', [SoapController::class, 'print'])->name('emr.soap.print');
});
