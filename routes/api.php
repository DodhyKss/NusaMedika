<?php

use App\Http\Controllers\API\ApiIcdController;
use App\Http\Controllers\API\ApiNasabahController;
use App\Http\Controllers\API\ApiPasienController;
use App\Http\Controllers\API\ApiPegawaiController;
use App\Http\Controllers\API\ApiWilayahController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('/pasien/search', [ApiPasienController::class, 'searchPasien'])->name('api.pasien.search');
    Route::get('/wilayah/provinsi', [ApiWilayahController::class, 'provinsi'])->name('api.wilayah.provinsi');
    Route::get('/wilayah/kabupaten', [ApiWilayahController::class, 'kabupaten'])->name('api.wilayah.kabupaten');
    Route::get('/wilayah/kecamatan', [ApiWilayahController::class, 'kecamatan'])->name('api.wilayah.kecamatan');
    Route::get('/wilayah/kelurahan', [ApiWilayahController::class, 'kelurahan'])->name('api.wilayah.kelurahan');
    Route::get('/icd/search', [ApiIcdController::class, 'searchIcd'])->name('api.icd.search');
    Route::get('/nasabah/search', [ApiNasabahController::class, 'searchNasabah'])->name('api.nasabah.search');
    Route::get('/pegawai/search', [ApiPegawaiController::class, 'searchPegawai'])->name('api.pegawai.search');
});
