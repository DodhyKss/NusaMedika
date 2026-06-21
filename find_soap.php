<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = DB::select("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'emr' OR table_name = 'emr_detail'");
foreach($columns as $c) {
    echo $c->column_name . ' (' . $c->data_type . ')' . PHP_EOL;
}
