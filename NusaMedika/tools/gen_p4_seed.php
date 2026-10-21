<?php
/**
 * Generator blok seeder P4 (Fase P4 — Kebidanan & MCU).
 *
 * Hanya alat bantu: membaca hasil `tools/extract_p4.php` lalu mencetak blok PHP
 * siap tempel ke `database/seeders/EmrMasterSeeder.php`. Tidak dipanggil aplikasi.
 *
 * Jalankan: docker compose exec -T app php tools/gen_p4_seed.php > /tmp/p4_seed.txt
 */

require '/var/www/html/vendor/autoload.php';
$app = require '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Reuse extractor untuk memetakan variabel => objek per form.
$specs = [
    ['doc' => 'docs/KONSEP_OBSTETRI_NEONATAL.md', 'forms' => range(59, 69)],
    ['doc' => 'docs/KONSEP_FORM_MCU.md', 'forms' => range(70, 84)],
];

$objek = [];
$var = [];

foreach ($specs as $spec) {
    $lines = file('/var/www/html/'.$spec['doc'], FILE_IGNORE_NEW_LINES);
    $form = null;
    foreach ($lines as $line) {
        if (preg_match('/^#{2,3}\s+(?:\d+\.\s*)?Form\s+(\d+)/i', $line, $m)) {
            $form = (int) $m[1];
            continue;
        }
        if ($form === null || ! in_array($form, $spec['forms'], true)) {
            continue;
        }
        if (! preg_match('/^\|\s*([^|]*?)\s*\|\s*(\d{1,3})\s*\|\s*([^|]*?)\s*\|/', $line, $m)) {
            continue;
        }
        if (! preg_match_all('/`([a-z_0-9]+)`/', $m[1], $vm)) {
            continue;
        }
        $objekId = (int) $m[2];
        $nama = trim($m[3]);
        if ($nama === '' || $nama === '—' || $nama === '-') {
            continue;
        }
        $objek[$objekId] = $nama;
        foreach ($vm[1] as $v) {
            if (preg_match('/^(.*?)(\d+)\.\.(\d+)$/', $v, $r)) {
                for ($i = (int) $r[2]; $i <= (int) $r[3]; $i++) {
                    $var[$form][$r[1].$i] = $objekId;
                }
            } else {
                $var[$form][$v] = $objekId;
            }
        }
    }
}

// Objek yang hanya muncul di loop/komentar pada dokumen konsep.
$objek += [
    554 => 'USG Ibu',
    561 => 'Butir Pengkajian Perina',
    566 => 'Masalah Kala',
    567 => 'Penatalaksanaan Kala',
    581 => 'Tindakan Bayi Lahir Normal',
    582 => 'Tindakan Bayi Lahir Tidak Normal',
    584 => 'Tindakan Resusitasi',
    587 => 'APGAR - Frekuensi Jantung',
    588 => 'APGAR - Usaha Napas',
    589 => 'APGAR - Tonus Otot',
    590 => 'APGAR - Refleks',
    591 => 'APGAR - Warna',
    592 => 'APGAR - Total Skor',
];

// Mapping tambahan yang hanya didefinisikan lewat loop di konsep.
$var[60] += array_merge(
    (function () { $r = []; for ($i = 1; $i <= 5; $i++) { $r['usg_ibu_'.$i] = 554; } return $r; })(),
    (function () {
        $jumlah = ['kepala' => 5, 'ubun_ubun' => 3, 'mata' => 5, 'telinga' => 3, 'mulut' => 5,
            'hidung' => 4, 'leher' => 3, 'menangis' => 3, 'punggung' => 6, 'anus' => 3,
            'genitalia_pria' => 5, 'keluaran_genitalia' => 3];
        $r = [];
        foreach ($jumlah as $prefix => $n) { for ($i = 1; $i <= $n; $i++) { $r[$prefix.'_'.$i] = 561; } }

        return $r;
    })(),
    array_fill_keys([
        'kepala_lain', 'telinga_lain', 'mulut_lain', 'punggung_lain', 'anus_lain', 'genitalia_pria_lain',
        'warna_kulit', 'pergerakan_bayi', 'meconium', 'dada_bayi', 'perut_bayi', 'umbilikal', 'bising_usus',
        'jari_tangan', 'pergerakan_tangan', 'jari_kaki', 'pergerakan_kaki', 'garis_tangan', 'tangan_keriput',
        'tangan_terkelupas', 'garis_kaki', 'kaki_keriput', 'kaki_terkelupas', 'kuku',
        'labio_mayora', 'labio_minora',
        'tgl_bab_pertama', 'jam_bab_pertama', 'tgl_bak_pertama', 'jam_bak_pertama',
    ], 561)
);

