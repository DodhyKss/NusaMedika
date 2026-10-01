<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Pusat logika Pengkajian Risiko Jatuh.
 *
 * Instrumen dipilih OTOMATIS berdasarkan usia pasien karena validitas tiap
 * instrumen berbeda:
 *
 *   - Anak (< 14 tahun)  -> Humpty Dumpty Falls Scale (HDS)
 *   - Dewasa (14-59)     -> Morse Fall Scale (MFS)
 *   - Lansia (>= 60)     -> Timed "Up and Go" (TUG) + norma usia
 *   - Semua usia         -> Sydney Scoring dapat dipilih manual (alternatif)
 *
 * Skor dan tingkat risiko dihitung di server (method `hitung()`) sehingga angka
 * yang tersimpan di emr_detail sama dengan yang tampil di layar. Frontend memakai
 * `definisi()` untuk merender item, lalu JS menghitung versi live; nilai yang
 * disimpan tetap hasil hitungan server.
 *
 * REFERENSI:
 *   - Morse Fall Scale (Morse 1989; LWW & VA Falls Toolkit): 6 item, skor 0-125.
 *     Rendah 0-24, sedang 25-50, tinggi >= 51.
 *   - Timed Up and Go (Podsiadlo & Richardson 1991). Norma usia dari
 *     meta-analisis Bohannon (2006, J Geriatr Phys Ther): 60-69 = 8,1 dtk
 *     (batas atas 9,0), 70-79 = 9,2 dtk (10,2), 80-99 = 11,3 dtk (12,7).
 *   - Humpty Dumpty Falls Scale (HDS, pediatric): 7 item, skor 1-7 per item,
 *     total 7-23; 7-11 risiko rendah, 12-23 risiko tinggi.
 *   - Sydney Scoring (STRATIFY modifikasi, Ontario / NSW Clinical Excellence
 *     Commission): Transfer Score + Mobility Score, tiap subitem 0-3; TS+MS 0-2
 *     menjadi 0, 3-6 menjadi 7, >= 7 dijumlahkan; total >= 9 risiko tinggi.
 */
class RisikoJatuhHelper
{
    /** Batas usia tiap instrumen (tahun). */
    public const USIA_ANAK_MAKS = 13;      // < 14 tahun -> HDS

    public const USIA_LANSIA_MIN = 60;     // >= 60 tahun -> TUG

    // =====================================================================
    // 1. HUMPTY DUMPTY FALLS SCALE (HDS) - anak
    // =====================================================================

    public static function hds(): array
    {
        return [
            'kode' => 'HDS',
            'nama' => 'Humpty Dumpty Falls Scale',
            'sumber' => 'Humpty Dumpty Falls Scale (HDS) - instrumen pediatric (total 7-23)',
            'rentang_usia' => 'Anak di bawah 14 tahun',
            'tipe' => 'ya_tidak',
            'skor_min' => 7,
            'skor_maks' => 23,
            // Adaptasi perawat: 7 item, jawaban Ya/Tidak dengan bobot 1,2,2,2,3,4,4.
            // Karena HDS asli memberi minimal 1 poin per item (total minimal 7),
            // skor di sini = 7 + jumlah bobot, lalu dibatasi maksimal 23, agar
            // rentang dan ambang publikasi (7-11 rendah, 12-23 tinggi) tetap terpakai.
            'skor_dasar' => 7,
            'item' => [
                ['var' => 'hds_1', 'label' => 'Pasien sangat hiperaktif', 'bobot_ya' => 1],
                ['var' => 'hds_2', 'label' => 'Pasien sering jatuh atau terjatuh dalam 1 bulan terakhir', 'bobot_ya' => 2],
                ['var' => 'hds_3', 'label' => 'Pasien menggunakan alat bantu (stroller, walker, kruk)', 'bobot_ya' => 2],
                ['var' => 'hds_4', 'label' => 'Pasien mengalami kendala komunikasi sehingga tidak dapat meminta bantuan', 'bobot_ya' => 2],
                ['var' => 'hds_5', 'label' => 'Pasien mengalami pusing saat berpindah tempat', 'bobot_ya' => 3],
                ['var' => 'hds_6', 'label' => 'Pasien mengalami fraktur (tulang paha atau tulang lengan atas)', 'bobot_ya' => 4],
                ['var' => 'hds_7', 'label' => 'Pasien pernah diikat (restraint) dalam 24 jam terakhir', 'bobot_ya' => 4],
            ],
            'kategori' => [
                ['maks' => 11, 'label' => 'Risiko Rendah', 'warna' => 'emerald', 'intervensi' => 'Pemantauan rutin, pasien berjalan escorted secara mandiri'],
                ['maks' => 23, 'label' => 'Risiko Tinggi', 'warna' => 'red', 'intervensi' => 'Pendampingan saat berjalan dan pasang pita risiko kuning'],
            ],
        ];
    }

