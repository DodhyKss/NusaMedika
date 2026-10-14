<?php

use App\Helpers\EmrHelper;
use App\Helpers\SelectOption;
use Illuminate\Support\Facades\DB;

/**
 * Payload builder untuk E2E test EMR.
 * Membaca BLOK validated() controller lalu memetakan variabel -> aturan,
 * lalu membuat nilai yang LAYAK (bukan sekadar lolos).
 */
/**
 * Split blok rules PHP menjadi ['var' => 'rules'] secara bracket-aware:
 * pisahkan koma di level terluar, abaikan koma di dalam kurung/kutip.
 */
function splitEntries(string $body): array
{
    // Buang komentar baris (// ...) supaya entri setelahnya tetap terdeteksi.
    $body = preg_replace('#//[^\n\'"\`]*#', '', $body);
    $body = preg_replace('#/\*.*?\*/#s', '', $body);
    $out = [];
    $depth = 0;
    $buf = '';
    $q = null;
    $len = strlen($body);
    for ($i = 0; $i < $len; $i++) {
        $c = $body[$i];
        if ($q !== null) {
            if ($c === '\\' && $q === "'") {
                $buf .= $c.($body[$i + 1] ?? '');
                $i++;

                continue;
            }
            if ($c === $q) {
                $q = null;
            }
            $buf .= $c;

            continue;
        }
        if ($c === "'" || $c === '"') {
            $q = $c;
            $buf .= $c;

            continue;
        }
        if ($c === '[' || $c === '(') {
            $depth++;
        }
        if ($c === ']' || $c === ')') {
            $depth--;
        }
        if ($c === ',' && $depth === 0) {
            $out[] = $buf;
            $buf = '';

            continue;
        }
        $buf .= $c;
    }
    if (trim($buf) !== '') {
        $out[] = $buf;
    }
    $res = [];
    foreach ($out as $e) {
        if (! preg_match("/^\s*'([a-z0-9_.\*]+)'\s*=>/i", $e, $m)) {
            continue;
        }
        $res[$m[1]] = trim(substr($e, strlen($m[0])));
    }

    return $res;
}

/** Ambil isi kurung [...] setelah validate( secara bracket-aware. */
function extractRuleBlocks(string $src): array
{
    $out = [];
    // Tangkap baik `->validate([...])` maupun `\Validator::make($x, [...])`
    // (form P4 memakai Validator::make untuk aturan after()/cross-field).
    if (! preg_match_all('/(?:->validate\(\s*\[|Validator::make\([^,]+,\s*\[)/', $src, $hits, PREG_OFFSET_CAPTURE)) {
        return $out;
    }
    foreach ($hits[0] as $h) {
        $start = $h[1] + strlen($h[0]) - 1;   // posisi '['
        $depth = 0;
        $q = null;
        $len = strlen($src);
        for ($i = $start; $i < $len; $i++) {
            $c = $src[$i];
            if ($q !== null) {
                if ($c === '\\' && $q === "'") {
                    $i++;

                    continue;
                }
                if ($c === $q) {
                    $q = null;
                }

                continue;
            }
            if ($c === "'" || $c === '"') {
                $q = $c;

                continue;
            }
            if ($c === '[') {
                $depth++;
            } elseif ($c === ']') {
                $depth--;
                if ($depth === 0) {
                    $out[] = substr($src, $start + 1, $i - $start - 1);
                    break;
                }
            }
        }
    }

    return $out;
}

/**
 * Nilaikan ekspresi kunci baris-bersuffix, mis:
 *   `'obat_'.$b`       -> obat_3
 *   `'obat_'.$b.'_x'`  -> obat_3_x
 *
 * Mengembalikan null bila ekspresi tidak berupa konkatenasi literal-quoted +
 * variabel loop yang dikenali (mis. pemanggilan method), sehingga pemanggil
 * bisa melewatinya alih-alih menghasilkan kunci yang salah.
 */
function evaluasiKunci(string $ekspresi, string $var, int $n): ?string
{
    $ekspresi = trim($ekspresi);
    $q = preg_quote($var, '/');

    if (! preg_match_all('/\x27([^\x27]*)\x27|\$'.$q.'/', $ekspresi, $bagian, PREG_SET_ORDER)) {
        return null;
    }

    $hasil = '';
    foreach ($bagian as $b) {
        if (isset($b[1]) && $b[1] !== '') {
            $hasil .= $b[1];
        } else {
            $hasil .= (string) $n;
        }
    }

    // Sisanya harus kosong (hanya operator titik & spasi) agar ekspresi benar.
    $sisa = preg_replace('/\x27[^\x27]*\x27|\$'.$q.'/', '', $ekspresi);
    $sisa = str_replace(['.', ' ', '\t', '\n', '\r'], '', (string) $sisa);

    return ($sisa === '' && $hasil !== '') ? $hasil : null;
}

/**
 * Sama dengan evaluasiKunci() tapi untuk nilai foreach yang BUKAN angka
 * (string konstanta, mis. 'risiko_tinggi'). Dipakai expandRuleLoops().
 */
function evaluasiKunciForeach(string $ekspresi, string $var, $nilai): ?string
{
    $ekspresi = trim($ekspresi);
    $q = preg_quote($var, '/');
    if (! preg_match_all('/\x27([^\x27]*)\x27|\$'.$q.'/', $ekspresi, $bagian, PREG_SET_ORDER)) {
        return null;
    }
    $hasil = '';
    foreach ($bagian as $b) {
        if (isset($b[1]) && $b[1] !== '') {
            $hasil .= $b[1];
        } else {
            $hasil .= (string) $nilai;
        }
    }
    $sisa = preg_replace('/\x27[^\x27]*\x27|\$'.$q.'/', '', $ekspresi);
    $sisa = str_replace(['.', ' ', '\t', '\n', '\r'], '', (string) $sisa);

    return ($sisa === '' && $hasil !== '') ? $hasil : null;
}

/**
 * Resolusi variabel yang isinya aturan validasi, mis.
 *     $statusRule = 'nullable|in:1,T,K,A,ESO,TAP,stop';
 *     $rules['status_'.$b] = $statusRule;
 * sehingga aturan baris ikut terbaca utuh (bukan hanya nama variabelnya).
 */
