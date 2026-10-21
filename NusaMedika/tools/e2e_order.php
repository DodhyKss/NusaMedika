<?php

/**
 * Verifikasi order penunjang (form 49, 50, 51).
 *
 * Form ini TIDAK bisa diuji dengan tools/e2e.php karena datanya disimpan di
 * tabel `order_*` (bukan emr_detail) dan membutuhkan item baris yang dipilih
 * lewat master Group Tindakan. Script ini memanggil alur yang sama dengan
 * controller: PenunjangHelper::buatOrder() -> ubahOrder() -> batalkan().
 *
 * Jalankan: docker compose exec -T app php tools/e2e_order.php
 */
use App\Helpers\PenunjangHelper;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

require '/var/www/html/vendor/autoload.php';

$app = require '/var/www/html/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// User peminta: ambil user yang punya pegawai.
$user = DB::table('users')->whereNotNull('pegawai_id')->first();
if (! $user) {
    fwrite(STDERR, "Tidak ada user dengan pegawai_id.\n");
    exit(1);
}
Auth::loginUsingId($user->user_id);
session(['profesi_id' => 1]);

$registrasiDetail = DB::table('registrasi_detail')->orderBy('registrasi_detail_id')->first();
if (! $registrasiDetail) {
    fwrite(STDERR, "Tidak ada registrasi_detail.\n");
    exit(1);
}

// Satu tindakan dari group pertama (dibutuhkan buat item order).
$grup = DB::table('group_tindakan_tindakan')
    ->where(function ($q) {
        $q->whereNull('status_batal')->orWhere('status_batal', 0);
    })
    ->orderBy('group_tindakan_id')->first();

if (! $grup) {
    fwrite(STDERR, "Tidak ada anggota group_tindakan_tindakan.\n");
    exit(1);
}

$kasir = [
    'patologi' => 'Order Patologi Anatomi',
    'mikrobiologi' => 'Order Mikrobiologi',
    'poct' => 'Order POCT',
];

$fail = 0;
$ok = 0;

foreach ($kasir as $jenis => $nama) {
    try {
        $orderId = PenunjangHelper::buatOrder($jenis, [
            'registrasi_detail_id' => (int) $registrasiDetail->registrasi_detail_id,
            'bagian_asal_id' => (int) $registrasiDetail->bagian_id,
            'bagian_tujuan_id' => (int) $grup->group_tindakan_id,
            'dokter_id' => (int) $user->pegawai_id,
            'prioritas' => 'BIASA',
            'keterangan' => 'Uji '.$nama,
        ], [
            ['tindakan_id' => (int) $grup->tindakan_id, 'spesimen' => 'Biopsi'],
        ]);

        // PK & FK mengikuti konvensi tabel: order_{jenis}_id / order_{jenis}_id.
        $pk = 'order_'.$jenis.'_id';
        $header = DB::table('order_'.$jenis)->where($pk, $orderId)->first();
        $detail = DB::table('order_'.$jenis.'_detail')->where($pk, $orderId)->count();

        // Uji ubahOrder: ganti item.
        PenunjangHelper::ubahOrder($jenis, $orderId, [
            'registrasi_detail_id' => (int) $registrasiDetail->registrasi_detail_id,
            'bagian_asal_id' => (int) $registrasiDetail->bagian_id,
            'bagian_tujuan_id' => (int) $grup->group_tindakan_id,
            'prioritas' => 'CITO',
            'keterangan' => 'Uji diubah '.$nama,
        ], [['tindakan_id' => (int) $grup->tindakan_id, 'spesimen' => 'Sitologi']]);

        $setelahUbah = DB::table('order_'.$jenis)->where($pk, $orderId)->first();

        // Uji batalkan.
        PenunjangHelper::batalkan($jenis, $orderId);
        $setelahBatal = DB::table('order_'.$jenis)->where($pk, $orderId)->value('status_order');

        $bagus = $header
            && $header->no_order !== ''
            && $detail >= 1
            && $setelahUbah->prioritas === 'CITO'
            && (int) $setelahBatal === 3;

        printf("  %-30s order=%d detail=%d ubah=%s batal=%s %s\n",
            $nama, $orderId, $detail, $setelahUbah->prioritas,
            $setelahBatal, $bagus ? 'OK' : '<<< PERIKSA');

        if ($bagus) {
            $ok++;
        } else {
            $fail++;
        }
    } catch (Throwable $e) {
        printf("  %-30s EXC %s: %s\n", $nama, get_class($e), $e->getMessage());
        $fail++;
    }
}

// Bersihkan data uji.
DB::table('order_patologi_detail')->delete();
DB::table('order_patologi')->delete();
DB::table('order_mikrobiologi_detail')->delete();
DB::table('order_mikrobiologi')->delete();
DB::table('order_poct_detail')->delete();
DB::table('order_poct')->delete();

echo "\n".($fail === 0 ? "ALL OK — $ok jenis order\n" : "FAILURES: $fail\n");
exit($fail === 0 ? 0 : 1);
