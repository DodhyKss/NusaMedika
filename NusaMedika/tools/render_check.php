<?php

/**
 * Render check EMR: buka form via GET lalu cari penanda skrip tertentu di
 * HTML hasil render. Dipakai untuk memastikan blok @push('scripts') benar-
 * benar ter-render oleh layouts/iframe.  Pakai:
 *   docker compose exec -T app php tools/render_check.php 45,46,47,48
 *   docker compose exec -T app php tools/render_check.php 45 'OPSI_OBAT'
 */
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['app.debug' => true]);

function profOfCek($u)
{
    return (int) DB::table('pegawai')->where('pegawai_id', DB::table('users')->where('user_id', $u)->value('pegawai_id'))->value('profesi_id');
}

$argv = array_slice($argv ?? [], 1);
$ids = array_map('intval', explode(',', $argv[0] ?? ''));
$needle = $argv[1] ?? null;

$K = $app->make(Illuminate\Contracts\Http\Kernel::class);
$fail = 0;
foreach ($ids as $fid) {
    $slug = DB::table('form')->where('form_id', $fid)->value('slug');
    $s = app('session')->driver();
    $s->start();
    $s->invalidate();
    $s->start();
    auth()->loginUsingId(3);
    session(['profesi_id' => profOfCek(3)]);
    $r = Request::create("/emr/form/$slug/12", 'GET');
    $r->setLaravelSession($s);
    $r->setUserResolver(fn () => DB::table('users')->where('user_id', 3)->first());
    app()->instance('request', $r);
    $resp = $K->handle($r);
    $html = $resp->getContent();
    $hasPush = (bool) preg_match('/<script\b[^>]*>(?:(?!<\/script>).)*OPSI_/s', $html);
    $hasInline = (bool) preg_match('/<script\b[^>]*>/', $html);
    $found = $needle === null ? $hasPush : str_contains($html, $needle);
    printf(
        "  %-32s status=%d script_tags=%d penanda=%s %s\n",
        $slug,
        $resp->getStatusCode(),
        preg_match_all('/<script\b/', $html),
        $found ? 'ADA' : 'TIDAK ADA',
        $found ? 'OK' : '<<< PERIKSA'
    );
    if (! $found) {
        $fail++;
    }
}
echo $fail ? "GAGAL: $fail\n" : "SEMUA OK\n";
exit($fail ? 1 : 0);
