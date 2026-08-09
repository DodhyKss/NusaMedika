<?php

namespace App\Helpers;

class SelectOption
{
    /**
     * Semua daftar pilihan dropdown yang tidak tersimpan di database.
     * Format tiap item: ['value' => ..., 'label' => ..., 'class' => ...?].
     */
    public static function all(): array
    {
        return [
            'jenis_kelamin' => [
                ['value' => 'L', 'label' => 'Laki-laki'],
                ['value' => 'P', 'label' => 'Perempuan'],
            ],
            'agama' => [
                ['value' => 'Islam', 'label' => 'Islam'],
                ['value' => 'Kristen', 'label' => 'Kristen Protestan'],
                ['value' => 'Katolik', 'label' => 'Katolik'],
                ['value' => 'Hindu', 'label' => 'Hindu'],
                ['value' => 'Buddha', 'label' => 'Buddha'],
                ['value' => 'Konghucu', 'label' => 'Konghucu'],
            ],
            'golongan_darah' => [
                ['value' => 'A', 'label' => 'A'],
                ['value' => 'B', 'label' => 'B'],
                ['value' => 'AB', 'label' => 'AB'],
                ['value' => 'O', 'label' => 'O'],
            ],
            'status_perkawinan' => [
                ['value' => 'Belum Kawin', 'label' => 'Belum Kawin'],
                ['value' => 'Kawin', 'label' => 'Kawin'],
                ['value' => 'Cerai Hidup', 'label' => 'Cerai Hidup'],
                ['value' => 'Cerai Mati', 'label' => 'Cerai Mati'],
            ],
            'kebangsaan' => [
                ['value' => 'WNI', 'label' => 'Warga Negara Indonesia (WNI)'],
                ['value' => 'WNA', 'label' => 'Warga Negara Asing (WNA)'],
            ],
            'suku' => [
                ['value' => 'Jawa', 'label' => 'Jawa'],
                ['value' => 'Sunda', 'label' => 'Sunda'],
                ['value' => 'Batak', 'label' => 'Batak'],
                ['value' => 'Minangkabau', 'label' => 'Minangkabau'],
                ['value' => 'Betawi', 'label' => 'Betawi'],
                ['value' => 'Bugis', 'label' => 'Bugis'],
                ['value' => 'Melayu', 'label' => 'Melayu'],
                ['value' => 'Madura', 'label' => 'Madura'],
                ['value' => 'Bali', 'label' => 'Bali'],
                ['value' => 'Aceh', 'label' => 'Aceh'],
                ['value' => 'Banjar', 'label' => 'Banjar'],
                ['value' => 'Dayak', 'label' => 'Dayak'],
                ['value' => 'Papua', 'label' => 'Papua'],
                ['value' => 'Tionghoa', 'label' => 'Tionghoa'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'pendidikan' => [
                ['value' => 'Tidak Sekolah', 'label' => 'Tidak / Belum Sekolah'],
                ['value' => 'SD', 'label' => 'SD / Sederajat'],
                ['value' => 'SMP', 'label' => 'SMP / Sederajat'],
                ['value' => 'SMA', 'label' => 'SMA / Sederajat'],
                ['value' => 'D1', 'label' => 'D1'],
                ['value' => 'D2', 'label' => 'D2'],
                ['value' => 'D3', 'label' => 'D3'],
                ['value' => 'D4/S1', 'label' => 'D4 / S1'],
                ['value' => 'S2', 'label' => 'S2'],
                ['value' => 'S3', 'label' => 'S3'],
            ],
            'pekerjaan' => [
                ['value' => 'Pelajar/Mahasiswa', 'label' => 'Pelajar / Mahasiswa'],
                ['value' => 'Ibu Rumah Tangga', 'label' => 'Ibu Rumah Tangga'],
                ['value' => 'PNS', 'label' => 'PNS'],
                ['value' => 'TNI/POLRI', 'label' => 'TNI / POLRI'],
                ['value' => 'Karyawan Swasta', 'label' => 'Karyawan Swasta'],
                ['value' => 'Wiraswasta', 'label' => 'Wiraswasta'],
                ['value' => 'Petani', 'label' => 'Petani'],
                ['value' => 'Nelayan', 'label' => 'Nelayan'],
                ['value' => 'Buruh', 'label' => 'Buruh'],
                ['value' => 'Pensiunan', 'label' => 'Pensiunan'],
                ['value' => 'Tidak Bekerja', 'label' => 'Tidak Bekerja'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'disabilitas' => [
                ['value' => 'Tidak Ada', 'label' => 'Tidak Ada'],
                ['value' => 'Netra', 'label' => 'Netra (Buta)'],
                ['value' => 'Rungu', 'label' => 'Rungu (Tuli)'],
                ['value' => 'Wicara', 'label' => 'Wicara (Bisu)'],
                ['value' => 'Rungu-Wicara', 'label' => 'Rungu-Wicara (Tuli-Bisu)'],
                ['value' => 'Fisik', 'label' => 'Fisik'],
                ['value' => 'Mental', 'label' => 'Mental'],
                ['value' => 'Grahita', 'label' => 'Grahita (Intelektual)'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'status_kepegawaian' => [
                ['value' => '1', 'label' => 'PNS'],
                ['value' => '2', 'label' => 'PPPK'],
                ['value' => '3', 'label' => 'Kontrak'],
                ['value' => '4', 'label' => 'Honorer'],
            ],
            'triase_igd' => [
                ['value' => 'Merah', 'label' => 'Level 1 - Resusitasi (Merah)', 'class' => 'text-red-600'],
                ['value' => 'Kuning', 'label' => 'Level 2 - Gawat Darurat (Kuning)', 'class' => 'text-amber-500'],
                ['value' => 'Hijau', 'label' => 'Level 3 - Darurat Tidak Gawat (Hijau)', 'class' => 'text-emerald-500'],
                ['value' => 'Hitam', 'label' => 'Level 4 - Meninggal (Hitam)', 'class' => 'text-slate-800'],
            ],
            'triase_igd_obgyn' => [
                ['value' => 'Merah', 'label' => 'Level 1 - Resusitasi / Kedaruratan Mengancam Jiwa (Merah)', 'class' => 'text-red-600'],
                ['value' => 'Kuning', 'label' => 'Level 2 - Gawat Darurat Maternal/Neonatal (Kuning)', 'class' => 'text-amber-500'],
                ['value' => 'Hijau', 'label' => 'Level 3 - Darurat Tidak Gawat (Hijau)', 'class' => 'text-emerald-500'],
            ],
            'cara_masuk_igd' => [
                ['value' => 'Datang Sendiri', 'label' => 'Datang Sendiri / Keluarga'],
                ['value' => 'Diantar Ambulans', 'label' => 'Diantar Ambulans'],
                ['value' => 'Diantar Polisi', 'label' => 'Diantar Polisi'],
                ['value' => 'Rujukan Faskes', 'label' => 'Rujukan RS / Puskesmas Lain'],
            ],
            'hubungan_penanggung' => [
                ['value' => 'Keluarga Inti', 'label' => 'Keluarga Inti'],
                ['value' => 'Kerabat', 'label' => 'Kerabat / Teman'],
                ['value' => 'Tetangga', 'label' => 'Tetangga / Warga'],
                ['value' => 'Petugas', 'label' => 'Petugas (Polisi/Ambulans)'],
                ['value' => 'Tidak Diketahui', 'label' => 'Tidak Diketahui'],
            ],
            'indikasi_igd_obgyn' => [
                ['value' => 'Inpartu', 'label' => 'Inpartu (Akan Melahirkan)'],
                ['value' => 'Pendarahan', 'label' => 'Pendarahan Hamil Muda / Tua'],
                ['value' => 'KPD', 'label' => 'Ketuban Pecah Dini (KPD)'],
                ['value' => 'Pre-eklampsia', 'label' => 'Pre-eklampsia / Kejang'],
                ['value' => 'Ginekologi', 'label' => 'Kasus Ginekologi Lainnya'],
            ],
            'hubungan_penanggung_obgyn' => [
                ['value' => 'Suami', 'label' => 'Suami'],
                ['value' => 'Orang Tua', 'label' => 'Orang Tua'],
                ['value' => 'Keluarga Lain', 'label' => 'Keluarga Lainnya'],
                ['value' => 'Bidan', 'label' => 'Bidan Pendamping'],
            ],
            'asal_pasien_ranap' => [
                ['value' => 'IGD', 'label' => 'Instalasi Gawat Darurat (IGD)'],
                ['value' => 'Poliklinik', 'label' => 'Poliklinik (Rawat Jalan)'],
                ['value' => 'Rujukan Luar', 'label' => 'Rujukan RS/Klinik Lain'],
            ],
            'hubungan_keluarga_ranap' => [
                ['value' => 'Suami', 'label' => 'Suami'],
                ['value' => 'Istri', 'label' => 'Istri'],
                ['value' => 'Anak', 'label' => 'Anak'],
                ['value' => 'Orang Tua', 'label' => 'Orang Tua'],
                ['value' => 'Saudara Kandung', 'label' => 'Saudara Kandung'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'kategori_icd' => [
                ['value' => 'ICD-10', 'label' => 'ICD-10'],
                ['value' => 'ICD-9', 'label' => 'ICD-9'],
            ],
        ];
    }

    /**
     * Mengambil daftar pilihan untuk sebuah key.
     */
    public static function get(string $key): array
    {
        return static::all()[$key] ?? [];
    }

    /**
     * Merender tag <option> untuk sebuah key.
     *
     * @param  string|int|null  $selected
     */
    public static function render(string $key, $selected = null, ?string $placeholder = null): string
    {
        $html = '';

        if ($placeholder !== null) {
            $html .= '<option value="">'.e($placeholder).'</option>';
        }

        foreach (static::get($key) as $option) {
            $selectedAttr = $selected !== null && (string) $option['value'] === (string) $selected ? ' selected' : '';
            $classAttr = isset($option['class']) ? ' class="'.e($option['class']).'"' : '';

            $html .= '<option value="'.e($option['value']).'"'.$classAttr.$selectedAttr.'>'.e($option['label']).'</option>';
        }

        return $html;
    }
}
