<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$seqs = DB::select("SELECT sequence_name FROM information_schema.sequences WHERE sequence_name LIKE '%emr%' LIMIT 10");
foreach($seqs as $s) {
    echo $s->sequence_name . PHP_EOL;
}