    // =====================================================================
    // 2. MORSE FALL SCALE (MFS) - dewasa
    // =====================================================================

    public static function morse(): array
    {
        return [
            'kode' => 'MFS',
            'nama' => 'Morse Fall Scale',
            'sumber' => 'Morse Fall Scale (Morse 1989) - 6 item, skor 0-125',
            'rentang_usia' => 'Dewasa 14 sampai 59 tahun',
            'tipe' => 'opsi',
            'skor_min' => 0,
            'skor_maks' => 125,
            'item' => [
                [
                    'var' => 'mfs_1',
                    'label' => 'Riwayat jatuh (dalam 3 bulan terakhir atau selama dirawat)',
                    'opsi' => ['Tidak' => 0, 'Ya' => 25],
                ],
                [
                    'var' => 'mfs_2',
                    'label' => 'Diagnosa sekunder (lebih dari satu diagnose medis)',
                    'opsi' => ['Tidak' => 0, 'Ya' => 15],
                ],
                [
                    'var' => 'mfs_3',
                    'label' => 'Alat bantu ambulasi yang digunakan',
                    'opsi' => [
                        'Tanpa alat bantu / tirah di tempat tidur / assisted by nurse' => 0,
                        'Kruk / tongkat / walker' => 15,
                        'Menyandarkan diri pada perabot' => 30,
                    ],
                ],
                [
                    'var' => 'mfs_4',
                    'label' => 'Terpasang infus atau heparin lock (IV therapy)',
                    'opsi' => ['Tidak' => 0, 'Ya' => 20],
                ],
                [
                    'var' => 'mfs_5',
                    'label' => 'Gait dan transfer',
                    'opsi' => [
                        'Normal / tirah di tempat tidur / kursi roda' => 0,
                        'Lemah' => 10,
                        'Terganggu' => 20,
                    ],
                ],
                [
                    'var' => 'mfs_6',
                    'label' => 'Status mental',
                    'opsi' => [
                        'Sesuai kemampuan sendiri' => 0,
                        'Overestimate atau lupa keterbatasan' => 15,
                    ],
                ],
            ],
            'kategori' => [
                ['maks' => 24, 'label' => 'Risiko Rendah', 'warna' => 'emerald', 'intervensi' => 'Basic nursing care (perawatan dasar)'],
                ['maks' => 50, 'label' => 'Risiko Sedang', 'warna' => 'amber', 'intervensi' => 'Intervensi pencegahan jatuh standar'],
                ['maks' => 125, 'label' => 'Risiko Tinggi', 'warna' => 'red', 'intervensi' => 'Intervensi risiko tinggi plus pita warna pada pergelangan tangan'],
            ],
        ];
    }

    // =====================================================================
    // 3. TIMED "UP AND GO" (TUG) - outputtingelderly
    // =====================================================================

