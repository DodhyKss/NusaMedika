<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\EMR\DynamicFormController;
use App\Http\Controllers\EMR\EmrDashboard\EmrDashboardController;
use App\Http\Controllers\EMR\Konsultasi\KonsultasiController;
use App\Http\Controllers\EMR\Soap\SoapController;
use App\Http\Controllers\Farmasi\Resep\ListPesananResep\ListPesananResepController;
use App\Http\Controllers\Inventory\Pesanan\BuatPesanan\BuatPesananController;
use App\Http\Controllers\Inventory\Pesanan\SetujuiPesanan\SetujuiPesananController;
use App\Http\Controllers\PenunjangMedis\Laboratorium\DaftarPesananLaboratorium\DaftarPesananLaboratoriumController;
use App\Http\Controllers\PenunjangMedis\Radiologi\DaftarPesananRadiologi\DaftarPesananRadiologiController;
use App\Http\Controllers\PenunjangMedis\RehabilitasiMedik\DaftarPasienRehabilitasiMedik\DaftarPasienRehabilitasiMedikController;
use Illuminate\Support\Facades\Route;

// ================ DESKRIPSI ============== #
/*
ROUTE PAGE DAN CRUD TIDAK PERLU DIDAFTARKAN DI SINI, KARENA ROUTE PAGE AKAN OTOMATIS TERDAFTAR SESUAI DENGAN NAMA FOLDERNYA, KECUALI JIKA ADA ROUTE CUSTOM.
BEGITUPUN DENGAN ROUTE EMR, ROUTE AKAN OTOMATIS TERDAFTAR SESUAI DENGAN NAMA FORMNYA.
*/