function selfResolveRule(string $rule, string $src): string
{
    $rule = trim($rule);
    if (! str_starts_with($rule, '$')) {
        return $rule;
    }
    if (preg_match('/^\$(\w+)$/', $rule, $v)
        && preg_match('/\$'.$v[1].'\s*=\s*([\x27\x22])(.*?)\1\s*;/s', $src, $isi)) {
        return $isi[2];
    }

    return $rule;
}

/**
 * Ambil isi blok `{ ... }` yang mengikuti header loop pada offset tertentu.
 *
 * Header loop sudah Includes `foreach (...)` / `for (...)` TANPA brace, jadi
 * caller hanya perlu melewati whitespace lalu brace pembuka.
 *
 * @return string|null isi body, atau null bila tidak ditemukan
 */
function loopBodyAt(string $src, int $offset): ?string
{
    $len = strlen($src);
    $i = $offset;
    while ($i < $len && ctype_space($src[$i])) {
        $i++;
    }
    if ($i >= $len || $src[$i] !== '{') {
        return null;
    }
    $i++;
    $depth = 1;
    $start = $i;
    for (; $i < $len && $depth > 0; $i++) {
        // Abaikan brace di dalam string/komentar agar depth tidakenyesatkan.
        $c = $src[$i];
        if ($c === "'" || $c === '"') {
            $q = $c;
            $i++;
            while ($i < $len && $src[$i] !== $q) {
                if ($src[$i] === '\\') {
                    $i++;
                } $i++;
            }

            continue;
        }
        if ($c === '{') {
            $depth++;
        } elseif ($c === '}') {
            $depth--;
            if ($depth === 0) {
                return substr($src, $start, $i - $start);
            }
        }
    }

    return null;
}

/**
 * Baca blok `return [ 'x' => 'rule', ... ];` di dalam method
 * `rulesTambahan()` (hook subclass `McuSpesialistikController`).
 *
 * @return array<string, string>
 */
function expandRulesTambahan(string $src): array
{
    if (! preg_match('/function\s+rulesTambahan\s*\([^)]*\)\s*(?::\s*array)?\s*\{/s', $src, $m, PREG_OFFSET_CAPTURE)) {
        return [];
    }
    $pos = $m[0][1];
    if (! preg_match('/return\s*\[/s', $src, $r, PREG_OFFSET_CAPTURE, $pos)) {
        return [];
    }
    $start = strpos($src, '[', $r[0][1]);
    if ($start === false) {
        return [];
    }
    $depth = 0;
    $q = null;
    $len = strlen($src);
    for ($i = $start; $i < $len; $i++) {
        $c = $src[$i];
        if ($q !== null) {
            if ($c === '\\' && $q === "'") {
                $i++;

                continue;
            }
            if ($c === $q) {
                $q = null;
            }

            continue;
        }
        if ($c === "'" || $c === '"') {
            $q = $c;

            continue;
        }
        if ($c === '[') {
            $depth++;
        } elseif ($c === ']') {
            $depth--;
            if ($depth === 0) {
                $out = [];
                foreach (splitEntries(substr($src, $start + 1, $i - $start - 1)) as $k => $v) {
                    $out[$k] = $v;
                }

                return $out;
            }
        }
    }

    return [];
}

/**
 * Baca blok literal `$rules = [ 'a' => '...', ... ];` dari sebuah source.
 *
 * Berbeda dari `extractRuleBlocks()` (yang mencari blok `validate([...])`),
 * fungsi ini menangkap variabel apa pun yang namanya persis `$rules` lalu
 * memecah isinya sebagai pasangan variabel => aturan.
 *
 * @return array<string, string>
 */
function expandRuleBlocks(string $src): array
{
    $out = [];
    if (! preg_match_all('/\$rules\s*=\s*\[/', $src, $m, PREG_OFFSET_CAPTURE)) {
        return $out;
    }

    foreach ($m as $satu) {
        $start = strpos($src, '[', $satu[0][1]);
        if ($start === false) {
            continue;
        }
        $depth = 0;
        $q = null;
        $len = strlen($src);
        for ($i = $start; $i < $len; $i++) {
            $c = $src[$i];
            if ($q !== null) {
                if ($c === '\\' && $q === "'") {
                    $i++;

                    continue;
                }
                if ($c === $q) {
                    $q = null;
                }

                continue;
            }
            if ($c === "'" || $c === '"') {
                $q = $c;

                continue;
            }
            if ($c === '[') {
                $depth++;
            } elseif ($c === ']') {
                $depth--;
                if ($depth === 0) {
                    foreach (splitEntries(substr($src, $start + 1, $i - $start - 1)) as $k => $v) {
                        $out[$k] ??= trim($v);
                    }
                    break;
                }
            }
        }
    }

    return $out;
}

/**
 * Ekspansi aturan validasi yang kunci-nya dihitung dari loop koleksi data.
 *
 * Contoh di McuSpesialistikController:
 *     foreach ($organ as $o) {
 *         $rules[$o['kunci'].'_ada'] = 'required|in:0,1';
 *         $rules[$o['kunci']]      = 'nullable|string|max:2000';
 *     }
 *
 * Kuncinya bukan literal, sehingga ekspansi loop biasa tidak bisa menjangkau.
 * Fungsi ini membaca definisi koleksi (`McuSpesialistikHelper::organ($slug)`)
 * yang dirujuk lewat variabel, lalu membangkitkan pasangan aturan konkret.
 *
 * @return array<string, string>
 */