    public static function tug(): array
    {
        return [
            'kode' => 'TUG',
            'nama' => 'Timed "Up and Go" (TUG)',
            'sumber' => 'Podsiadlo & Richardson 1991; norma usia Bohannon 2006',
            'rentang_usia' => 'Lansia 60 tahun ke atas',
            'tipe' => 'angka',
            'skor_min' => 0,
            'skor_maks' => null,
            'item' => [
                [
                    'var' => 'tug_detik',
                    'label' => 'Durasi TUG dalam detik (duduk di kursi, bangun, berjalan 3 meter, kembali, lalu duduk kembali)',
                    'tipe' => 'number',
                    'satuan' => 'detik',
                ],
            ],
            // Kategori TUG ditentukan dari norma usia (lihat hitung()), bukan angka statis.
            'kategori' => [
                ['maks' => null, 'label' => 'Sesuai Norma Usia', 'warna' => 'emerald', 'intervensi' => 'Aktivitas fisik dan latihan keseimbangan secara rutin'],
                ['maks' => null, 'label' => 'Risiko Meningkat', 'warna' => 'amber', 'intervensi' => 'Latihan keseimbangan, penilaian gait, supervision saat ambulasi'],
                ['maks' => null, 'label' => 'Risiko Tinggi', 'warna' => 'red', 'intervensi' => 'Pendampingan penuh saat ambulasi, TUG 30 detik ke atas perlu bantuan'],
            ],
        ];
    }

    // =====================================================================
    // 4. SYDNEY SCORING (STRATIFY modifikasi) - alternatif semua usia
    // =====================================================================

    public static function sydney(): array
    {
        return [
            'kode' => 'SYDNEY',
            'nama' => 'Sydney Scoring (STRATIFY modifikasi)',
            'sumber' => 'Ontario Modified STRATIFY (Sydney Scoring), NSW Clinical Excellence Commission',
            'rentang_usia' => 'Alternatif - semua usia',
            'tipe' => 'opsi',
            'skor_min' => 0,
            'skor_maks' => 18,
            'syarat' => 'Transfer Score + Mobility Score: total 0-2 menjadi 0, total 3-6 menjadi 7, total 7 ke atas dijumlahkan apa adanya.',
            'item' => [
                ['var' => 'syd_ts_bed', 'label' => 'Transfer Score - memakai tempat tidur atau zipler frame', 'opsi' => ['Tidak' => 0, 'Ya' => 1]],
                ['var' => 'syd_ts_bangku', 'label' => 'Transfer Score - memakai kursi roda atau walker', 'opsi' => ['Tidak' => 0, 'Ya' => 2]],
                ['var' => 'syd_ts_bantuan', 'label' => 'Transfer Score - perlu bantuan satu orang terlatih', 'opsi' => ['Tidak' => 0, 'Ya' => 3]],
                ['var' => 'syd_ms_bantuan', 'label' => 'Mobility Score - berjalan dengan bantuan satu orang', 'opsi' => ['Tidak' => 0, 'Ya' => 1]],
                ['var' => 'syd_ms_kursi_roda', 'label' => 'Mobility Score - kursi roda independen', 'opsi' => ['Tidak' => 0, 'Ya' => 2]],
                ['var' => 'syd_ms_imobil', 'label' => 'Mobility Score - tidak dapat bergerak (imobil)', 'opsi' => ['Tidak' => 0, 'Ya' => 3]],
            ],
            'kategori' => [
                ['maks' => 8, 'label' => 'Risiko Rendah', 'warna' => 'emerald', 'intervensi' => 'Pemantauan rutin'],
                ['maks' => 18, 'label' => 'Risiko Tinggi', 'warna' => 'red', 'intervensi' => 'FRAMP: rencana manajemen risiko jatuh beserta intervensinya'],
            ],
        ];
    }

    // =====================================================================
    // DEFINISI DAN PEMILIHAN INSTRUMEN
    // =====================================================================

    /** Definisi lengkap sebuah instrumen berdasarkan kode (HDS | MFS | TUG | SYDNEY). */
    public static function definisi(?string $kode): ?array
    {
        return match (self::normalisasiKode($kode)) {
            'HDS' => self::hds(),
            'MFS' => self::morse(),
            'TUG' => self::tug(),
            'SYDNEY' => self::sydney(),
            default => null,
        };
    }

    /** Semua instrumen (untuk dropdown). */
    public static function semuaInstrumen(): array
    {
        return [self::hds(), self::morse(), self::tug(), self::sydney()];
    }

