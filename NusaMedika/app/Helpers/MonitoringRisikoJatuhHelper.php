<?php

namespace App\Helpers;

/**
 * Pusat logika Monitoring & Evaluasi Risiko Jatuh (form 30).
 *
 * Form ini bukan instrumen baru: ia memakai ulang seluruh instrumen pengkajian
 * risiko jatuh milik `RisikoJatuhHelper` (HDS / MFS / TUG / Sydney, objek
 * 89-113) supaya laporan risiko jatuh bisa menjumlahkan dan membandingkan antar
 * waktu, lalu menambah bagian yang belum ada di mana pun:
 *
 *   - tanggal & waktu monitoring (per shift),
 *   - status monitoring (Membaik / Tetap / Memburuk) yang diturunkan dari
 *     perbandingan skor dengan penilaian sebelumnya,
 *   - Ada / tidak adanya kejadian jatuh beserta waktu, keterangan, dan
 *     tindak lanjutnya,
 *   - checklist 6 butir intervensi pencegahan jatuh.
 *
 * ARAH SKOR risiko jatuh: skor TINGGI berarti risiko TINGGI (berlawanan dengan
 * Norton & Barthel). Karena itu `statusMonitoring()` membandingkan angka skor
 * apa adanya: skor turun = Membaik, skor naik = Memburuk.
 *
 * Semua skor, label, intervensi, dan ringkasan dihitung ulang di server lewat
 * `RisikoJatuhHelper::hitung()`; nilai yang dikirim browser untuk
 * `risiko_jatuh_skor`, `risiko_jatuh_label`, `risiko_jatuh_intervensi`,
 * `risiko_jatuh_ringkasan`, dan `status_monitoring` dibuang tanpa dibacakan.
 *
 * R3 doc: objek 92 dipakai dua variabel pada form ini
 * (`risiko_jatuh_intervensi` teks turunan, `intervensi_pencegahan` JSON
 * checklist). Aman untuk `emrDetailByVariabel()`, BERBAHAYA untuk
 * `emrDetailByObjek()` - jangan pakai yang kedua untuk form 30.
 */
class MonitoringRisikoJatuhHelper
{
    /**
     * Semua item instrumen risiko jatuh yang dipakai form 30, keyed by variabel
     * di emr_detail.
     *
     * Bentuk tiap entri: ['label' => ..., 'instrumen' => ..., 'opsi' => [...]]
     * dengan 'opsi' berisi [['value' => ..., 'label' => ...], ...].
     *
     * Daftar ini DITURUNKAN dari `RisikoJatuhHelper` sehingga hanya ada satu
     * sumber kebenaran untuk label dan opsi (tidak ada duplikasi tabel skoring).
     * Item TUG (`tug_detik`) tidak punya opsi karena berupa angka.
     */
    public static function items(): array
    {
        $items = [];

        foreach (RisikoJatuhHelper::semuaInstrumen() as $def) {
            foreach ($def['item'] as $item) {
                $opsi = [];

                if (isset($item['opsi'])) {
                    foreach ($item['opsi'] as $label => $skor) {
                        $opsi[] = ['value' => (string) $label, 'label' => $label.' ('.$skor.')'];
                    }
                } elseif (isset($item['bobot_ya'])) {
                    // HDS: jawaban Ya/Tidak, skor hanya dari jawaban Ya.
                    $opsi[] = ['value' => 'Tidak', 'label' => 'Tidak (0)'];
                    $opsi[] = ['value' => 'Ya', 'label' => 'Ya ('.$item['bobot_ya'].')'];
                }

                if ($opsi === []) {
                    continue;
                }

                $items[$item['var']] = [
                    'label' => $item['label'],
                    'instrumen' => $def['kode'],
                    'opsi' => $opsi,
                ];
            }
        }

        return $items;
    }

