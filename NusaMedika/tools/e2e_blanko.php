<?php

/**
 * Harness Batch E — Katalog Form Blanko (form 129, tabel sendiri).
 *
 * Alur: admin CRUD (create template → tambah field → edit → publish) lalu
 * alur pengisian pasien (katalog → isi → store → edit → update → cetak →
 * delete) + negatif test validasi & filter adaptif. Membersihkan semua data
 * uji di akhir run (hard delete), sehingga aman dijalankan berulang.
 *
 * Pakai:
 *   docker compose exec -T app php tools/e2e_blanko.php
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['app.debug' => true]);

const RDID = 12; // registrasi_detail dummy (PasienDummySeeder)
const KODE = 'BLNKO-E2E';
const SLUG = 'blanko_uji_e2e';
const NAMA = 'Form Blanko Uji E2E';

$fail = 0;

function cek(string $label, bool $ok, string $info = ''): void
{
    global $fail;
    if (! $ok) {
        $fail++;
    }
    printf("  %-62s %s %s\n", $label, $ok ? 'OK' : 'GAGAL', $info);
}

function sesiBaru(int $userId)
{
    $s = app('session')->driver();
    $s->start();
    $s->invalidate();
    $s->start();
    auth()->loginUsingId($userId);
    $profesi = (int) DB::table('pegawai')
        ->where('pegawai_id', DB::table('users')->where('user_id', $userId)->value('pegawai_id'))
        ->value('profesi_id');
    session(['profesi_id' => $profesi > 0 ? $profesi : 1]);

    return $s;
}

function tembak(HttpKernel $K, $s, int $userId, string $method, string $uri, array $data = [])
{
    if (strtoupper($method) !== 'GET' && ! isset($data['_token'])) {
        $data['_token'] = $s->token();
    }
    $r = Request::create($uri, strtoupper($method), $data);
    $r->headers->set('referer', 'http://localhost/');
    $r->setLaravelSession($s);
    $r->setUserResolver(fn () => DB::table('users')->where('user_id', $userId)->first());
    app()->instance('request', $r);

    return $K->handle($r);
}

/** Bersihkan sisa run sebelumnya (idempoten). */
function bersihkan(?int $fb = null): void
{
    $ids = DB::table('form_blanko')->where('kode_blanko', KODE)->pluck('form_blanko_id');
    if ($fb) {
        $ids->push($fb);
    }
    foreach ($ids->unique() as $id) {
        DB::table('form_blanko_pengisian')->where('form_blanko_id', $id)->delete();
        DB::table('form_blanko_field')->where('form_blanko_id', $id)->delete();
        DB::table('form_blanko')->where('form_blanko_id', $id)->delete();
    }
}

$K = $app->make(HttpKernel::class);

$meta = [
    'kode_blanko' => KODE,
    'slug' => SLUG,
    'nama_blanko' => NAMA,
    'deskripsi' => 'Template uji harness e2e_blanko.',
    'kategori' => 'Uji E2E',
    'departemen' => 'Penunjang',
    'is_aktif' => 1,
    'ukuran_halaman' => 'A4',
    'orientasi_halaman' => 'portrait',
    'tampilkan_identitas_pasien' => 1,
    'tampilkan_logo' => 0,
    'kop_surat' => "RS UJI E2E\nJl. Uji Nomor 1",
    'filter_adaptif' => [
        'kategori_umur' => ['Anak'],
        'jenis_kelamin' => [],
        'jenis_rawat' => ['RJ', 'IGD'],
        'profesi_id' => [1],
    ],
];

