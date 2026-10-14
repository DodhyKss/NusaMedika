<?php
/**
 * Ekstraktor mapping P4 dari dokumen konsep.
 *
 * Membaca tabel "| variabel | objek_id | nama_objek | ..." pada
 * KONSEP_OBSTETRI_NEONATAL.md & KONSEP_FORM_MCU.md, lalu mengekstrak
 * pasangan variabel => objek_id. Range ditulis sebagai `nama_1..5` atau
 * `nama_1..N` dan diekspansi di sini.
 *
 * Hanya untuk membantu membangun seeder — bukan bagian dari aplikasi.
 *
 * Jalankan: docker compose exec -T app php tools/extract_p4.php [form_id ...]
 */
$root = '/var/www/html';

$specs = [
    ['doc' => 'docs/KONSEP_OBSTETRI_NEONATAL.md', 'forms' => range(59, 69)],
    ['doc' => 'docs/KONSEP_FORM_MCU.md', 'forms' => range(70, 84)],
];

$only = array_slice($argv, 1);
$only = $only ? array_map('intval', $only) : [];

$result = [];

foreach ($specs as $spec) {
    $path = $root.'/'.$spec['doc'];
    if (! is_file($path)) {
        fwrite(STDERR, "Tidak ditemukan: {$path}\n");
        continue;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES);

    $form = null;
    foreach ($lines as $line) {
        // Judul bagian "## 3. Form 59 — ..." atau "### Form 62 — ..."
        if (preg_match('/^#{2,3}\s+(?:\d+\.\s*)?Form\s+(\d+)/i', $line, $m)) {
            $form = (int) $m[1];
            continue;
        }
        if ($form === null || ! in_array($form, $spec['forms'], true)) {
            continue;
        }
        if ($only && ! in_array($form, $only, true)) {
            continue;
        }

        // Baris tabel: | `variabel` | 123 | Nama Objek | ...
        // Sel pertama boleh berisi VARIANEL jamak dipisah " / "
        // (mis. `kepala_ada` / `kepala` untuk pasangan radio + text).
        if (! preg_match('/^\|\s*([^|]*?)\s*\|\s*(\d{1,3})\s*\|\s*([^|]*?)\s*\|/', $line, $m)) {
            continue;
        }
        if (! preg_match_all('/`([a-z_0-9]+)`/', $m[1], $vm)) {
            continue;
        }
        $daftarVariabel = $vm[1];
        $objekId = (int) $m[2];
        $namaObjek = trim($m[3]);

        $variabel = $m[1];
        $objekId = (int) $m[2];
        $namaObjek = trim($m[3]);

        if ($namaObjek === '' || $namaObjek === '—' || $namaObjek === '-') {
            continue;
        }
        // Lewati baris re-use yang objeknya <= 177? Tidak — re-use tetap dicatat.
        $result[$objekId] = $namaObjek;

        // Ekspansi range `nama_1..5` untuk SETIAP variabel di sel.
        foreach ($daftarVariabel as $variabel) {
            if (preg_match('/^(.*?)(\d+)\.\.(\d+)$/', $variabel, $r)) {
                [, $prefix, $start, $end] = $r;
                for ($i = (int) $start; $i <= (int) $end; $i++) {
                    $result['_var'][$form][$prefix.$i] = $objekId;
                }
            } else {
                $result['_var'][$form][$variabel] = $objekId;
            }
        }
    }
}

echo "===== NAMA OBJEK =====\n";
$objek = $result;
unset($objek['_var']);
ksort($objek);
foreach ($objek as $id => $nama) {
    printf("            %d => '%s',\n", $id, str_replace("'", "\\'", $nama));
}

echo "\n===== VARIABEL PER FORM =====\n";
$var = $result['_var'] ?? [];
ksort($var);
foreach ($var as $form => $pairs) {
    printf("// form %d (%d variabel)\n", $form, count($pairs));
    printf("%d => [\n", $form);
    foreach ($pairs as $v => $o) {
        printf("    '%s' => %d,\n", $v, $o);
    }
    printf("],\n");
}