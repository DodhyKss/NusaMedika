<?php
require '/var/www/html/vendor/autoload.php';
$a=require '/var/www/html/bootstrap/app.php';
$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use App\Helpers\EmrHelper;

$expect = [
 22=>[2,73,26],23=>[2,73,27],24=>[2,93,46],25=>[2,93,47],26=>[9,94],27=>[9,113,66],
 28=>[9,95],29=>[2,74,28],30=>[8,133,86],31=>[2,75,29],32=>[8,134,87],33=>[8,134,88],
 34=>[8,76,31],35=>[2,75,30],36=>[9,113,67],37=>[9,114],38=>[9,113,68],
];
$err=0;
foreach ($expect as $fid=>$path) {
    $f=DB::table('form')->where('form_id',$fid)->first();
    if(!$f){ echo "MISSING form $fid\n"; $err++; continue; }
    $m=$path[0]; $s=$path[1]; $e=$path[2] ?? null;
    $ms=DB::table('dashboard_menu')->where('dashboard_menu_id',$m)->value('nama_menu');
    $ss=DB::table('dashboard_menu_sub')->where('dashboard_menu_sub_id',$s)->value('nama_sub_menu');
    $es=$e? DB::table('dashboard_menu_sub_extra')->where('dashboard_menu_sub_extra_id',$e)->value('nama_sub_menu_extra'):'(tanpa extra)';
    $nObj=DB::table('objek_form_control')->where('form_id',$fid)->count();
    $nVar=count(EmrHelper::objekVariabels($fid));
    $nAks=DB::table('akses_ehr')->where('form_id',$fid)->count();
    printf("%-3d %-36s m=%s / s=%s / e=%s | objek=%-3d var=%-3d akses=%d\n",
        $fid,$f->slug,$ms,$ss,$es,$nObj,$nVar,$nAks);
    if(!$nObj || !$nVar || !$nAks){ echo "   ^^ KOSONG\n"; $err++; }
}
echo $err? "\nERROR: $err\n" : "\nSemua form P2 punya objek, variabel, dan akses.\n";