    /** Instrumen default berdasarkan usia pasien. */
    public static function instrumenUntukUsia(?int $usia): string
    {
        $usia = (int) $usia;

        if ($usia <= 0) {
            return 'MFS';
        }

        if ($usia <= self::USIA_ANAK_MAKS) {
            return 'HDS';
        }

        if ($usia >= self::USIA_LANSIA_MIN) {
            return 'TUG';
        }

        return 'MFS';
    }

    /**
     * Deteksi instrumen dari jawaban yang terkirim.
     *
     * Dipakai sebagai fallback ketika field `risiko_jatuh_instrumen` tidak ikut
     * terkirim (mis. form yang dirender ulang atau integraasi lama), supaya
     * jawaban item tidak hilang.
     */
    public static function deteksiInstrumen(array $input): ?string
    {
        foreach (self::semuaInstrumen() as $def) {
            foreach ($def['item'] as $item) {
                if (trim((string) ($input[$item['var']] ?? '')) !== '') {
                    return $def['kode'];
                }
            }
        }

        return null;
    }

    /** Kode instrumen yang valid, atau null bila tidak dikenal. */
    public static function normalisasiKode(?string $kode): ?string
    {
        $kode = strtoupper(trim((string) $kode));

        return in_array($kode, ['HDS', 'MFS', 'TUG', 'SYDNEY'], true) ? $kode : null;
    }

    /** Hitung usia pasien (tahun) dari tanggal lahir. */
    public static function hitungUsia(?string $tglLahir): ?int
    {
        if (! $tglLahir) {
            return null;
        }

        try {
            return Carbon::parse($tglLahir)->diffInYears(now());
        } catch (\Exception $e) {
            return null;
        }
    }

    // =====================================================================
    // PERHITUNGAN
    // =====================================================================

    /**
     * Hitung skor dan tingkat risiko dari jawaban mentah.
     *
     * @return array{
     *     instrumen:?string, skor:?float, label:string, warna:string,
     *     intervensi:?string, kategori_index:?int, lengkap:bool,
     *     norma:?array, item_terisi:int, item_total:int
     * }
     */
    public static function hitung(?string $kode, array $input, ?int $usia = null): array
    {
        $kode = self::normalisasiKode($kode);
        $def = $kode ? self::definisi($kode) : null;

        $hasil = [
            'instrumen' => $kode,
            'skor' => null,
            'label' => 'Belum Dinilai',
            'warna' => 'slate',
            'intervensi' => null,
            'kategori_index' => null,
            'lengkap' => false,
            'norma' => null,
            'item_terisi' => 0,
            'item_total' => 0,
        ];

        if (! $def) {
            return $hasil;
        }

        $hasil['item_total'] = count($def['item']);
        $total = 0.0;
        $terisi = 0;

        foreach ($def['item'] as $item) {
            $nilai = trim((string) ($input[$item['var']] ?? ''));

            if ($nilai === '') {
                continue;
            }

            $terisi++;

            if (isset($item['bobot_ya'])) {
                // HDS: jawaban Ya/Tidak, skor hanya dari jawaban Ya.
                $total += $nilai === 'Ya' ? (float) $item['bobot_ya'] : 0.0;
            } elseif (isset($item['opsi'])) {
                $total += (float) ($item['opsi'][$nilai] ?? 0);
            } else {
                $total += (float) $nilai;
            }
        }

        $hasil['item_terisi'] = $terisi;
        $hasil['lengkap'] = $terisi > 0;

        if (! $hasil['lengkap']) {
            return $hasil;
        }

        if ($kode === 'SYDNEY') {
            $total = self::skorSydney($def, $input);
        }

        if ($kode === 'HDS') {
            $total = min((float) $def['skor_maks'], (float) $def['skor_dasar'] + $total);
        }

        $hasil['skor'] = $kode === 'TUG' ? round($total, 1) : (int) $total;

        if ($kode === 'TUG') {
            $index = self::kategoriTug((float) $total, $usia);
        } else {
            $index = 0;
            foreach ($def['kategori'] as $i => $cat) {
                if ($total <= $cat['maks']) {
                    $index = $i;
                    break;
                }
            }
        }

        $cat = $def['kategori'][$index];
        $hasil['kategori_index'] = $index;
        $hasil['label'] = $cat['label'];
        $hasil['warna'] = $cat['warna'];
        $hasil['intervensi'] = $cat['intervensi'];

        if ($kode === 'TUG') {
            $hasil['norma'] = self::normaTug($usia);
        }

        return $hasil;
    }