$fields = [
    ['kode_field' => 'usia_kategori', 'label' => 'Usia Kategori', 'tipe_kontrol' => 'select', 'grup' => 'Identitas', 'wajib' => 1, 'lebar_kolom' => 6,
        'opsi_text' => "Neonatus|Neonatus\nAnak|Anak\nDewasa|Dewasa\nGeriatri|Geriatri"],
    ['kode_field' => 'catatan_anak', 'label' => 'Catatan Anak', 'tipe_kontrol' => 'textarea', 'grup' => 'Identitas', 'wajib' => 0, 'lebar_kolom' => 6,
        'rows' => 3, 'depends_on_field' => 'usia_kategori', 'depends_on_value' => 'Anak'],
    ['kode_field' => 'tanggal_periksa', 'label' => 'Tanggal Pemeriksaan', 'tipe_kontrol' => 'date', 'grup' => 'Detail', 'wajib' => 1, 'lebar_kolom' => 6],
    ['kode_field' => 'skor', 'label' => 'Skor', 'tipe_kontrol' => 'number', 'grup' => 'Detail', 'wajib' => 0, 'lebar_kolom' => 6, 'min' => 0, 'max' => 100],
    ['kode_field' => 'layanan', 'label' => 'Jenis Layanan', 'tipe_kontrol' => 'select', 'grup' => 'Detail', 'wajib' => 1, 'lebar_kolom' => 6,
        'multiple' => 1, 'opsi_text' => "RJ|Rawat Jalan\nRI|Rawat Inap\nIGD|IGD"],
    ['kode_field' => 'keluhan', 'label' => 'Keluhan', 'tipe_kontrol' => 'checkbox', 'grup' => 'Keluhan', 'wajib' => 1, 'lebar_kolom' => 12,
        'opsi_text' => "Nyeri|Nyeri\nSesak|Sesak\nMual|Mual"],
];

echo "== FASE 1: ADMIN (user 1) ==\n";
bersihkan();
$s1 = sesiBaru(1);

$resp = tembak($K, $s1, 1, 'GET', '/katalog_form_blanko');
cek('GET admin index', $resp->getStatusCode() === 200, 'status='.$resp->getStatusCode());

$resp = tembak($K, $s1, 1, 'GET', '/katalog_form_blanko/create');
cek('GET admin create', $resp->getStatusCode() === 200, 'status='.$resp->getStatusCode());

$resp = tembak($K, $s1, 1, 'POST', '/katalog_form_blanko', $meta);
$fb = (int) DB::table('form_blanko')->where('kode_blanko', KODE)->value('form_blanko_id');
cek('POST admin store template', $resp->getStatusCode() === 302 && $fb > 0,
    'status='.$resp->getStatusCode().' id='.$fb);
if ($fb === 0) {
    echo "GAGAL total: template gagal dibuat — hentikan.\n";
    exit(1);
}

$resp = tembak($K, $s1, 1, 'GET', "/katalog_form_blanko/$fb/edit");
$html = $resp->getContent();
cek('GET admin edit (render builder)', $resp->getStatusCode() === 200 && str_contains($html, 'Opsi (per baris'),
    'status='.$resp->getStatusCode());

$resp = tembak($K, $s1, 1, 'GET', '/katalog_form_blanko');
cek('index menampilkan template baru', str_contains($resp->getContent(), NAMA));

$resp = tembak($K, $s1, 1, 'PUT', "/katalog_form_blanko/$fb", $meta + ['fields' => $fields]);
$jumlahField = (int) DB::table('form_blanko_field')->where('form_blanko_id', $fb)
    ->where(function ($q) { $q->whereNull('status_batal')->orWhere('status_batal', 0); })->count();
cek('PUT admin update (sinkron 6 field)', $resp->getStatusCode() === 302 && $jumlahField === 6,
    'status='.$resp->getStatusCode().' field='.$jumlahField);

$resp = tembak($K, $s1, 1, 'GET', "/katalog_form_blanko/$fb/edit");
$html = $resp->getContent();
cek('edit memuat kembali 6 field (old/normalisasi)',
    $resp->getStatusCode() === 200 && str_contains($html, 'usia_kategori') && str_contains($html, 'keluhan'),
    'status='.$resp->getStatusCode());

$resp = tembak($K, $s1, 1, 'POST', "/katalog_form_blanko/$fb/publish");
$versi = (int) DB::table('form_blanko')->where('form_blanko_id', $fb)->value('versi');
$publishTime = DB::table('form_blanko')->where('form_blanko_id', $fb)->value('publish_time');
cek('POST publish (versi naik + publish_time)',
    $resp->getStatusCode() === 302 && $versi === 2 && $publishTime !== null,
    'status='.$resp->getStatusCode().' versi='.$versi);

echo "\n== FASE 2: ALUR PASIEN (user 3, dokter) ==\n";
$s2 = sesiBaru(3);

$resp = tembak($K, $s2, 3, 'GET', '/emr/katalog-form-blanko/'.RDID);
$html = $resp->getContent();
cek('GET katalog (default: template tampil)',
    $resp->getStatusCode() === 200 && str_contains($html, 'cek129Katalog') && str_contains($html, NAMA),
    'status='.$resp->getStatusCode());

