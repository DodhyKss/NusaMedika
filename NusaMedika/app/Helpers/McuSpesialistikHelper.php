<?php

namespace App\Helpers;

/**
 * Pusat definisi parameter per organ untuk form Medical Check Up (form 72–84).
 *
 * Empat belas form spesialis MCU (72 Visus Mata, 73 Obstetri, 74 Penyakit Dalam,
 * 75 Jantung, 76 THT, 77 Mata, 78 Gigi, 79 OBGYN, 80 Bedah, 81 Neurologi,
 * 82 Gizi, 83 Urologi, 84 Psikologi) memakai satu pola seragam. Parameter yang
 * mengikuti pola **"Tidak Ada / Ada" + uraian** dikumpulkan di sini supaya
 * tidak diduplikasi di 14 controller; sisanya (field bebas seperti EKG,
 * siklus haid, skala cemas) tetap per-form.
 *
 * POLA "TIDAK ADA / ADA" ALA LEGACY (docs/KONSEP_FORM_MCU.md §3.4):
 * - `<kunci>_ada` = '0' → field uraian kosong dan **dibuang** di `filteredData()`.
 * - `<kunci>_ada` = '1' → field uraian **wajib** diisi.
 *
 * Daftar `kunci` di sini WAJIB cocok dengan nama variabel di `objek_form_control`
 * (lihat `tools/e2e.php`, yang memverifikasi seluruhnya tersimpan).
 */
class McuSpesialistikHelper
{
    /**
     * Parameter berpola `_ada` per form spesialis MCU.
     *
     * @return array<string, array{judul: string, organ: array<int, array{kunci: string, label: string, normal: string}>}>
     */
    private const ORGAN = [
        // --- Form 74 Penyakit Dalam MCU: 15 parameter inspasial.
        'penyakit_dalam_mcu' => [
            'judul' => 'PEMERIKSAAN PENYAKIT DALAM',
            'organ' => [
                ['kunci' => 'kepala', 'label' => 'Kepala'],
                ['kunci' => 'kulit', 'label' => 'Kulit'],
                ['kunci' => 'mata', 'label' => 'Mata'],
                ['kunci' => 'mulut', 'label' => 'Mulut'],
                ['kunci' => 'hidung', 'label' => 'Hidung'],
                ['kunci' => 'tenggorokan', 'label' => 'Tenggorokan'],
                ['kunci' => 'telinga', 'label' => 'Telinga'],
                ['kunci' => 'leher', 'label' => 'Leher'],
                ['kunci' => 'mamae', 'label' => 'Mamae'],
                ['kunci' => 'thorax', 'label' => 'Thorax'],
                ['kunci' => 'jantung', 'label' => 'Jantung'],
                ['kunci' => 'paru', 'label' => 'Paru'],
                ['kunci' => 'abdomen', 'label' => 'Abdomen'],
                ['kunci' => 'ekstremitas', 'label' => 'Ekstremitas'],
                ['kunci' => 'lain', 'label' => 'Lainnya'],
            ],
        ],

        // --- Form 75 Jantung MCU.
        'jantung_mcu' => [
            'judul' => 'PEMERIKSAAN JANTUNG',
            'organ' => [
                ['kunci' => 'jantung', 'label' => 'Pemeriksaan Jantung'],
            ],
        ],

        // --- Form 76 THT MCU.
        'tht_mcu' => [
            'judul' => 'PEMERIKSAAN THT',
            'organ' => [
                ['kunci' => 'telinga_kanan', 'label' => 'Telinga Kanan'],
                ['kunci' => 'telinga_kiri', 'label' => 'Telinga Kiri'],
                ['kunci' => 'hidung', 'label' => 'Hidung'],
                ['kunci' => 'tenggorokan', 'label' => 'Tenggorokan'],
                ['kunci' => 'tht_lainnya', 'label' => 'Kelainan THT Lainnya'],
            ],
        ],

        // --- Form 77 Mata MCU.
        'mata_mcu' => [
            'judul' => 'PEMERIKSAAN MATA',
            'organ' => [
                ['kunci' => 'mata_kanan', 'label' => 'Mata Kanan'],
                ['kunci' => 'mata_kiri', 'label' => 'Mata Kiri'],
                ['kunci' => 'kelainan_mata_lainnya', 'label' => 'Kelainan Mata Lainnya'],
            ],
        ],

        // --- Form 78 Gigi MCU.
        'gigi_mcu' => [
            'judul' => 'PEMERIKSAAN GIGI',
            'organ' => [
                ['kunci' => 'gigi', 'label' => 'Gigi Geligi'],
                ['kunci' => 'kebutuhan_perawatan', 'label' => 'Kebutuhan Perawatan Gigi'],
            ],
        ],

        // --- Form 79 OBGYN & 80 Bedah: keduanya memakai kunci `lokalis`,
        //     TAPI objek & label berbeda — tetap berada di tabel terpisah.
        'obgyn_mcu' => [
            'judul' => 'PEMERIKSAAN OBGYN',
            'organ' => [
                ['kunci' => 'lokalis', 'label' => 'Pemeriksaan Lokalis'],
            ],
        ],
        'bedah_mcu' => [
            'judul' => 'PEMERIKSAAN BEDAH',
            'organ' => [
                ['kunci' => 'lokalis', 'label' => 'Pemeriksaan Localis'],
            ],
        ],

        // --- Form 81 Neurologi MCU.
        'neurologi_mcu' => [
            'judul' => 'PEMERIKSAAN NEUROLOGI',
            'organ' => [
                ['kunci' => 'neurology', 'label' => 'Pemeriksaan Neurologi'],
            ],
        ],

        // --- Form 83 Urologi MCU.
        'urologi_mcu' => [
            'judul' => 'PEMERIKSAAN UROLOGI',
            'organ' => [
                ['kunci' => 'fisik', 'label' => 'Pemeriksaan Fisik'],
            ],
        ],
    ];

