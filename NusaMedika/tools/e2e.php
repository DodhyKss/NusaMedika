<?php

/**
 * E2E checker EMR. Pakai:  docker compose exec -T app php e2e.php 22,23,29
 *Format: form_id1,form_id2,...   (kosongkan utk cek semua form yang punya controller)
 */
use App\Helpers\EmrHelper;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require __DIR__.'/lib_pay.php';
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['app.debug' => true]);

function profOf($u)
{
    return (int) DB::table('pegawai')->where('pegawai_id', DB::table('users')->where('user_id', $u)->value('pegawai_id'))->value('profesi_id');
}
function mk($m, $u, $d = [], $uId = 3)
{
    $s = app('session')->driver();
    $s->start();
    $s->invalidate();
    $s->start();
    auth()->loginUsingId($uId);
    session(['profesi_id' => profOf($uId)]);
    if (! in_array($m, ['GET', 'HEAD'])) {
        $d['_token'] = $s->token();
    }
    $r = Request::create($u, $m, $d);
    $r->setLaravelSession($s);
    $r->setUserResolver(fn () => DB::table('users')->where('user_id', $uId)->first());
    app()->instance('request', $r);

    return $r;
}

$argv = array_slice($argv ?? [], 1);
$ids = [];
if ($argv && $argv[0] !== '') {
    $ids = array_map('intval', explode(',', $argv[0]));
} else {
    foreach (DB::table('form')->where('status_batal', '!=', 1)->pluck('form_id') as $f) {
        $slug = DB::table('form')->where('form_id', $f)->value('slug');
        if (class_exists('App\\Http\\Controllers\\EMR\\'.Str::studly($slug).'\\'.Str::studly($slug).'Controller')) {
            $ids[] = (int) $f;
        }
    }
}
$acc = [];
foreach (DB::table('akses_ehr')->where('status_batal', '!=', 1)->get(['profesi_id', 'form_id', 'akses_create', 'akses_update', 'akses_delete']) as $a) {
    $acc[$a->form_id][$a->profesi_id] = [(int) $a->akses_create, (int) $a->akses_update, (int) $a->akses_delete];
}

