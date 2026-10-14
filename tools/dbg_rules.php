<?php
require '/var/www/html/vendor/autoload.php';
$app = require '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$slug = $argv[1] ?? 'penyakit_dalam_mcu';
$map = [
    'penyakit_dalam_mcu' => 'App\\Http\\Controllers\\EMR\\PenyakitDalamMcu\\PenyakitDalamMcuController',
    'jantung_mcu' => 'App\\Http\\Controllers\\EMR\\JantungMcu\\JantungMcuController',
    'tft_mcu' => 'App\\Http\\Controllers\\EMR\\ThtMcu\\ThtMcuController',
    'mata_mcu' => 'App\\Http\\Controllers\\EMR\\MataMcu\\MataMcuController',
];
$cls = $map[$slug];
$fid = App\Helpers\EmrHelper::formIdBySlug($slug);
$vars = App\Helpers\EmrHelper::objekVariabels($fid);
$ctrl = new ReflectionClass($cls);
$m = $ctrl->getMethod('rules'); $m->setAccessible(true);
$rules = $m->invoke($ctrl->newInstanceWithoutConstructor());
echo "form_id=$fid vars=".count($vars).' rules='.count($rules)."\n";
foreach ($vars as $v) { if (!isset($rules[$v])) echo "  NO_RULE: $v\n"; }
