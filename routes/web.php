<?php

use App\Http\Controllers\Administrator\ManajemenMaster\Bagian\BagianController;
use App\Http\Controllers\Administrator\ManajemenMaster\Icd\IcdController;
use App\Http\Controllers\Administrator\ManajemenMaster\Jabatan\JabatanController;
use App\Http\Controllers\Administrator\ManajemenMaster\JadwalDokter\JadwalDokterController;
use App\Http\Controllers\Administrator\ManajemenMaster\Kelas\KelasController;
use App\Http\Controllers\Administrator\ManajemenMaster\Menu\MenuController;
use App\Http\Controllers\Administrator\ManajemenMaster\Modul\ModulController;
use App\Http\Controllers\Administrator\ManajemenMaster\Nasabah\NasabahController;
use App\Http\Controllers\Administrator\ManajemenMaster\Pegawai\PegawaiController;
use App\Http\Controllers\Administrator\ManajemenMaster\Profesi\ProfesiController;
use App\Http\Controllers\Administrator\ManajemenMaster\ReferensiBagian\ReferensiBagianController;
use App\Http\Controllers\Administrator\ManajemenMaster\SubMenu\SubMenuController;
use App\Http\Controllers\Administrator\ManajemenMaster\Wilayah\WilayahController;
use App\Http\Controllers\Administrator\ManajemenUser\User\UserController;
use App\Http\Controllers\API\ApiPasienController;
use App\Http\Controllers\API\ApiWilayahController;
use App\Http\Controllers\API\ApiIcdController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\EMR\DynamicFormController;
use App\Http\Controllers\EMR\EmrDashboard\EmrDashboardController;
use App\Http\Controllers\EMR\PengkajianAwalKeperawatan\PengkajianAwalKeperawatanController;
use App\Http\Controllers\EMR\Soap\SoapController;
use App\Http\Controllers\GawatDarurat\Pasien\ListPasienIGD\ListPasienIGDController;
use App\Http\Controllers\RawatInap\Pasien\ListPasien\ListPasienRanapController;
use App\Http\Controllers\RawatJalan\Pasien\ListPasien\ListPasienRajalController;
use App\Http\Controllers\Registrasi\Pasien\DataPasien\DataPasienController;
use App\Http\Controllers\Registrasi\Pasien\NasabahPasien\NasabahPasienController;
use App\Http\Controllers\Registrasi\Pendaftaran\DaftarIGD\DaftarIGDController;
use App\Http\Controllers\Registrasi\Pendaftaran\DaftarIGDObgyn\DaftarIGDObgynController;
use App\Http\Controllers\Registrasi\Pendaftaran\DaftarRajal\DaftarRajalController;
use App\Http\Controllers\Registrasi\Pendaftaran\DaftarRanap\DaftarRanapController;
use App\Http\Controllers\Registrasi\Pendaftaran\ListPelayanan\ListPelayananController;
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

    // ======================================= #
    //             MODUL REGISTRASI            #
    // ======================================= #
    // ========= MENU PASIEN ================== #
    // GET
    Route::get('/daftar_pasien', [DataPasienController::class, 'index'])->name('daftar_pasien.index');
    Route::get('/daftar_pasien/create', [DataPasienController::class, 'create'])->name('daftar_pasien.create');
    Route::get('/daftar_pasien/{id}/edit', [DataPasienController::class, 'edit'])->name('daftar_pasien.edit');
    // POST
    Route::post('/daftar_pasien', [DataPasienController::class, 'store'])->name('daftar_pasien.store');
    // PUT
    Route::put('/daftar_pasien/{id}', [DataPasienController::class, 'update'])->name('daftar_pasien.update');
    // DELETE
    Route::delete('/daftar_pasien/{id}', [DataPasienController::class, 'destroy'])->name('daftar_pasien.destroy');
    Route::get('/nasabah_pasien', [NasabahPasienController::class, 'index'])->name('nasabah_pasien.index');
    Route::get('/nasabah_pasien/create', [NasabahPasienController::class, 'create'])->name('nasabah_pasien.create');
    Route::get('/nasabah_pasien/{id}/edit', [NasabahPasienController::class, 'edit'])->name('nasabah_pasien.edit');
    Route::post('/nasabah_pasien', [NasabahPasienController::class, 'store'])->name('nasabah_pasien.store');
    Route::put('/nasabah_pasien/{id}', [NasabahPasienController::class, 'update'])->name('nasabah_pasien.update');
    Route::delete('/nasabah_pasien/{id}', [NasabahPasienController::class, 'destroy'])->name('nasabah_pasien.destroy');

    // ========= MENU PENDAFTARAN ============= #
    // GET
    Route::get('/list_pelayanan_pasien', [ListPelayananController::class, 'index'])->name('list_pelayanan_pasien.index');
    Route::get('/daftar_rajal', [DaftarRajalController::class, 'index'])->name('daftar_rajal.index');
    Route::post('/daftar_rajal', [DaftarRajalController::class, 'store'])->name('daftar_rajal.store');
    Route::get('/daftar_ranap', [DaftarRanapController::class, 'index'])->name('daftar_ranap.index');
    Route::get('/registrasi_igd', [DaftarIGDController::class, 'index'])->name('registrasi_igd.index');
    Route::get('/registrasi_igd_obgyn', [DaftarIGDObgynController::class, 'index'])->name('registrasi_igd_obgyn.index');
    // DELETE
    Route::delete('/list_pelayanan_pasien/{id}', [ListPelayananController::class, 'destroy'])->name('list_pelayanan_pasien.destroy');

    // ======================================= #
    //             MODUL EMR                   #
    // ======================================= #
    // ============ DASHBOARD PASIEN ========= #
    // GET
    Route::get('/dashboard_pasien/{registrasi_detail_id}', [EmrDashboardController::class, 'index'])->name('dashboard_pasien.index');

    // ============ DYNAMIC EMR ROUTE ======== #
    Route::get('/emr/form/{form_name}/{registrasi_detail_id}/{emr_id?}', [DynamicFormController::class, 'index'])->name('emr.dynamic.index');

    // ============ SOAP (CPPT) ============== #
    Route::get('/emr/unsupported', function () {
        return view('moduls.emr.unsupported');
    })->name('emr.unsupported');

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
    //             MODUL RAWAT JALAN           #
    // ======================================= #
    // ========== MENU PASIEN ================ #
    // GET
    Route::get('/list_pasien_dokter', [ListPasienRajalController::class, 'index'])->name('list_pasien_dokter.index');

    // ======================================= #
    //             MODUL RAWAT INAP            #
    // ======================================= #
    // ========== MENU PASIEN ================ #
    Route::get('/list_pasien_ranap', [ListPasienRanapController::class, 'index'])->name('list_pasien_ranap.index');

    // ======================================= #
    //             MODUL GAWAT DARURAT         #
    // ======================================= #
    // ========== MENU PASIEN ================ #
    Route::get('/list_pasien_igd', [ListPasienIGDController::class, 'index'])->name('list_pasien_igd.index');

    // ======================================= #
    //           MODUL ADMINISTRATOR           #
    // ======================================= #
    // ========== MANAJEMEN MASTER =========== #
    Route::resource('modul', ModulController::class)->names('admin.modul');
    Route::resource('menu', MenuController::class)->names('admin.menu');
    Route::resource('sub_menu', SubMenuController::class)->names('admin.sub_menu');
    Route::resource('bagian', BagianController::class)->names('admin.bagian');
    Route::resource('referensi_bagian_id', ReferensiBagianController::class)->names('admin.referensi_bagian_id');
    Route::resource('profesi', ProfesiController::class)->names('admin.profesi');
    Route::resource('jabatan', JabatanController::class)->names('admin.jabatan');
    Route::resource('pegawai', PegawaiController::class)->names('admin.pegawai');
    Route::resource('wilayah', WilayahController::class)->names('admin.wilayah');
    Route::resource('nasabah', NasabahController::class)->names('admin.nasabah');
    Route::resource('kelas', KelasController::class)->names('admin.kelas');
    Route::resource('jadwal_dokter', JadwalDokterController::class)->names('admin.jadwal_dokter');
    Route::resource('icd', IcdController::class)->names('admin.icd');
    // ========== MANAJEMEN USER ============= #
    Route::resource('user', UserController::class)->names('admin.user');

    // ========= API ROUTES =============
    Route::get('/api/pasien/search', [ApiPasienController::class, 'searchPasien'])->name('api.pasien.search');
    Route::get('/api/wilayah/provinsi', [ApiWilayahController::class, 'provinsi'])->name('api.wilayah.provinsi');
    Route::get('/api/wilayah/kabupaten', [ApiWilayahController::class, 'kabupaten'])->name('api.wilayah.kabupaten');
    Route::get('/api/wilayah/kecamatan', [ApiWilayahController::class, 'kecamatan'])->name('api.wilayah.kecamatan');
    Route::get('/api/wilayah/kelurahan', [ApiWilayahController::class, 'kelurahan'])->name('api.wilayah.kelurahan');
    Route::get('/api/icd/search', [ApiIcdController::class, 'searchIcd'])->name('api.icd.search');
});
Route::get('/api/test/pasien/search', [ApiPasienController::class, 'searchPasien']);