function expandRuleKoleksi(string $src, string $class): array
{
    $out = [];

    // Forehead: `foreach ( <ekspresi> as $item )` — koleksi boleh berupa
    // variabel (`$organ`) ATAU panggilan statis langsung
    // (`McuSpesialistikHelper::organ($this->slug)`).
    $polaLoop = '/foreach\s*\(\s*(.+?)\s+as\s+\$(\w+)\s*\)/s';
    if (! preg_match_all($polaLoop, $src, $loop, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
        return $out;
    }

    foreach ($loop as $info) {
        $ekspresiKoleksi = trim($info[1][0]);
        $varItem = $info[2][0];

        // Hanya koleksi yang berasal dari panggilan statik helper yang
        // bisa dievaluasi; variabel biasa ditangani `expandRuleLoops()`.
        $nilaiKoleksi = selfResolveCollection($src, $class, $ekspresiKoleksi);
        if (! is_array($nilaiKoleksi)) {
            continue;
        }

        $body = loopBodyAt($src, $info[0][1] + strlen($info[0][0]));
        if ($body === null) {
            continue;
        }

        // $rules[$item['kunci'].'_ada'] = '...'  (bersuffix literal)
        $polaAturan = <<<'REGEX'
~\$rules\s*\[\s*\$(\w+)\s*\[\x27([^\x27]*)\x27\s*\]\s*\.\s*\x27([^\x27]*)\x27\s*\]\s*=\s*(.+?);~s
REGEX;
        // $rules[$item['kunci']] = '...'
        $polaPolos = <<<'REGEX'
~\$rules\s*\[\s*\$(\w+)\s*\[\x27([^\x27]*)\x27\s*\]\s*\]\s*=\s*(.+?);~s
REGEX;

        $aturan = [];
        if (preg_match_all($polaAturan, $body, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $x) {
                $aturan[] = ['var' => $x[1], 'suffix' => $x[3], 'rule' => trim($x[4])];
            }
        }
        if (preg_match_all($polaPolos, $body, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $x) {
                $aturan[] = ['var' => $x[1], 'suffix' => '', 'rule' => trim($x[3])];
            }
        }
        if ($aturan === []) {
            continue;
        }

        foreach ($nilaiKoleksi as $item) {
            if (! is_array($item) || ! isset($item['kunci'])) {
                continue;
            }
            foreach ($aturan as $a) {
                if ($a['var'] !== $varItem) {
                    continue;
                }
                $out[$item['kunci'].$a['suffix']] = $a['rule'];
            }
        }
    }

    return $out;
}

/**
 * Nilaikan `$var = Helper::method('slug')` / `Helper::method(SLUG_CONST)`.
 *
 * Hanya memanggil method STATIK pada kelas yang sedang diproses (kelas
 * controller atau helper yang di-`use`) — tidak ada input tak tepercaya yang
 * dievaluasi, dan hasilnya hanya dipakai untuk membangkitkan kunci aturan
 * validasi pada harness.
 */
function selfResolveCollection(string $src, string $class, string $ekspresi): ?array
{
    $ekspresi = trim($ekspresi);

    // Bentuk: Helper::method('slug') atau Helper::method($this->slug)
    if (! preg_match('/^([A-Za-z0-9_\\\\]+)::(\w+)\((.*)\)$/s', $ekspresi, $m)) {
        // Bentuk: $var yang nilainya panggilan statis — resolusi 1 tingkat.
        if (preg_match('/^\$(\w+)$/', $ekspresi, $mv)) {
            if (preg_match('/\$'.preg_quote($mv[1], '/').'\s*=\s*([A-Za-z0-9_\\\\]+)::(\w+)\(([^;]*)\)\s*;/', $src, $as)) {
                return selfResolveCollection($src, $class, $as[1].'::'.$as[2].'('.$as[3].')');
            }
        }

        return null;
    }

    [$kelas, $metode, $argumen] = [$m[1], $m[2], trim($m[3])];

    // Hanya izinkan kelas yang dirujuk di file ini.
    if (! str_contains($src, $kelas)) {
        return null;
    }

    // Nama kelas biasanya ditulis tanpa namespace; resolve lewat `use`.
    if (! str_contains($kelas, '\\')) {
        if (preg_match_all('/^use\s+([A-Za-z0-9_\\\\]+)(?:\s+as\s+(\w+))?\s*;/m', $src, $uu, PREG_SET_ORDER)) {
            foreach ($uu as $satu) {
                $alias = ($satu[2] ?? '') !== ''
                    ? $satu[2]
                    : substr(strrchr('\\'.$satu[1], '\\'), 1);
                if ($alias === $kelas) {
                    $kelas = $satu[1];
                    break;
                }
            }
        }
    }

    if (! class_exists($kelas) || ! method_exists($kelas, $metode)) {
        return null;
    }

    // Argumen hanya boleh literal string atau properti objek — tanpa eval bebas.
    if ($argumen === '') {
        $hasil = $kelas::$metode();
    } elseif (preg_match("/^['\"]([^'\"]*)['\"]$/", $argumen, $a)) {
        $hasil = $kelas::$metode($a[1]);
    } elseif (preg_match('/^\$this->(\w+)$/', $argumen, $a)) {
        $objek = new $class;
        if (! property_exists($objek, $a[1])) {
            return null;
        }
        $reflect = new ReflectionProperty($objek, $a[1]);
        $reflect->setAccessible(true);
        $nilai = $reflect->getValue($objek);
        if (! is_string($nilai)) {
            return null;
        }
        $hasil = $kelas::$metode($nilai);
    } else {
        return null;
    }

    return is_array($hasil) ? $hasil : null;
}

/**
 * Ekspansi aturan validasi yang ditulis di dalam loop numerik.
 *
 * Controller EMR menulis aturan baris-bersuffix lewat loop, mis.
 *     for ($b = 1; $b <= 20; $b++) {
 *         $rules['obat_'.$b]      = 'nullable|integer|exists:barang,barang_id';
 *         $rules['jumlah_'.$b]    = 'nullable|numeric|min:0|max:10000';
 *     }
 *
 * Aturan seperti ini tidak bisa dibaca regex biasa (kuncinya dihitung saat
 * runtime), sehingga bentuk tersebut dipecah di sini menjadi kunci konkret
 * (`obat_1` .. `obat_20`) supaya payload builder tetap bisa membuat payload
 * yang LAYAK untuk setiap baris.
 *
 * @return array<string, string> variabel => aturan
 */
function expandRuleLoops(string $src): array
{
    $out = [];

    // Batas loop boleh berupa literal (20) ATAU konstanta kelas
    // (self::MAKS_BARIS) — konstanta dibaca dari blok `const` di file yang sama.
    $konstanta = [];
    if (preg_match_all('/(?:public|private|protected)\s+const\s+(\w+)\s*=\s*(\d+)\s*;/', $src, $kc, PREG_SET_ORDER)) {
        foreach ($kc as $one) {
            $konstanta[$one[1]] = (int) $one[2];
        }
    }
    $batas = '(\d+|self::\w+)';

    // foreach ($arr as $x) / foreach ($arr as $x => $y) — konstanta kelas
    // dibaca dari `const NAMA = [ ... ];` (array literal, hormati kutip).
    // Dipakai form 69 (Skrining MPP) yang me-loop KATEGORI_RISIKO /
    // ASPEK_FORM_A, bukan angka.
    $konstantaArray = [];
    if (preg_match_all('/(?:public|private|protected)?\s*const\s+(\w+)\s*=\s*\[(.*?)\];/s', $src, $ka, PREG_SET_ORDER)) {
        foreach ($ka as $one) {
            $isi = [];
            if (preg_match_all('/\x27([^\x27]*)\x27\s*(?:=>|,)/', $one[2], $kv, PREG_SET_ORDER)) {
                foreach ($kv as $kvOne) {
                    $isi[] = $kvOne[1];
                }
            } elseif (preg_match_all('/(\d+)\s*(?:=>|,)/', $one[2], $kn, PREG_SET_ORDER)) {
                foreach ($kn as $knOne) {
                    $isi[] = (int) $knOne[1];
                }
            }
            $konstantaArray[$one[1]] = $isi;
        }
    }
    $polaForeach = '/foreach\s*\(\s*(?:self::(\w+)|\[\s*([^\]]*)\])\s+as\s+\$(\w+)/';
    if (preg_match_all($polaForeach, $src, $fe, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
        foreach ($fe as $info) {
            if ($info[1][1] >= 0) {
                $nama = $info[1][0];
                if (! isset($konstantaArray[$nama])) {
                    continue;
                }
                $iterable = $konstantaArray[$nama];
            } else {
                $iterable = phpListValues($info[2][0]);
            }
            if ($iterable === []) {
                continue;
            }
            $var = $info[3][0];
            $body = loopBodyAt($src, $info[0][1] + strlen($info[0][0]));
            if ($body === null) {
                continue;
            }
            $pattern = <<<'REGEX'
~\$rules\s*\[\s*(.+?)\s*\]\s*=\s*(.+?);~s
REGEX;
            if (! preg_match_all($pattern, $body, $assign, PREG_SET_ORDER)) {
                continue;
            }
            foreach ($assign as $a) {
                $ekspresiKunci = $a[1];
                $rule = trim($a[2]);
                if (strpos($ekspresiKunci, '$'.$var) === false) {
                    continue;
                }
                foreach ($iterable as $nilai) {
                    $kunci = evaluasiKunciForeach($ekspresiKunci, $var, $nilai);
                    if ($kunci === null) {
                        continue;
                    }
                    $out[$kunci] = selfResolveRule($rule, $src);
                }
            }
        }
    }

    // PENTING: pola harus riwayat sampai-header `)` (termasuk `\$i++`),
    // bukan berhenti di `;` — kalau berhenti di titik-semicolon, `bodyStart`
    // masih berada di dalam header dan pemindaian brace salah.
    $polaFor = '/for\s*\(\s*\$(\w+)\s*=\s*(\d+)\s*;\s*\$\1\s*<=?\s*'.$batas.'\s*;[^)]*\)/';
    if (! preg_match_all($polaFor, $src, $loop, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
        return $out;
    }

    foreach ($loop as $info) {
        $var = $info[1][0];
        $start = (int) $info[2][0];
        $raw = $info[3][0];
        if (preg_match('/^self::(\w+)$/', $raw, $kc2)) {
            if (! isset($konstanta[$kc2[1]])) {
                continue; // konstanta tidak ketemu (bisa dievaluasi dinamis) -> skip
            }
            $end = $konstanta[$kc2[1]];
        } else {
            $end = (int) $raw;
        }
        if ($end < $start || $end - $start > 200) {
            continue; // guard: loop tak wajar / tak terhingga
        }

        // Ambil isi body.
        $body = loopBodyAt($src, $info[0][1] + strlen($info[0][0]));
        if ($body === null) {
            continue;
        }

        // Cari assignment di dalam body, lalu ganti $var dengan tiap angka.
        // Tangkap EKSPESI kunci utuh (mis. `'obat_'.$b`), bukan hanya bagian
        // dalam kutip — variabel loop berada di bagian KONKATENASI, jadi kalau
        // hanya bagian kutip yang diambil, kuncinya tak pernah berubah.
        $pattern = <<<'REGEX'
~\$rules\s*\[\s*(.+?)\s*\]\s*=\s*(.+?);~s
REGEX;
        if (! preg_match_all($pattern, $body, $assign, PREG_SET_ORDER)) {
            continue;
        }

        foreach ($assign as $a) {
            $ekspresiKunci = $a[1];
            $rule = trim($a[2]);
            // Hanya ekspresi yang benar-benar memuat variabel loop yang
            // di-ekspansi; sisanya bukan aturan baris-bersuffix.
            if (strpos($ekspresiKunci, '$'.$var) === false) {
                continue;
            }
            for ($n = $start; $n <= $end; $n++) {
                $kunci = evaluasiKunci($ekspresiKunci, $var, $n);
                if ($kunci === null) {
                    continue;
                }
                $out[$kunci] = selfResolveRule($rule, $src);
            }
        }
    }

    return $out;
}

function ruleMap(string $absClass): array
{
    $src = file_get_contents((new ReflectionClass($absClass))->getFileName());
    $map = [];
    // Kumpulkan SEMUA blok validate([ ... ]) di file (bukan hanya yang pertama).
    foreach (extractRuleBlocks($src) as $body) {
        foreach (splitEntries($body) as $k => $v) {
            if (str_ends_with($k, '.*')) {
                $map['__el:'.substr($k, 0, -2)] ??= $v;

                continue;
            }
            $map[$k] ??= $v;
        }
    }
    // Blok rules yang dipisah: $var = [ ... ]; lalu validate($var)
    if (preg_match_all('/->validate\(\s*\$(\w+)/', $src, $vars)) {
        foreach ($vars[1] as $vn) {
            if (preg_match('/\$'.$vn.'\s*=\s*\[/', $src, $at, PREG_OFFSET_CAPTURE)) {
                $start = strpos($src, '[', $at[0][1]);
                if ($start === false) {
                    continue;
                }
                $depth = 0;
                $q = null;
                $len = strlen($src);
                for ($i = $start; $i < $len; $i++) {
                    $c = $src[$i];
                    if ($q !== null) {
                        if ($c === '\\' && $q === "'") {
                            $i++;

                            continue;
                        }
                        if ($c === $q) {
                            $q = null;
                        }

                        continue;
                    }
                    if ($c === "'" || $c === '"') {
                        $q = $c;

                        continue;
                    }
                    if ($c === '[') {
                        $depth++;
                    } elseif ($c === ']') {
                        $depth--;
                        if ($depth === 0) {
                            foreach (splitEntries(substr($src, $start + 1, $i - $start - 1)) as $k => $v) {
                                $map[$k] ??= $v;
                            }
                            break;
                        }
                    }
                }
            }
        }
    }
    // Blok rules yang dipisah (mis. $rules = [ ... ]; return $request->validate($rules + [...]))
    foreach (['rules', 'rulesForm', 'validatedRules'] as $varname) {
        if (preg_match('/\$'.$varname.'\s*=\s*\[(.*?)\]\s*;/s', $src, $rb)) {
            $rb[1] = preg_replace('#//[^\n\'\"\`]*#', '', $rb[1]);
            $rb[1] = preg_replace('#/\*.*?\*/#s', '', $rb[1]);
            preg_match_all("/'([a-z0-9_]+')\s*=>\s*(.+?)(?=,\s*'[a-z0-9_]+'\s*=>|\z)/s", $rb[1], $mm, PREG_SET_ORDER);
            foreach ($mm as $x) {
                $map[trim($x[1], "'")] ??= $x[2];
            }
        }
    }
    // array_fill_keys(self::CONST, 'rules') -> give rules to each const member
    if (preg_match("/array_fill_keys\\(\\s*(self::\\w+)\\s*,\\s*'([^']*)'\\s*\\)/s", $src, $f)
        && preg_match('/const\s+'.preg_quote($f[1], '/').'\s*=\s*\[(.*?)\];/s', $src, $cc)) {
        preg_match_all("/'([a-z0-9_]+)'/", $cc[1], $vv);
        foreach ($vv[1] as $v) {
            $map[$v] ??= $f[2];
        }
    }
    // Aturan berbentuk variabel lokal: `'x' => $visus` — resolve ke array
    // literal `$visus = [ ... ];` atau string `$visus = '...';` di file yang sama.
    foreach ($map as $variabel => $rule) {
        $r = trim($rule);
        if (preg_match('/^\$(\w+)$/', $r, $m)) {
            $vn = $m[1];
            // Array literal: $visus = [ 'required', 'string', ... ];
            // Elemen berbentuk konkatenasi (mis. 'regex:'.self::POLA) di-resolve
            // dulu sebelum digabung — phpListValues akan memotong kutipnya.
            if (preg_match('/\$'.preg_quote($vn, '/').'\s*=\s*\[([^\]]*)\]/s', $src, $mm)) {
                $bagian = phpListValues($mm[1]);
                foreach ($bagian as $i => $e) {
                    // phpListValues sudah memotong kutip di ujung, jadi pola
                    // menerima bentuk 'regex:X'.self::C maupun regex:X'.self::C.
                    if (preg_match("/^'([^']*)'\s*\.\s*self::(\w+)$/", $e, $cm)
                        || preg_match("/^([^']*)'\s*\.\s*self::(\w+)$/", $e, $cm)) {
                        $rcArr = new ReflectionClass($absClass);
                        if ($rcArr->hasConstant($cm[2])) {
                            $bagian[$i] = $cm[1].$rcArr->getConstant($cm[2]);
                        }
                    }
                }
                $map[$variabel] = "'".implode('|', $bagian)."'";

                continue;
            }
            // String literal: $visus = 'required|string';
            if (preg_match('/\$'.preg_quote($vn, '/')."\s*=\s*'([^']*)'/", $src, $mm)) {
                $map[$variabel] = "'".$mm[1]."'";
            }
        }
        // Konkatenasi string + const kelas: 'regex:'.self::POLA_VISUS
        elseif (preg_match("/^'([^']*)'\s*\.\s*self::(\w+)$/", $r, $m)) {
            $rcSelf = new ReflectionClass($absClass);
            if ($rcSelf->hasConstant($m[2])) {
                $nilai = $rcSelf->getConstant($m[2]);
                $map[$variabel] = "'".$m[1].$nilai."'";
            }
        }
    }

    // Hook rulesTambahan() pada subclass MCU spesialis.
    foreach (expandRulesTambahan($src) as $variabel => $rule) {
        $map[$variabel] ??= $rule;
    }

    // Assignment per-baris: $rules['x'] = 'required|string';
    // (dipakai controller yang menambah aturan di dalam foreach)
    // Catatan: pakai preg_match_all (bukan preg_match) — build CLI container ini
    // menolak argumen $flags pada preg_match, sementara preg_match_all normal.
    $patternAssignment = <<<'REGEX'
~\$\w+\[(?:\x27|\x22)([a-z0-9_]+)(?:\x27|\x22)\]\s*=\s*(.+?);~s
REGEX;
    if (preg_match_all($patternAssignment, $src, $as, PREG_SET_ORDER)) {
        foreach ($as as $x) {
            $map[trim($x[1], "'\"")] ??= trim($x[2]);
        }
    }

    // Aturan diwarisi dari kelas induk: subclass yang men-thin (mis. 9 form MCU
    // yang extends McuSpesialistikController) tidak mendefinisikan aturan
    // sendiri — semuanya ada di induknya.
    $kelas = new ReflectionClass($absClass);
    $induk = $kelas->getParentClass();
    while ($induk) {
        $srcInduk = file_get_contents($induk->getFileName());
        // $absClass (bukan nama induk) yang dipakai agar `$this->slug`
        // terbaca dari kelas KONKRET — kelas induk bisa abstract.
        foreach (expandRuleKoleksi($srcInduk, $absClass) as $variabel => $rule) {
            $map[$variabel] ??= $rule;
        }
        // Blok literal `$rules = [ 'a' => '...', ... ];` di kelas induk.
        foreach (expandRuleBlocks($srcInduk) as $variabel => $rule) {
            $map[$variabel] ??= $rule;
        }
        foreach (expandRuleLoops($srcInduk) as $variabel => $rule) {
            $map[$variabel] ??= $rule;
        }
        $induk = $induk->getParentClass();
    }

    // Aturan hasil ekspansi loop MENANG atas pembacaan lain: loop selalu
    // menulis ke $rules di dalam validated(), sedangkan pembacaan blok array
    // bisa menemukan string pesan dari method lain (mis. $errors[...] = "...").
    foreach (expandRuleLoops($src) as $variabel => $rule) {
        $map[$variabel] = $rule;
    }
    foreach (expandRuleKoleksi($src, $absClass) as $variabel => $rule) {
        $map[$variabel] ??= $rule;
    }

    return $map;
}

/** Pisahkan daftar literal PHP jadi elemen, hormati kutip & kurung. */
function phpListValues(string $s): array
{
    $out = [];
    $buf = '';
    $q = null;
    $depth = 0;
    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $c = $s[$i];
        if ($q !== null) {
            if ($c === '\\' && $q === "'") {
                $buf .= $c.($s[$i + 1] ?? '');
                $i++;

                continue;
            }
            if ($c === $q) {
                $q = null;
            }
            $buf .= $c;

            continue;
        }
        if ($c === "'" || $c === '"') {
            $q = $c;
            $buf .= $c;

            continue;
        }
        if ($c === '[' || $c === '(') {
            $depth++;
        }
        if ($c === ']' || $c === ')') {
            $depth--;
        }
        if ($c === ',' && $depth === 0) {
            $out[] = trim($buf);
            $buf = '';

            continue;
        }
        $buf .= $c;
    }
    if (trim($buf) !== '') {
        $out[] = trim($buf);
    }

    return array_values(array_filter(array_map(fn ($t) => trim(trim($t), "'\" "), $out), fn ($t) => $t !== ''));
}

function optsFromRule(string $rule, string $src): ?string
{
    // regex berisi alternasi pecahan Snellen (\d+/\d+) → kirim contoh valid.
    // Pola lain yang tidak dikenal dibiarkan agar fallback biasa menang.
    if (str_contains($rule, 'regex:') && str_contains($rule, '\\d+\\/\\d+')) {
        return '6/6';
    }
    // regex durasi HH:MM (^\d{1,2}:\d{2}$) → kirim contoh valid.
    if (str_contains($rule, 'regex:') && str_contains($rule, '\\d{1,2}:\\d{2}')) {
        return '01:30';
    }
    // regex persentase/digit (mis. ^\d{1,3}%?$) → kirim contoh valid.
    if (str_contains($rule, 'regex:') && str_contains($rule, '\\d{1,3}')) {
        return '50';
    }
    // Rule::in([ 'A', 'B' ])
    if (preg_match("/Rule::in\(\s*\[((?:[^\]]|\](?!\)))*)\]/s", $rule, $m)) {
        $vv = phpListValues($m[1]);

        return $vv[0] ?? null;
    }
    // Bentuk string: 'in:A,B,C' (dipisah tanda koma, tanpa tanda kutip)
    if (preg_match('/(?:^|\\||\[|,)\s*[\'\"]?in[\'\"]?:([^\'|\]]+)/', $rule, $m)) {
        $vv = phpListValues($m[1]);
        if ($vv !== []) {
            // Checkbox pola legacy (hidden "0" + checkbox "1"): kirim nilai "1"
            // supaya after() server benar-benar menguji cabang tercentang.
            // Daftar lain (mis. `in:Ya,Tidak,N/A`) tetap pakai opsi pertama.
            return (count($vv) === 2 && $vv[0] === '0' && $vv[1] === '1') ? '1' : $vv[0];
        }
    }
    // Rule::in($this->opsi('key')) / ::opsi("key")
    if (preg_match("/\\\$this->opsi\(\s*'([^']+)'\s*\)/", $rule, $m)) {
        $o = SelectOption::get($m[1]);

        return $o ? ($o[0]['value'] ?? null) : null;
    }
    // Rule::in(array_column(SelectOption::get('key'),'value'))
    if (preg_match("/array_column\(\s*SelectOption::get\(\s*'([^']+)'\s*\)/", $rule, $m)) {
        $o = SelectOption::get($m[1]);

        return $o ? ($o[0]['value'] ?? null) : null;
    }
    // $this->OPSI['key']
    if (preg_match("/\\\$this->OPSI\[\s*'([^']+)'\s*\]/", $rule, $m)) {
        if (preg_match('/const OPSI\s*=\s*\[(.*?)\n    \];/s', $src, $c)
            && preg_match("/'".preg_quote($m[1], '/')."'\s*=>\s*\[(.*?)\]/s", $c[1], $cc)) {
            $first = trim(explode(',', $cc[1])[0], "'\" \n");

            return $first !== '' ? $first : null;
        }
    }
    // Rule::in($var) -> cari assignment $var = [...] / array_column(SelectOption::get(...))
    if (preg_match('/Rule::in\\(\\s*\$(\\w+)\\s*\\)/', $rule, $m2)) {
        $vn = $m2[1];
        $pat = '/\\$'.$vn.'\\s*=\\s*array_column\\(\\s*SelectOption::get\\(\\s*\'([^\']+)\'/';
        if (preg_match($pat, $src, $m3)) {
            $o = SelectOption::get($m3[1]);
            if ($o) {
                return $o[0]['value'] ?? null;
            }
        }
        if (preg_match('/\\$'.$vn.'\\s*=\\s*\\[([^\\]]*)\\]/s', $src, $m3)) {
            $vv = array_map(fn ($t) => trim(trim($t), "'\" "), explode(',', $m3[1]));
            $vv = array_values(array_filter($vv, fn ($t) => $t !== ''));

            return $vv[0] ?? null;
        }
        if (preg_match('/\\$'.$vn.'\\s*=\\s*SelectOption::get\\(\\s*\'([^\']+)\'\\)/', $src, $m3)) {
            $o = SelectOption::get($m3[1]);
            if ($o) {
                return $o[0]['value'] ?? null;
            }
        }
    }
    // helper array konstan, mis. Helpers::ITEMS['a'] => ...
    if (preg_match("/\\\$?(\w+Helper)?::(\w+)\[\s*'([^']+)'\s*\]/", $rule, $m)) {
        $cls = $m[1] ? "App\\Helpers\\{$m[1]}" : null;
        if ($cls && class_exists($cls) && isset($cls::${m[2]})) {
            $arr = constant("$cls::{$m[2]}");
            $it = $arr[$m[3]] ?? null;
            if (is_array($it)) {
                return $it[0]['value'] ?? (is_scalar($it[0] ?? null) ? (string) $it[0] : null);
            }
        }
    }

    return null;
}

function buildPayload(string $absClass, int $fid): array
{
    $rc = new ReflectionClass($absClass);
    $src = file_get_contents($rc->getFileName());
    $rm = ruleMap($absClass);
    $p = [];
    foreach (EmrHelper::objekVariabels($fid) as $v) {
        $rule = trim($rm[$v] ?? '');
        // Checkbox pola legacy (hidden "0" + checkbox "1") yang nullable:
        // JANGAN diisi payload — hidden "0" mengalahkan aturan in:0,1 dan
        // after() server melewati grup bila tidak ada nilai selain "0"/kosong.
        // Aturan `required|in:0,1` TIDAK dilewati (butuh nilai).
        // Aturan enum NON-nullable harus tetap diisi — `in:0,1,2` (APGAR) bukan
        // checkbox dan wajib mendapat nilai valid dari daftar.
        if (! str_contains($rule, 'required')
            && preg_match('/\bin:(0,1|1,0|on,off|off,on)(?:\s*(\||$))/', $rule)) {
            continue;
        }
        $o = $rule !== '' ? optsFromRule($rule, $src) : null;
        if ($o !== null) {
            $p[$v] = $o;

            continue;
        }
        // exists:table,col -> ambil id pertama yang ada
        // Rule::exists('tbl','col') bentuk objek (bukan string "exists:...").
        if (preg_match("/Rule::exists\(\s*'([a-z0-9_]+)'\s*,\s*'([a-z0-9_]+)'/", $rule, $m)) {
            try {
                $q = DB::table($m[1]);
                // Hormati rantai ->where('col', value)
                if (preg_match_all("/->where\(\s*'([a-z0-9_]+)'\s*,\s*(\d+)\s*\)/", $rule, $ww, PREG_SET_ORDER)) {
                    foreach ($ww as $w) {
                        $q->where($w[1], (int) $w[2]);
                    }
                }
                $id = $q->value($m[2]);
                if ($id === null) {
                    $id = DB::table($m[1])->value($m[2]);
                }
                $p[$v] = $id ?? 1;

                continue;
            } catch (Throwable $e) {
            }
        }
        if (preg_match('/exists:([a-z0-9_]+),([a-z0-9_]+)/', $rule, $m)) {
            $id = null;
            try {
                $id = DB::table($m[1])->value($m[2]);
            } catch (Throwable $e) {
                $id = null;
            }
            // Validasi lintas-field: petugas serah != petugas terima.
            if (preg_match('/_terima_id$/', $v)) {
                $sibling = str_replace('_terima_id', '_serah_id', $v);
                if (isset($p[$sibling]) && $p[$sibling] !== $id) {
                    $p[$v] = $id;

                    continue;
                }
                try {
                    $alt = DB::table($m[1])->where($m[2], '!=', $p[$sibling] ?? 0)->value($m[2]);
                    if ($alt !== null) {
                        $p[$v] = $alt;

                        continue;
                    }
                } catch (Throwable $e) {
                }
            }
            // Validasi lintas-field: ruangan tujuan != ruangan asal
            // (Transfer Pasien Antar Ruangan, form 94 — guard after() server).
            if ($v === 'ruangan_tujuan' && isset($p['ruangan_asal'])) {
                if ($id !== null && $id !== $p['ruangan_asal']) {
                    $p[$v] = $id;

                    continue;
                }
                try {
                    $alt = DB::table($m[1])->where($m[2], '!=', $p['ruangan_asal'])->value($m[2]);
                    if ($alt !== null) {
                        $p[$v] = $alt;

                        continue;
                    }
                } catch (Throwable $e) {
                }
            }
            $p[$v] = $id ?? 1;

            continue;
        }
        if (preg_match('/(^|\\||\[|,)\s*[\'\"]?array[\'\"]?(\\||$|\s|,|\])/', $rule)) {
            $el = $rm['__el:'.$v] ?? '';
            if ($el !== '' && preg_match('/exists:([a-z0-9_]+),([a-z0-9_]+)/', $el, $m)) {
                try {
                    $id = DB::table($m[1])->value($m[2]);
                    if ($id !== null) {
                        $p[$v] = [$id];

                        continue;
                    }
                } catch (Throwable $e) {
                }
            }
            if ($el !== '' && preg_match('/integer|numeric/', $el)) {
                $p[$v] = [1];

                continue;
            }
            $p[$v] = ['Data Uji'];

            continue;
        }
        if (preg_match("/\bbetween:(\d+),(\d+)/", $rule, $m)) {
            $p[$v] = (int) round((intval($m[1]) + intval($m[2])) / 2);

            continue;
        }
        // min:N / max:N terpisah (bukan between) -> pilih nilai yang memenuhi
        if (preg_match('/\\bmin:(\\d+)\\b/', $rule, $mn) && preg_match('/\\b(integer|numeric)\\b/', $rule)) {
            $lo = (int) $mn[1];
            $hi = preg_match("/\bmax:(\d+)\b/", $rule, $mx) ? (int) $mx[1] : null;
            if ($hi !== null && $hi >= $lo) {
                $p[$v] = (int) round(($lo + $hi) / 2);

                continue;
            }
            $default = ['40' => 120, '30' => 170, '20' => 80, '10' => 36, '5' => 20, '1' => 3, '0' => 1];
            $p[$v] = $default[(string) $lo] ?? $lo;

            continue;
        }
        if (preg_match("/\bmax:(\d+)\b/", $rule, $mx) && preg_match('/\b(integer|numeric)\b/', $rule)) {
            $p[$v] = max(0, (int) floor(intval($mx[1]) / 2));

            continue;
        }
        // max:N pada kolom teks → potong contok "Data Uji" agar muat.
        if (preg_match("/\bmax:(\d+)\b/", $rule, $mx) && ! preg_match('/\b(integer|numeric|date)\b/', $rule)) {
            $n = (int) $mx[1];
            if ($n > 0) {
                $p[$v] = mb_substr('Data Uji', 0, $n);

                continue;
            }
        }
        if (preg_match("/'date'|H:i/", $rule) && preg_match('/integer|numeric/', $rule)) {
            $p[$v] = 1;

            continue;
        }
        if (preg_match('/(^|\\||\[|,)\s*[\'\"]?boolean[\'\"]?(\\||$|\s|,|\])/', $rule)) {
            $p[$v] = true;

            continue;
        }
        if (preg_match("/\binteger\b/", $rule)) {
            $p[$v] = 1;

            continue;
        }
        if (preg_match("/\bnumeric\b/", $rule)) {
            $p[$v] = 1.5;

            continue;
        }
        if (preg_match('/(^|\\|)date\\b/', $rule) || str_contains($v, 'tanggal')) {
            // after*:today (mis. rencana operasi) butuh hari ini — default
            // '2026-10-10' sudah lewat dan divalidasi gagal. before*:today
            // tetap memakai tanggal lampau (aman untuk semua form lama).
            $p[$v] = preg_match('/(?:^|\|)after(?:_or_equal)?:today\b/', $rule)
                ? date('Y-m-d')
                : '2026-10-10';

            continue;
        }
        if (preg_match('/H:i/', $rule) || str_contains($rule, 'date_format')) {
            $p[$v] = '10:00';

            continue;
        }
        // Fallback: cari key $v di return-array method helper mana pun (opsi[0].value)
        $found = helperOptionFor($v);
        if ($found !== null) {
            $p[$v] = $found;

            continue;
        }
        $p[$v] = 'Data Uji';
    }

    // Validasi lintas-field: p + a <= g (Asesmen Kebidanan, form 59).
    // Angka default harness membuat p = a = g = 1 sehingga selalu melanggar;
    // naikkan g supaya payload tetap layak.
    if (isset($p['obstetrik_g'], $p['obstetrik_p'], $p['obstetrik_a'])) {
        $p['obstetrik_g'] = max((int) $p['obstetrik_g'], (int) $p['obstetrik_p'] + (int) $p['obstetrik_a']);
    }

    // Pre Hemodialisa (form 102): jam_selesai > jam_mulai.
    if (isset($p['jam_mulai'], $p['jam_selesai'])) {
        $p['jam_mulai'] = '08:00';
        $p['jam_selesai'] = '11:00';
    }
    // tanggal_hd_berikutnya after tanggal_pertemuan.
    if (isset($p['tanggal_hd_berikutnya'], $p['tanggal_pertemuan'])) {
        $p['tanggal_hd_berikutnya'] = date('Y-m-d', strtotime(((string) $p['tanggal_pertemuan']).' +1 day'));
    }
    // akses_* minimal satu 'on' (semua default 'off' dari in:off,on).
    $aksesKeys = ['akses_fistula', 'akses_graft', 'akses_sndl', 'akses_cdl', 'akses_cdl_thermo', 'akses_femoral'];
    if (isset($p['akses_fistula'])) {
        $anyOn = false;
        foreach ($aksesKeys as $ak) {
            if (($p[$ak] ?? 'off') === 'on') {
                $anyOn = true;
                break;
            }
        }
        if (! $anyOn) {
            $p['akses_fistula'] = 'on';
        }
    }
    // komunikasi_lain/perawat_rumah_lain = 'on' → keterangan wajib.
    if (isset($p['komunikasi_keterangan'])) {
        $p['komunikasi_keterangan'] = 'Tidak ada kendala';
        $p['perawat_rumah_keterangan'] = 'Tidak berlaku';
    }
    // form 103/104: berat_badan >= bb_kering.
    if (isset($p['berat_badan'], $p['bb_kering'])) {
        $p['bb_kering'] = min((float) $p['bb_kering'], (float) $p['berat_badan']);
    }

    return $p;
}

/** Cari opsi pertama untuk sebuah variabel di seluruh helper statis. */
function helperOptionFor(string $var): ?string
{
    foreach (glob(__DIR__.'/../app/Helpers/*.php') as $file) {
        $cls = 'App\\Helpers\\'.basename($file, '.php');
        if (! class_exists($cls)) {
            continue;
        }
        $rc = new ReflectionClass($cls);
        foreach ($rc->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC) as $mth) {
            if ($mth->getNumberOfParameters() > 0) {
                continue;
            }
            try {
                $r = $mth->invoke(null);
            } catch (Throwable $e) {
                continue;
            }
            if (! is_array($r) || ! isset($r[$var])) {
                continue;
            }
            $item = $r[$var];
            if (is_array($item)) {
                if (isset($item['opsi'][0]['value'])) {
                    return (string) $item['opsi'][0]['value'];
                }
                if (isset($item[0]['value'])) {
                    return (string) $item[0]['value'];
                }
            }
        }
    }

    return null;
}
