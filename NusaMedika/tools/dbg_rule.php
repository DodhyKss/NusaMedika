<?php
require '/var/www/html/vendor/autoload.php';
$app = require '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
require_once '/var/www/html/tools/lib_pay.php';
$rm = ruleMap('App\Http\Controllers\EMR\SkriningMpp\SkriningMppController');
$keys = ['risiko_tinggi_1','risiko_psikososial_4','risiko_lama_rawat_2','paraf_skrining_1','forma_plan_3','formb_waktu_1','formb_paraf_10','tanggal_skrining','risiko_tinggi_lainnya','jam_skrining'];
foreach ($keys as $k) { echo "$k => ".json_encode($rm[$k] ?? null)."\n"; }
echo 'total: '.count($rm)."\n";