$resp = tembak($K, $s2, 3, 'GET', '/emr/katalog-form-blanko/'.RDID.'?kategori_umur=Geriatri');
cek('filter kategori_umur=Geriatri → template terbuang', ! str_contains($resp->getContent(), NAMA));

$resp = tembak($K, $s2, 3, 'GET', '/emr/katalog-form-blanko/'.RDID.'?profesi_id=2');
cek('filter profesi_id=2 → template terbuang', ! str_contains($resp->getContent(), NAMA));

$resp = tembak($K, $s2, 3, 'GET', '/emr/katalog-form-blanko/'.RDID.'?jenis_rawat=MCU');
cek('filter jenis_rawat=MCU → template terbuang', ! str_contains($resp->getContent(), NAMA));

$resp = tembak($K, $s2, 3, 'GET', '/emr/katalog-form-blanko/'.RDID.'?kategori_umur=Anak&jenis_rawat=RJ&profesi_id=1');
cek('filter cocok → template tampil', str_contains($resp->getContent(), NAMA));

$uriIsi = "/emr/katalog-form-blanko/$fb/".RDID;
$resp = tembak($K, $s2, 3, 'GET', $uriIsi);
$html = $resp->getContent();
cek('GET isi (create, render 12 tipe)',
    $resp->getStatusCode() === 200 && str_contains($html, 'cek129Isi') && str_contains($html, 'Usia Kategori'),
    'status='.$resp->getStatusCode());

$jumlahAwal = (int) DB::table('form_blanko_pengisian')->where('form_blanko_id', $fb)->count();
$resp = tembak($K, $s2, 3, 'POST', $uriIsi, ['data' => [
    'usia_kategori' => 'Anak',
    'catatan_anak' => 'Keluhan uji e2e',
    'tanggal_periksa' => '2026-10-10',
    'skor' => 55,
    'layanan' => ['RJ', 'IGD'],
    'keluhan' => ['Nyeri', 'Mual'],
]]);
$jumlahSesudah = (int) DB::table('form_blanko_pengisian')->where('form_blanko_id', $fb)->count();
cek('POST store (valid → 1 pengisian baru)',
    $resp->getStatusCode() === 302 && $jumlahSesudah === $jumlahAwal + 1,
    'status='.$resp->getStatusCode());

$pid = (int) DB::table('form_blanko_pengisian')->where('form_blanko_id', $fb)->value('form_blanko_pengisian_id');
$row = $pid ? json_decode(DB::table('form_blanko_pengisian')->where('form_blanko_pengisian_id', $pid)->value('data'), true) : [];
cek('data JSON: multi-select & checkbox array + __versi=2',
    ($row['layanan'] ?? null) === ['RJ', 'IGD'] && ($row['keluhan'] ?? null) === ['Nyeri', 'Mual']
    && ($row['__versi'] ?? null) === 2 && ($row['skor'] ?? null) === 55,
    json_encode($row['layanan'] ?? null).' '.__LINE__);

$ringkasan = $pid ? json_decode(DB::table('form_blanko_pengisian')->where('form_blanko_pengisian_id', $pid)->value('ringkasan'), true) : [];
cek('ringkasan ≤ 3 field pertama terisi', count($ringkasan) <= 3 && $ringkasan !== [],
    implode(',', array_keys($ringkasan)));

// Negatif: wajib (tanggal_periksa) tidak dikirim → validasi server menolak.
$resp = tembak($K, $s2, 3, 'POST', $uriIsi, ['data' => [
    'usia_kategori' => 'Anak',
    'catatan_anak' => 'x',
    'layanan' => ['RJ'],
    'keluhan' => ['Nyeri'],
]]);
$statusGagal = $resp->getStatusCode();
$jumlahGagal = (int) DB::table('form_blanko_pengisian')->where('form_blanko_id', $fb)->count();
$resp = tembak($K, $s2, 3, 'GET', $uriIsi); // flash errors ter-render di request berikutnya
cek('negatif: required tanggal_periksa → tolak + pesan',
    $statusGagal === 302 && $jumlahGagal === $jumlahSesudah
    && str_contains($resp->getContent(), 'Kolom Tanggal Pemeriksaan wajib diisi'),
    'status='.$statusGagal);