    /**
     * Nilai yang sah untuk sebuah item instrumen (daftar teks jawaban).
     *
     * @return array<int, string>
     */
    public static function opsiItem(string $variabel): array
    {
        $items = self::items();

        return array_column($items[$variabel]['opsi'] ?? [], 'value');
    }

    /**
     * Kode instrumen yang benar-benar dipakai. Nilai `risiko_jatuh_instrumen`
     * dari browser dipakai HANYA bila valid; kalau tidak, `deteksiInstrumen()`
     * mencari jawaban item yang ikut terkirim (R9 doc).
     */
    public static function instrumenAktif(?string $kode, array $data): ?string
    {
        return RisikoJatuhHelper::normalisasiKode($kode) ?: RisikoJatuhHelper::deteksiInstrumen($data);
    }

    /**
     * Hitung skor, label, intervensi, dan ringkasan untuk satu monitoring.
     *
     * @return array{
     *     instrumen:?string, skor:?int|float, label:string, warna:string,
     *     intervensi:?string, ringkasan:string, lengkap:bool,
     *     item_terisi:int, item_total:int, norma:?array
     * }
     */
    public static function hitung(?string $kode, array $data, ?int $usia = null): array
    {
        $kode = self::instrumenAktif($kode, $data);

        $hasil = RisikoJatuhHelper::hitung($kode, $data, $usia);

        return [
            'instrumen' => $hasil['instrumen'],
            'skor' => $hasil['skor'],
            'label' => $hasil['label'],
            'warna' => $hasil['warna'],
            'intervensi' => $hasil['intervensi'],
            'ringkasan' => RisikoJatuhHelper::ringkasan($hasil),
            'lengkap' => $hasil['lengkap'],
            'item_terisi' => $hasil['item_terisi'],
            'item_total' => $hasil['item_total'],
            'norma' => $hasil['norma'],
        ];
    }

    /**
     * 6 butir checklist intervensi pencegahan jatuh (objek 92, disimpan sebagai
     * JSON array). Sumber tunggal: `SelectOption::get('intervensi_pencegahan_jatuh')`.
     */
    public static function intervensiPencegahan(): array
    {
        return SelectOption::get('intervensi_pencegahan_jatuh');
    }

    /**
     * Status monitoring hasil perbandingan dengan penilaian sebelumnya.
     *
     * Arahnya mengikuti skor risiko jatuh: skor TINGGI = risiko TINGGI, jadi
     * skor yang TURUN berarti Membaik. `null` bila tidak ada pembanding atau
     * skor yang sedang disimpan belum lengkap.
     *
     * @param  float|int|string|null  $sebelum  skor penilaian sebelumnya
     * @param  float|int|string|null  $sekarang  skor penilaian yang sedang disimpan
     */
    public static function statusMonitoring($sebelum, $sekarang): ?string
    {
        if ($sebelum === null || $sebelum === '' || $sekarang === null || $sekarang === '') {
            return null;
        }

        $sebelum = (float) $sebelum;
        $sekarang = (float) $sekarang;

        return match (true) {
            $sekarang < $sebelum => 'Membaik',
            $sekarang > $sebelum => 'Memburuk',
            default => 'Tetap',
        };
    }

    /**
     * Label interpretatif status monitoring, lengkap dengan skor pembanding -
     * dipakai untuk menampilkan rincian perbandingan di riwayat.
     */
    public static function interpretasiStatus($sebelum, $sekarang): string
    {
        $status = self::statusMonitoring($sebelum, $sekarang);

        if ($status === null) {
            return 'Belum ada penilaian sebelumnya untuk dibandingkan';
        }

        return $status.' (skor '.self::formatSkor($sekarang).' dari '.self::formatSkor($sebelum).')';
    }

    /** Format skor untuk ditampilkan (pembulatan agar 7,0 tidak jadi 7.00000). */
    private static function formatSkor($skor): string
    {
        $skor = (float) $skor;

        return floor($skor) === $skor
            ? (string) (int) $skor
            : rtrim(rtrim(number_format($skor, 1, ',', '.'), '0'), ',');
    }
}