    /**
     * Slug form MCU spesialis yang TIDAK memakai pola `_ada`, sehingga tidak
     * bisa dilayani `McuSpesialistikController` dan butuh controller sendiri.
     *
     * - 72 `visus_mata_mcu`  : field visus & konfrontasi, tanpa diagnosa/rekomendasi.
     * - 73 `obstetri_mcu`    : field riwayat & ginekologi, tanpa diagnosa/rekomendasi.
     * - 82 `gizi_mcu`        : objektif gizi + status gizi/diet (tanpa `_ada`).
     * - 84 `psikologi_mcu`   : gangguan kehidupan + skala cemas/depresi,
     *                         memakai `interpretasi`/`saran`, bukan diagnosa/rekomendasi.
     *
     * @var array<int, string>
     */
    public const FORM_BESPOKE = ['visus_mata_mcu', 'obstetri_mcu', 'gizi_mcu', 'psikologi_mcu'];

    /**
     * @return array<int, string>
     */
    public static function slugs(): array
    {
        return array_keys(self::ORGAN);
    }

    /**
     * Definisi lengkap untuk sebuah form spesialis (judul + organ).
     */
    public static function definisi(string $slug): array
    {
        return self::ORGAN[$slug] ?? ['judul' => '', 'organ' => []];
    }

    /**
     * Daftar parameter organ berpola `_ada` untuk sebuah form.
     *
     * @return array<int, array{kunci: string, label: string}>
     */
    public static function organ(string $slug): array
    {
        return self::definisi($slug)['organ'];
    }

    /**
     * Normalisasi nilai `<kunci>_ada` menjadi '0' / '1'.
     *
     * Radio HTML mengirim string; form lama bisa menyimpan null (artinya tidak
     * ada kelainan) — semuanya dinormalkan ke '0'.
     */
    public static function normalisasiAda($nilai): string
    {
        return in_array((string) $nilai, ['1', 'on', 'Ya', 'true'], true) ? '1' : '0';
    }
}