$K = $app->make(Illuminate\Contracts\Http\Kernel::class);
$fail = 0;
$okN = 0;
$totalVar = 0;
foreach ($ids as $fid) {
    $slug = DB::table('form')->where('form_id', $fid)->value('slug');
    // Form 129 Katalog Form Blanko menyimpan data di tabel sendiri
    // (form_blanko*) — bukan emr/emr_detail. Diverifikasi tools/e2e_blanko.php.
    if ($slug === 'katalog_form_blanko') {
        printf("  %-30s DILEWATI (tabel sendiri form_blanko*, bukan emr_detail)\n", $slug);

        continue;
    }
    $studly = Str::studly($slug);
    $cls = "App\\Http\\Controllers\\EMR\\$studly\\{$studly}Controller";
    if (! class_exists($cls)) {
        printf("  %-30s MISSING controller\n", $slug);
        $fail++;

        continue;
    }
    $cu = $uu = $du = null;
    foreach ([3, 2, 7, 9, 10, 5, 13] as $c) {
        $pr = profOf($c);
        if ($acc[$fid][$pr][0] ?? 0) {
            $cu = $c;
            if (! $uu && ($acc[$fid][$pr][1] ?? 0)) {
                $uu = $c;
            } if (! $du && ($acc[$fid][$pr][2] ?? 0)) {
                $du = $c;
            }
        }
    }
    if (! $cu) {
        printf("  %-30s tidak ada profesi dengan akses create\n", $slug);

        continue;
    }
    $vars = EmrHelper::objekVariabels($fid);
    $totalVar += count($vars);
    $p = buildPayload($cls, $fid);
    DB::table('emr_detail')->whereIn('emr_id', function ($q) use ($fid) {
        $q->select('emr_id')->from('emr')->where('form_id', $fid);
    })->delete();
    DB::table('emr')->where('form_id', $fid)->delete();

    // Kesimpulan MCU punya soft-gate: store ditolak bila BELUM ADA satupun
    // form organ yang terisi pada kunjungan yang sama. Sebelum memanggil
    // store, tanam satu EMR organ minimal (bedah_mcu, 5 variabel) lalu
    // biarkan pembersihan akhir run menghapusnya bersama data uji lain.
    if ($slug === 'kesimpulan_mcu') {
        // Tanam satu form organ yang variabel ringkasnya benar-benar bisa terisi
        // (McuHelper::ringkasan memakai status_gizi & risiko_gizi untuk gizi_mcu).
        $organId = DB::table('form')->where('slug', 'gizi_mcu')->value('form_id');
        if ($organId) {
            $organVars = EmrHelper::objekVariabels((int) $organId);
            $organPayload = buildPayload("App\\Http\\Controllers\\EMR\\GiziMcu\\GiziMcuController", (int) $organId);
            EmrHelper::insert((int) $organId, array_intersect_key($organPayload, array_flip($organVars)), rdId());
        }
    }
    auth()->loginUsingId($cu);
    session(['profesi_id' => profOf($cu)]);
    $rq = Request::create('/x', 'POST', $p);
    $rq->setLaravelSession(app('session')->driver());
    $rq->setUserResolver(fn () => DB::table('users')->where('user_id', $cu)->first());
    app()->instance('request', $rq);
    $ok = false;
    $pend = [];
    try {
        (new $cls)->store($rq, rdId());
        $ok = true;
    } catch (ValidationException $e) {
        $pend = $e->errors();
    } catch (Throwable $e) {
        echo "  EXC $slug: ".get_class($e).': '.$e->getMessage()."\n";
        $fail++;
    }
    if (! $ok) {
        printf("  %-30s VALIDASI %d: %s\n", $slug, count($pend), json_encode(array_slice($pend, 0, 2)));

        continue;
    }
    $id = DB::table('emr')->where('form_id', $fid)->where(function ($q) {
        $q->whereNull('status_batal')->orWhere('status_batal', 0);
    })->orderByDesc('emr_id')->value('emr_id');
    $saved = DB::table('emr_detail')->where('emr_id', $id)->where(function ($q) {
        $q->whereNull('status_batal')->orWhere('status_batal', 0);
    })->pluck('value', 'variabel');
    $miss = array_values(array_filter($vars, fn ($v) => ! $saved->has($v)));
    $g = $K->handle(mk('GET', "/emr/form/$slug/".rdId()."/$id?action=view", [], $cu))->getStatusCode();
    $uR = $uu ? $K->handle(mk('PUT', "/emr/form-update/$slug/".rdId()."/$id", $p, $uu))->getStatusCode() : 'append-only';
    $dR = $du ? $K->handle(mk('DELETE', "/emr/form-delete/$slug/".rdId()."/$id", [], $du))->getStatusCode() : 'append-only';
    $good = ($saved->count() === count($vars)) && $g === 200 && ($uR === 'append-only' || $uR === 302) && ($dR === 'append-only' || $dR === 302);
    printf("  %-30s simpan=%d/%d index=%d upd=%s del=%s %s\n", $slug, $saved->count(), count($vars), $g, $uR, $dR, $good ? 'OK' : '<<< PERIKSA');
    if (! $good) {
        $fail++;
        if ($miss) {
            echo '      tidak tersimpan: '.implode(', ', $miss)."\n";
        }
    } else {
        $okN++;
    }
}
echo "\n".($fail === 0 ? "ALL OK — $okN form, $totalVar variabel\n" : "FAILURES: $fail\n");

// Bersihkan seluruh data uji yang dibuat harness ini.
DB::table('emr_detail')->whereIn('emr_id', DB::table('emr')->select('emr_id'))->delete();
DB::table('emr')->delete();
echo "Data uji dibersihkan.\n";
