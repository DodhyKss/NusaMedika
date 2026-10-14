<?php

namespace App\Helpers;

use App\Models\RegistrasiDetail;

/**
 * Pusat agregasi Medical Check Up (dipakai form 70 "Kesimpulan MCU").
 *
 * Satu episode MCU dihimpun dari 14 form terpisah: 71 TTV, lalu 72–84 organ
 * spesialis. Form 70 membaca EMR **terakhir** tiap form pada
 * `registrasi_detail` yang sama lalu meringkasnya menjadi tabel temuan.
 *
 * PENTING — prefill `interpretasi` hanya TEKS (pola `SbarController`):
 * nilai yang tersimpan di `emr_detail` selalu menang, `old()` menang atas
 * keduanya.-doctor mengedit teks prefill; tidak ada kolom turunan yang
 * dihitung ulang di server untuk field ini.
 */
class McuHelper
{
    /**
     * Daftar form MCU beserta label panel & variabel ringkasannya.
     *
     * `ringkas` sengaja hanya berisi variabel yang mewakili "apa yang
     * ditanyakan pasien" per organ — bukan seluruh isi form.
     *
     * @return array<string, array{label: string, ringkas: array<int, string>}>
     */
    public const FORMS_MCU = [
        'ttv_mcu' => ['label' => 'Tanda Vital & Anamnesis', 'ringkas' => ['sistolik', 'diastolik', 'nadi', 'suhu']],
        'visus_mata_mcu' => ['label' => 'Visus Mata', 'ringkas' => ['visus_od_avod', 'visus_os_avos', 'buta_warna']],
        'obstetri_mcu' => ['label' => 'Obstetri', 'ringkas' => ['kehamilan_g', 'kehamilan_p', 'kehamilan_a', 'kb_metode']],
        'penyakit_dalam_mcu' => ['label' => 'Penyakit Dalam', 'ringkas' => ['diagnosa']],
        'jantung_mcu' => ['label' => 'Jantung & EKG', 'ringkas' => ['ekg_hr', 'ekg_kesimpulan']],
        'tht_mcu' => ['label' => 'THT', 'ringkas' => ['diagnosa']],
        'mata_mcu' => ['label' => 'Mata', 'ringkas' => ['diagnosa']],
        'gigi_mcu' => ['label' => 'Gigi', 'ringkas' => ['diagnosa', 'kebutuhan_perawatan']],
        'obgyn_mcu' => ['label' => 'OBGYN', 'ringkas' => ['diagnosa']],
        'bedah_mcu' => ['label' => 'Bedah', 'ringkas' => ['diagnosa']],
        'neurologi_mcu' => ['label' => 'Neurologi', 'ringkas' => ['diagnosa']],
        'gizi_mcu' => ['label' => 'Gizi', 'ringkas' => ['status_gizi', 'risiko_gizi']],
        'urologi_mcu' => ['label' => 'Urologi', 'ringkas' => ['diagnosa']],
        'psikologi_mcu' => ['label' => 'Psikologi', 'ringkas' => ['interpretasi', 'saran']],
    ];

    /**
     * Slug form MCU yang_menu panel "Kesimpulan MCU" sendiri.
     */
    public const SLUG_KESIMPULAN = 'kesimpulan_mcu';

    /**
     * `form_id` seluruh form MCU, keyed slug. Slug yang belum ada di DB dilewati.
     *
     * @return array<string, int>
     */
    public static function formIdsOrgan(): array
    {
        $ids = [];
        foreach (array_keys(self::FORMS_MCU) as $slug) {
            $formId = EmrHelper::formIdBySlug($slug);
            if ($formId) {
                $ids[$slug] = (int) $formId;
            }
        }

        return $ids;
    }

    /**
     * Ringkasan temuan seluruh form MCU pada satu kunjungan.
     *
     * Setiap baris: `['slug', 'form_id', 'organ', 'temuan', 'terisi']`.
     * `terisi` false berarti form organ itu belum pernah diisi pada kunjungan
     * ini — dipakai badge "Belum diperiksa" di view.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function ringkasan(RegistrasiDetail $registrasiDetail): array
    {
        $registrasiId = (int) $registrasiDetail->registrasi_id;
        $baris = [];

        foreach (self::FORMS_MCU as $slug => $meta) {
            $formId = EmrHelper::formIdBySlug($slug);
            if (! $formId) {
                continue;
            }

            $nilai = EmrHelper::latestValuesByVariabel((int) $formId, $registrasiId, $meta['ringkas']);
            $terisi = false;
            $potongan = [];

            foreach ($meta['ringkas'] as $variabel) {
                $ada = trim((string) ($nilai[$variabel] ?? ''));
                if ($ada === '') {
                    continue;
                }
                $terisi = true;
                $potongan[] = $ada;
            }

            $baris[] = [
                'slug' => $slug,
                'form_id' => (int) $formId,
                'organ' => $meta['label'],
                'temuan' => implode(' · ', $potongan),
                'terisi' => $terisi,
            ];
        }

        return $baris;
    }

    /**
     * Daftar organ yang BELUM pernah diisi pada kunjungan ini.
     *
     * @param  array<int, array<string, mixed>>  $ringkasan  keluaran `ringkasan()`
     * @return array<int, string> label organ
     */
    public static function organBelumDiperiksa(array $ringkasan): array
    {
        $belum = [];
        foreach ($ringkasan as $baris) {
            if (! $baris['terisi']) {
                $belum[] = $baris['organ'];
            }
        }

        return $belum;
    }

    /**
     * Teks interpretasi yang otomatis disusun dari daftar temuan.
     *
     * Hanya PREFILL — bila EMR sudah punya `interpretasi` tersimpan, nilai itu
     * yang dipakai. Panjang teks dibatasi 5000 (batas kolom `emr_detail`).
     */
    public static function interpretasiOtomatis(array $ringkasan): string
    {
        $temuan = [];
        foreach ($ringkasan as $baris) {
            if (! $baris['terisi']) {
                continue; // organ kosong dianggap "tidak menemukan kelainan"
            }
            $teks = trim((string) $baris['temuan']);
            if ($teks === '' || stripos($teks, 'normal') !== false) {
                continue;
            }
            $temuan[] = '- '.$baris['organ'].': '.$teks;
        }

        if ($temuan === []) {
            return 'Hasil pemeriksaan fisik, pemeriksaan penunjang dan laboratorium dalam batas normal.';
        }

        return "Hasil pemeriksaan fisik, pemeriksaan penunjang dan laboratorium dalam batas normal, kecuali:\n"
            .implode("\n", $temuan);
    }

    /**
     * Jumlah organ dengan temuan yang TIDAK normal.
     *
     * Dipakai untuk peringatan (bukan pemblokiran) di form 70.
     */
    public static function jumlahTemuanTidakNormal(array $ringkasan): int
    {
        $jumlah = 0;
        foreach ($ringkasan as $baris) {
            if (! $baris['terisi']) {
                continue;
            }
            $teks = trim((string) $baris['temuan']);
            if ($teks !== '' && stripos($teks, 'normal') === false) {
                $jumlah++;
            }
        }

        return $jumlah;
    }

    /**
     * Kategori kesimpulan MCU (verbatim legacy — menjadi isi surat, tidak boleh diubah).
     *
     * @return array<int, string> nilai => label
     */
    public static function kategoriKesimpulan(): array
    {
        return [
            1 => 'SEHAT',
            2 => 'SEHAT Dengan Catatan Medis',
            3 => 'TIDAK SEHAT Sementara Waktu Hingga Pemeriksaan Berikutnya',
            4 => 'TIDAK SEHAT',
        ];
    }
}