// ================ ROUTE DEFAULT ============== #
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// ================ ROUTE AUTHENTICATION ============== #
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    // ============ DYNAMIC EMR ROUTE ======== #
    // TIDAK PERLU DIUBAH-UBAH INI PATENN YAA  #
    // ======================================= #
    Route::get('/dashboard_pasien/{registrasi_detail_id}', [EmrDashboardController::class, 'index'])->name('dashboard_pasien.index');
    Route::get('/emr/form/{form_name}/{registrasi_detail_id}/{emr_id?}', [DynamicFormController::class, 'index'])->name('emr.dynamic.index');
    Route::post('/emr/form-store/{form_name}/{registrasi_detail_id}', [DynamicFormController::class, 'store'])->name('emr.form.store');
    Route::put('/emr/form-update/{form_name}/{registrasi_detail_id}/{emr_id}', [DynamicFormController::class, 'update'])->name('emr.form.update');
    Route::delete('/emr/form-delete/{form_name}/{registrasi_detail_id}/{emr_id}', [DynamicFormController::class, 'destroy'])->name('emr.form.destroy');

    // ============ ROUTE CUSTOM ============== #
    // DI SINI KALAU MAU TAMBAH ROUTE CUSTOM    #
    // ======================================== #
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ============ ROUTE SOAP ================= #
    Route::get('/emr/soap/print/{emr_id}', [SoapController::class, 'print'])->name('emr.soap.print');

    // ============ ROUTE KONSULTASI (EMR/Cetak) ============= #
    // Aksi non-CRUD konsultasi (cetak slip/rencana kontrol) didaftarkan manual.
    Route::get('/emr/konsultasi/print/{emr_id}', [KonsultasiController::class, 'print'])->name('emr.konsultasi.print');

    // ============ ROUTE INVENTORY ============= #
    // Aksi non-CRUD pemesanan (setujui/tolak/batal) didaftarkan manual agar reusable.
    Route::post('/pemesanan/{pemesanan}/setujui', [SetujuiPesananController::class, 'setujui'])->name('pemesanan.setujui');
    Route::post('/pemesanan/{pemesanan}/tolak', [SetujuiPesananController::class, 'tolak'])->name('pemesanan.tolak');
    Route::post('/pemesanan/{pemesanan}/batal', [BuatPesananController::class, 'batal'])->name('pemesanan.batal');

    // ============ ROUTE FARMASI ============= #
    // Aksi non-CRUD peresepan (pilih depo / dispense / batal) didaftarkan manual.
    Route::post('/list_pesanan_resep/depo', [ListPesananResepController::class, 'pilihDepo'])->name('list_pesanan_resep.depo');
    Route::get('/list_pesanan_resep/{peresepan_obat}', [ListPesananResepController::class, 'detail'])->name('list_pesanan_resep.detail');
    Route::get('/list_pesanan_resep/{peresepan_obat}/cetak-tiket', [ListPesananResepController::class, 'cetakTiket'])->name('list_pesanan_resep.cetak_tiket');
    Route::get('/list_pesanan_resep/{peresepan_obat}/cetak-detail', [ListPesananResepController::class, 'cetakDetail'])->name('list_pesanan_resep.cetak_detail');
    Route::get('/list_pesanan_resep/{peresepan_obat}/dispense', [ListPesananResepController::class, 'dispense'])->name('list_pesanan_resep.dispense');
    Route::post('/list_pesanan_resep/{peresepan_obat}/dispense', [ListPesananResepController::class, 'dispenseStore'])->name('list_pesanan_resep.dispense_store');
    Route::post('/list_pesanan_resep/{peresepan_obat}/batal', [ListPesananResepController::class, 'batal'])->name('list_pesanan_resep.batal');

    // ============ ROUTE PENUNJANG MEDIS ============= #
    // Aksi non-CRUD order laboratorium/radiologi (pilih bagian / terima / simpan hasil / selesai / batal / cetak)
    // didaftarkan manual agar reusable & tidak terikat pola CRUD sub_menu.
    // Laboratorium.
    Route::post('/daftar_pesanan_laboratorium/pilih-bagian', [DaftarPesananLaboratoriumController::class, 'pilihBagian'])->name('daftar_pesanan_laboratorium.pilih_bagian');
    Route::get('/daftar_pesanan_laboratorium/{order_laboratorium}', [DaftarPesananLaboratoriumController::class, 'detail'])->name('daftar_pesanan_laboratorium.detail');
    Route::post('/order_laboratorium/{order_laboratorium}/terima', [DaftarPesananLaboratoriumController::class, 'terima'])->name('order_laboratorium.terima');
    Route::post('/order_laboratorium/{order_laboratorium}/simpan-hasil', [DaftarPesananLaboratoriumController::class, 'simpanHasil'])->name('order_laboratorium.simpan_hasil');
    Route::post('/order_laboratorium/{order_laboratorium}/selesai', [DaftarPesananLaboratoriumController::class, 'selesai'])->name('order_laboratorium.selesai');
    Route::post('/order_laboratorium/{order_laboratorium}/batal', [DaftarPesananLaboratoriumController::class, 'batal'])->name('order_laboratorium.batal');
    Route::get('/order_laboratorium/{order_laboratorium}/cetak', [DaftarPesananLaboratoriumController::class, 'cetak'])->name('order_laboratorium.cetak');
    // Radiologi.
    Route::post('/daftar_pesanan_radiologi/pilih-bagian', [DaftarPesananRadiologiController::class, 'pilihBagian'])->name('daftar_pesanan_radiologi.pilih_bagian');
    Route::get('/daftar_pesanan_radiologi/{order_radiologi}', [DaftarPesananRadiologiController::class, 'detail'])->name('daftar_pesanan_radiologi.detail');
    Route::post('/order_radiologi/{order_radiologi}/terima', [DaftarPesananRadiologiController::class, 'terima'])->name('order_radiologi.terima');
    Route::post('/order_radiologi/{order_radiologi}/simpan-hasil', [DaftarPesananRadiologiController::class, 'simpanHasil'])->name('order_radiologi.simpan_hasil');
    Route::post('/order_radiologi/{order_radiologi}/selesai', [DaftarPesananRadiologiController::class, 'selesai'])->name('order_radiologi.selesai');
    Route::post('/order_radiologi/{order_radiologi}/batal', [DaftarPesananRadiologiController::class, 'batal'])->name('order_radiologi.batal');
    Route::get('/order_radiologi/{order_radiologi}/cetak', [DaftarPesananRadiologiController::class, 'cetak'])->name('order_radiologi.cetak');

    // Rehabilitasi Medik — pilih bagian rehab (session) & detail pasien (bukan CRUD, didaftarkan manual).
    Route::post('/daftar_pasien_rehabilitasi_medik/pilih-bagian', [DaftarPasienRehabilitasiMedikController::class, 'pilihBagian'])->name('daftar_pasien_rehabilitasi_medik.pilih_bagian');
    Route::get('/daftar_pasien_rehabilitasi_medik/detail/{registrasi_detail}', [DaftarPasienRehabilitasiMedikController::class, 'detail'])->name('daftar_pasien_rehabilitasi_medik.detail');
});
