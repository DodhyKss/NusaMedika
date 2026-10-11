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
    if (! preg_match_all('/->validate\(\s*\[/', $src, $hits, PREG_OFFSET_CAPTURE)) {
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
            if (preg_match('/\$' . $vn . '\s*=\s*\[/', $src, $at, PREG_OFFSET_CAPTURE)) {
                $start = strpos($src, '[', $at[0][1]);
                if ($start === false) { continue; }
                $depth = 0; $q = null; $len = strlen($src);
                for ($i = $start; $i < $len; $i++) {
                    $c = $src[$i];
                    if ($q !== null) {
                        if ($c === '\\' && $q === "'") { $i++; continue; }
                        if ($c === $q) { $q = null; }
                        continue;
                    }
                    if ($c === "'" || $c === '"') { $q = $c; continue; }
                    if ($c === '[') { $depth++; } elseif ($c === ']') {
                        $depth--;
                        if ($depth === 0) {
                            foreach (splitEntries(substr($src, $start + 1, $i - $start - 1)) as $k => $v) { $map[$k] ??= $v; }
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

    return $map;
}

/** Pisahkan daftar literal PHP jadi elemen, hormati kutip & kurung. */
function phpListValues(string $s): array
{
    $out = []; $buf = ''; $q = null; $depth = 0; $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $c = $s[$i];
        if ($q !== null) {
            if ($c === '\\' && $q === "'") { $buf .= $c.($s[$i+1] ?? ''); $i++; continue; }
            if ($c === $q) { $q = null; }
            $buf .= $c;
            continue;
        }
        if ($c === "'" || $c === '"') { $q = $c; $buf .= $c; continue; }
        if ($c === '[' || $c === '(') { $depth++; }
        if ($c === ']' || $c === ')') { $depth--; }
        if ($c === ',' && $depth === 0) { $out[] = trim($buf); $buf = ''; continue; }
        $buf .= $c;
    }
    if (trim($buf) !== '') { $out[] = trim($buf); }

    return array_values(array_filter(array_map(fn ($t) => trim(trim($t), "'\" "), $out), fn ($t) => $t !== ''));
}


function optsFromRule(string $rule, string $src): ?string
{
    // Rule::in([ 'A', 'B' ])
    if (preg_match("/Rule::in\(\s*\[((?:[^\]]|\](?!\)))*)\]/s", $rule, $m)) {
        $vv = phpListValues($m[1]);

        return $vv[0] ?? null;
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
            $p[$v] = (int) round((intval($m[1]) + intval($m[2])) / 2); continue;
        }
        // min:N / max:N terpisah (bukan between) -> pilih nilai yang memenuhi
        if (preg_match("/\\bmin:(\\d+)\\b/", $rule, $mn) && preg_match('/\\b(integer|numeric)\\b/', $rule)) {
            $lo = (int) $mn[1];
            $hi = preg_match("/\bmax:(\d+)\b/", $rule, $mx) ? (int) $mx[1] : null;
            if ($hi !== null && $hi >= $lo) { $p[$v] = (int) round(($lo + $hi) / 2); continue; }
            $default = ['40' => 120, '30' => 170, '20' => 80, '10' => 36, '5' => 20, '1' => 3, '0' => 1];
            $p[$v] = $default[(string) $lo] ?? $lo; continue;
        }
        if (preg_match("/\bmax:(\d+)\b/", $rule, $mx) && preg_match('/\b(integer|numeric)\b/', $rule)) { $p[$v] = max(0, (int) floor(intval($mx[1]) / 2)); continue; }
        if (preg_match("/'date'|H:i/", $rule) && preg_match('/integer|numeric/', $rule)) {
            $p[$v] = 1;

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
            $p[$v] = '2026-10-10';

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