    /**
     * Kategori TUG: bandingkan durasi dengan batas atas norma usia.
     * 30 detik ke atas = risiko tinggi (butuh bantuan).
     */
    private static function kategoriTug(float $detik, ?int $usia): int
    {
        if ($detik >= 30) {
            return 2;
        }

        // Bandingkan dengan batas atas 95% CI (mis. 10,2 dtk untuk usia 70-79),
        // bukan dengan nilai bulat yang sudah dibulatkan ke atas.
        $batas = self::normaTug($usia)['batas'];

        if ($batas === null) {
            return 1;
        }

        return $detik <= $batas ? 0 : 1;
    }

    /**
     * Norma TUG per kelompok usia (mean dan batas atas 95% CI).
     * Bohannon RW 2006 - meta-analisis 21 studi pada populasi sehat.
     *
     * @return array{nilai:?int, batas:?float, rata:?float, label:string}
     */
    public static function normaTug(?int $usia): array
    {
        $usia = (int) $usia;

        $tabel = [
            ['min' => 60, 'max' => 69, 'rata' => 8.1, 'batas' => 9.0],
            ['min' => 70, 'max' => 79, 'rata' => 9.2, 'batas' => 10.2],
            ['min' => 80, 'max' => 200, 'rata' => 11.3, 'batas' => 12.7],
        ];

        foreach ($tabel as $n) {
            if ($usia >= $n['min'] && $usia <= $n['max']) {
                return [
                    'nilai' => (int) ceil($n['batas']),
                    'batas' => $n['batas'],
                    'rata' => $n['rata'],
                    'label' => $n['min'].'-'.$n['max'].' tahun',
                ];
            }
        }

        if ($usia > 0) {
            return ['nilai' => 12, 'batas' => 12.0, 'rata' => null, 'label' => 'Di bawah 60 tahun (tanpa norma khusus)'];
        }

        return ['nilai' => null, 'batas' => null, 'rata' => null, 'label' => 'Usia tidak diketahui'];
    }

    /**
     * Sydney: TS + MS -> 0-2 menjadi 0, 3-6 menjadi 7, 7 ke atas dijumlahkan.
     */
    private static function skorSydney(array $def, array $input): float
    {
        $skorItem = function (string $var) use ($def, $input): float {
            foreach ($def['item'] as $item) {
                if ($item['var'] === $var) {
                    $jawab = trim((string) ($input[$var] ?? ''));

                    return (float) ($item['opsi'][$jawab] ?? 0);
                }
            }

            return 0.0;
        };

        $ts = $skorItem('syd_ts_bed') + $skorItem('syd_ts_bangku') + $skorItem('syd_ts_bantuan');
        $ms = $skorItem('syd_ms_bantuan') + $skorItem('syd_ms_kursi_roda') + $skorItem('syd_ms_imobil');
        $jumlah = $ts + $ms;

        return $jumlah <= 2 ? 0.0 : ($jumlah <= 6 ? 7.0 : $jumlah);
    }

    /** Ringkasan satu baris untuk disimpan ke emr_detail. */
    public static function ringkasan(array $hasil): string
    {
        if (! $hasil['lengkap'] || $hasil['skor'] === null) {
            return '';
        }

        $teks = $hasil['skor'].' - '.$hasil['label'];

        if (! empty($hasil['norma']['nilai'])) {
            $teks .= ' (norma '.$hasil['norma']['label'].': maksimal '.$hasil['norma']['nilai'].' detik)';
        }

        return $teks;
    }
}