// Riwayat obstetrik form 59: 5 baris x 9 kolom.
foreach (['tempat', 'tahun', 'kehamilan', 'jenis_persalinan', 'penyulit', 'nifas', 'sex', 'bb', 'keadaan'] as $kolom) {
    for ($i = 1; $i <= 5; $i++) {
        $var[59]['riwayat_obst_'.$kolom.'_'.$i] = 542;
    }
}

// Form 62: masalah & penatalaksanaan per kala (4 kala).
for ($k = 1; $k <= 4; $k++) {
    $var[62]['masalah_kala_'.$k] = 566;
    $var[62]['masalah_kala_detail_'.$k] = 566;
    $var[62]['penatalaksanaan_kala_'.$k] = 567;
}

// Form 64 & 65: tindakan checkbox baris berulang.
for ($i = 1; $i <= 4; $i++) {
    $var[64]['tindakan_bayi_normal_'.$i] = 581;
}
for ($i = 1; $i <= 9; $i++) {
    $var[64]['tindakan_bayi_abnormal_'.$i] = 582;
}
for ($i = 1; $i <= 6; $i++) {
    $var[65]['tindakan_resusitasi_'.$i] = 584;
}

// Form 66: APGAR 5 tanda x 5 menit (1, 5, 10, 15, 20).
$menitApgar = [1, 5, 10, 15, 20];
foreach (['frekuensi_jantung' => 587, 'usaha_napas' => 588, 'tonus_otot' => 589, 'refleks' => 590, 'warna' => 591] as $tanda => $objekId) {
    foreach ($menitApgar as $menit) {
        $var[66]['apgar_'.$tanda.'_'.$menit] = $objekId;
    }
}
foreach ($menitApgar as $menit) {
    $var[66]['apgar_total_'.$menit] = 592;
}

// Form 69: paraf & form B.
for ($i = 1; $i <= 5; $i++) {
    $var[69]['paraf_skrining_'.$i] = 606;
}
for ($i = 1; $i <= 6; $i++) {
    $var[69]['forma_plan_'.$i] = 607;
    $var[69]['forma_paraf_'.$i] = 608;
}
for ($i = 1; $i <= 5; $i++) {
    $var[69]['formb_waktu_'.$i] = 609;
}

// ---------------------------------------------------------------- output

echo "===== OBJEK =====\n";
// Objek <= 177 sudah dideklarasikan baseline (lihat seeder) — hanya objek BARU
// rentang dokumen ini (>= 536) yang dicetak, agar tidak dideklarasikan ulang.
$objek = array_filter($objek, fn ($id) => $id >= 536, ARRAY_FILTER_USE_KEY);
ksort($objek);
$lines = [];
foreach ($objek as $id => $nama) {
    $lines[] = sprintf("            %d => '%s',", $id, str_replace("'", "\\'", $nama));
}
foreach (array_chunk($lines, 3) as $chunk) {
    echo implode(' ', $chunk)."\n";
}

echo "\n===== MAPPING =====\n";
ksort($var);
foreach ($var as $form => $pairs) {
    printf("            %d => [\n", $form);
    foreach ($pairs as $v => $o) {
        printf("                '%s' => %d,\n", $v, $o);
    }
    printf("            ],\n");
}

echo "\n===== REKAP =====\n";
printf("objek=%d, form=%d, total variabel=%d\n", count($objek), count($var), array_sum(array_map('count', $var)));
foreach ($var as $f => $pairs) {
    printf("  form %-4d %d variabel\n", $f, count($pairs));
}