$resp = tembak($K, $s2, 3, 'GET', "$uriIsi/$pid");
cek('GET edit (prefill nilai tersimpan)',
    $resp->getStatusCode() === 200 && str_contains($resp->getContent(), 'Keluhan uji e2e'),
    'status='.$resp->getStatusCode());

$resp = tembak($K, $s2, 3, 'GET', "$uriIsi/$pid?action=view");
cek('GET mode Lihat',
    $resp->getStatusCode() === 200 && str_contains($resp->getContent(), 'cek129Isi'),
    'status='.$resp->getStatusCode());

// Update: condition TIDAK terpenuhi (Dewasa) → catatan_anak tidak dikirim → hilang dari data.
$resp = tembak($K, $s2, 3, 'PUT', "$uriIsi/$pid", ['data' => [
    'usia_kategori' => 'Dewasa',
    'tanggal_periksa' => '2026-10-11',
    'skor' => 77,
    'layanan' => ['RI'],
    'keluhan' => ['Sesak'],
]]);
$data2 = json_decode(DB::table('form_blanko_pengisian')->where('form_blanko_pengisian_id', $pid)->value('data'), true) ?: [];
cek('PUT update (nilai baru + conditional di-null-kan)',
    $resp->getStatusCode() === 302 && ($data2['skor'] ?? null) === 77
    && ($data2['usia_kategori'] ?? null) === 'Dewasa' && ! array_key_exists('catatan_anak', $data2)
    && ($data2['keluhan'] ?? null) === ['Sesak'],
    'status='.$resp->getStatusCode());

$resp = tembak($K, $s2, 3, 'GET', "/emr/katalog-form-blanko/print/$fb/".RDID."/$pid");
cek('GET cetak pengisian (§9.2)',
    $resp->getStatusCode() === 200 && str_contains($resp->getContent(), 'Tanggal Pemeriksaan')
    && str_contains($resp->getContent(), '2026-10-11'),
    'status='.$resp->getStatusCode());

$resp = tembak($K, $s2, 3, 'GET', "/emr/katalog-form-blanko/cetak/$fb");
cek('GET cetak blanko kosong (§9.1)',
    $resp->getStatusCode() === 200 && str_contains($resp->getContent(), NAMA),
    'status='.$resp->getStatusCode());

$resp = tembak($K, $s2, 3, 'GET', "$uriIsi/$pid");
$html = $resp->getContent();
cek('tabel riwayat menampilkan pengisian',
    $resp->getStatusCode() === 200 && str_contains($html, 'Riwayat Pengisian')
    && str_contains($html, 'Data Uji') === false && preg_match('/Riwayat Pengisian.*Sesak/s', $html),
    'status='.$resp->getStatusCode());

$resp = tembak($K, $s2, 3, 'DELETE', "$uriIsi/$pid");
$batal = DB::table('form_blanko_pengisian')->where('form_blanko_pengisian_id', $pid)->value('status_batal');
cek('DELETE destroy (soft delete)', $resp->getStatusCode() === 302 && (int) $batal === 1,
    'status='.$resp->getStatusCode());

echo "\n== FASE 3: ADMIN destroy + BERSIHKAN ==\n";
// Store session satu instance untuk seluruh proses — fase 2 sudah berpindah
// ke user 3, jadi login ulang sebagai admin sebelum rute Administrator.
$s1 = sesiBaru(1);
$resp = tembak($K, $s1, 1, 'DELETE', "/katalog_form_blanko/$fb");
$fbBatal = DB::table('form_blanko')->where('form_blanko_id', $fb)->value('status_batal');
cek('DELETE admin template (cascade soft delete)', $resp->getStatusCode() === 302 && (int) $fbBatal === 1,
    'status='.$resp->getStatusCode());

bersihkan($fb);
$sisa = DB::table('form_blanko')->where('kode_blanko', KODE)->count()
    + DB::table('form_blanko_field')->where('form_blanko_id', $fb)->count()
    + DB::table('form_blanko_pengisian')->where('form_blanko_id', $fb)->count();
cek('cleanup total (data uji hilang)', $sisa === 0, 'sisa='.$sisa);

echo $fail ? "GAGAL: $fail langkah\n" : "ALL OK\n";
exit($fail ? 1 : 0);
