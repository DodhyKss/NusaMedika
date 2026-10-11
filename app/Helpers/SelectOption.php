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
            'cara_masuk' => [
                ['value' => 'Berjalan Sendiri', 'label' => 'Berjalan Sendiri'],
                ['value' => 'Dengan Kursi Roda', 'label' => 'Dengan Kursi Roda'],
                ['value' => 'Dengan Brankar', 'label' => 'Dengan Brankar'],
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
            'alasan_pulang' => [
                ['value' => 'Sembuh', 'label' => 'Sembuh'],
                ['value' => 'Atas Permintaan Sendiri (APS)', 'label' => 'Pulang Atas Permintaan Sendiri (APS)'],
                ['value' => 'Dirujuk', 'label' => 'Dirujuk ke Rumah Sakit Lain'],
                ['value' => 'Meninggal', 'label' => 'Meninggal'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'kategori_icd' => [
                ['value' => 'ICD-10', 'label' => 'ICD-10'],
                ['value' => 'ICD-9', 'label' => 'ICD-9'],
            ],
            'ruang_igd' => [
                ['value' => 'Ruang Resusitasi', 'label' => 'Ruang Resusitasi'],
                ['value' => 'Ruang Tindakan Non-Bedah', 'label' => 'Ruang Tindakan Non-Bedah'],
                ['value' => 'Ruang Observasi', 'label' => 'Ruang Observasi'],
                ['value' => 'Ruang Isolasi', 'label' => 'Ruang Isolasi'],
                ['value' => 'IGD Anak', 'label' => 'IGD Anak'],
            ],
            'cara_pemberian_o2' => [
                ['value' => 'Nasal Canul', 'label' => 'Nasal Canul'],
                ['value' => 'Non Rebreathing Mask', 'label' => 'Non Rebreathing Mask'],
                ['value' => 'Rebreathing Mask', 'label' => 'Rebreathing Mask'],
                ['value' => 'Ventury', 'label' => 'Ventury Mask'],
                ['value' => 'CPAP', 'label' => 'CPAP'],
                ['value' => 'Voltran', 'label' => 'Ventilator Volutan'],
                ['value' => 'HFNC', 'label' => 'HFNC (High Flow Nasal Cannula)'],
                ['value' => 'Ventilator', 'label' => 'Ventilator'],
                ['value' => 'Room Air', 'label' => 'Room Air (Tanpa Oksigen)'],
            ],
            'karakter_bab' => [
                ['value' => 'Normal', 'label' => 'Normal'],
                ['value' => 'Diare', 'label' => 'Diare'],
                ['value' => 'Konstipasi', 'label' => 'Konstipasi'],
                ['value' => 'Colostomy', 'label' => 'Colostomy'],
                ['value' => 'Melena', 'label' => 'Melena'],
                ['value' => 'Inkontinensia', 'label' => 'Inkontinensia'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'karakter_bak' => [
                ['value' => 'Normal', 'label' => 'Normal'],
                ['value' => 'Disuria', 'label' => 'Disuria (Nyeri saat buang air kecil)'],
                ['value' => 'Retensio', 'label' => 'Retensio (Sulit buang air kecil)'],
                ['value' => 'Inkontinensia', 'label' => 'Inkontinensia'],
                ['value' => 'Hematuri', 'label' => 'Hematuri (Berdarah)'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'jenis_konsultasi' => [
                ['value' => 'REHABILITASI_MEDIK', 'label' => 'Konsultasi Rehabilitasi Medik'],
                ['value' => 'KONSUL_LAYANAN', 'label' => 'Konsul Layanan Klinik'],
                ['value' => 'RENCANA_KONTROL', 'label' => 'Rencana Kontrol Rawat Jalan'],
            ],
            'kesadaran' => [
                ['value' => 'Compos Mentis', 'label' => 'Compos Mentis (Alert)'],
                ['value' => 'Apatis', 'label' => 'Apatis (GCS 12-14)'],
                ['value' => 'Delirium', 'label' => 'Delirium (GCS 10-11)'],
                ['value' => 'Somnolen', 'label' => 'Somnolen (GCS 7-9)'],
                ['value' => 'Soporus', 'label' => 'Soporus (GCS 4-6)'],
                ['value' => 'Confusion', 'label' => 'Confusion (Bingung)'],
                ['value' => 'Restlessness', 'label' => 'Restlessness (Gelisah)'],
                ['value' => 'Lethargy', 'label' => 'Lethargy (Lesu)'],
                ['value' => 'Stupor', 'label' => 'Stupor'],
                ['value' => 'Coma', 'label' => 'Coma'],
            ],
            'tujuan_kunjungan' => [
                ['value' => 'Rawat Jalan', 'label' => 'Rawat Jalan (Konsultasi / Pengobatan)'],
                ['value' => 'Kontrol', 'label' => 'Kontrol Berkala'],
                ['value' => 'Rujukan', 'label' => 'Rujukan / Konsul ke Poli Lain'],
                ['value' => 'Rawat Inap', 'label' => 'Rawat Inap'],
            ],
            'oksigen' => [
                ['value' => 'Air', 'label' => 'Air (Tanpa Oksigen)'],
                ['value' => 'Oksigen', 'label' => 'Oksigen'],
            ],
            'shift' => [
                ['value' => 'Pagi', 'label' => 'Pagi (07.00 - 14.00)'],
                ['value' => 'Siang', 'label' => 'Siang (14.00 - 21.00)'],
                ['value' => 'Sore', 'label' => 'Sore (21.00 - 07.00)'],
                ['value' => 'Malam', 'label' => 'Malam'],
            ],
            'urgensi_sbar' => [
                ['value' => 'Biasa', 'label' => 'Biasa'],
                ['value' => 'Segera', 'label' => 'Segera'],
                ['value' => 'Mendesak', 'label' => 'Mendesak'],
            ],
            'cara_komunikasi' => [
                ['value' => 'Tatap Muka', 'label' => 'Tatap Muka'],
                ['value' => 'Telepon', 'label' => 'Telepon'],
                ['value' => 'WhatsApp', 'label' => 'WhatsApp'],
                ['value' => 'Surat', 'label' => 'Surat Rujukan'],
            ],
            'alat_invasif' => [
                ['value' => 'Kateter Urin (Foley)', 'label' => 'Kateter Urin (Foley)'],
                ['value' => 'Kateter Afferent (CA)', 'label' => 'Kateter Afferent (CA)'],
                ['value' => 'Kateter Efferent (CE)', 'label' => 'Kateter Efferent (CE)'],
                ['value' => 'Kateter Suprapubik', 'label' => 'Kateter Suprapubik'],
                ['value' => 'NGT / OG', 'label' => 'NGT / OG (Nasogastric Tube)'],
                ['value' => 'ETT', 'label' => 'ETT (Endotracheal Tube)'],
                ['value' => 'Tracheostomy', 'label' => 'Tracheostomy'],
                ['value' => 'CVC', 'label' => 'CVC (Central Venous Catheter)'],
                ['value' => 'PICC Line', 'label' => 'PICC Line'],
                ['value' => 'IVC / Perifer', 'label' => 'IVC / Jalur Venosa Perifer'],
                ['value' => 'Arteri Line', 'label' => 'Arteri Line'],
                ['value' => 'Chest Tube', 'label' => 'Chest Tube (Toraks Drainase)'],
                ['value' => 'Drainase Abdomen', 'label' => 'Drainase Abdomen (JP / Penrose)'],
                ['value' => 'Nasal Kanul', 'label' => 'Nasal Kanul'],
                ['value' => 'TBQ', 'label' => 'TBQ (Transnasal Oksigen)'],
            ],
            'lokasi_alat_invasif' => [
                ['value' => 'Vena Mediana Cubiti', 'label' => 'Vena Mediana Cubiti'],
                ['value' => 'Vena Cephalica', 'label' => 'Vena Cephalica'],
                ['value' => 'Vena Basilica', 'label' => 'Vena Basilica'],
                ['value' => 'Vena Jugularis Interna', 'label' => 'Vena Jugularis Interna'],
                ['value' => 'Vena Subclavia', 'label' => 'Vena Subclavia'],
                ['value' => 'Vena Femoralis', 'label' => 'Vena Femoralis'],
                ['value' => 'Bladder', 'label' => 'Bladder (Vesica Urinaria)'],
                ['value' => 'Lambung', 'label' => 'Lambung / Perut'],
                ['value' => 'Trakea', 'label' => 'Trakea'],
                ['value' => 'Pleura', 'label' => 'Pleura (Rongga Dada)'],
                ['value' => 'Perut', 'label' => 'Perut (Rongga Peritoneal)'],
                ['value' => 'Tangan', 'label' => 'Tangan / Perifer'],
                ['value' => 'Kaki', 'label' => 'Kaki / Perifer'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],

            // ===== Fase P1 — Catatan Medis LanjutAN =====
            // Form 16 Resume Medis — tujuan rujukan saat kondisi pulang "Dirujuk".
            'tujuan_rujukan' => [
                ['value' => 'Puskesmas', 'label' => 'Puskesmas'],
                ['value' => 'Faskes', 'label' => 'Faskes'],
                ['value' => 'Rumah Sakit', 'label' => 'Rumah Sakit'],
            ],
            // Form 16 Resume Medis — klasifikasi kematian saat pulang "Meninggal".
            'penyebab_kematian' => [
                ['value' => 'DOA', 'label' => 'DOA (Dead On Arrival)'],
                ['value' => 'Meninggal Di IGD', 'label' => 'Meninggal Di IGD'],
                ['value' => '< 48 Jam', 'label' => 'Meninggal < 48 Jam'],
                ['value' => '> 48 Jam', 'label' => 'Meninggal > 48 Jam'],
                ['value' => 'Lahir Meninggal', 'label' => 'Lahir Meninggal'],
            ],
            // Form 20 Care Plan — tempat pertemuan evaluasi dijalankan.
            'tempat_pertemuan' => [
                ['value' => 'Ruang Perawatan', 'label' => 'Ruang Perawatan'],
                ['value' => 'Poli / Poliklinik', 'label' => 'Poli / Poliklinik'],
                ['value' => 'Instalasi Gawat Darurat', 'label' => 'Instalasi Gawat Darurat'],
                ['value' => 'Ruang Periksa', 'label' => 'Ruang Periksa'],
                ['value' => 'Teleedema', 'label' => 'Teleedema / Telemedisin'],
            ],
            // Form 21 DAR — jenis kejadian yang dilaporkan.
            'jenis_dar' => [
                ['value' => 'Kecelakaan Lalu Lintas', 'label' => 'Kecelakaan Lalu Lintas'],
                ['value' => 'Kecelakaan Kerja', 'label' => 'Kecelakaan Kerja'],
                ['value' => 'Kekerasan', 'label' => 'Kekerasan'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            // ===== Form 19 Triage IGD =====
            // Keadaan umum pasien saat triase (legacy `ku`).
            'keadaan_umum_igd' => [
                ['value' => 'Baik', 'label' => 'Baik', 'class' => 'text-emerald-600'],
                ['value' => 'Sedang', 'label' => 'Sedang', 'class' => 'text-amber-500'],
                ['value' => 'Lemas', 'label' => 'Lemas', 'class' => 'text-red-600'],
            ],
            // Patent jalan napas. Objek 226 dipakai dua variabel
            // (jalan_nafas, tipe_nafas) karena legacy punya dua kontrol
            // terpisah.
            'jalan_nafas' => [
                ['value' => 'N/A', 'label' => 'N/A (tidak dapat dinilai)'],
                ['value' => 'PATEN', 'label' => 'PATEN'],
                ['value' => 'GURGLING', 'label' => 'GURGLING'],
                ['value' => 'SNORRING', 'label' => 'SNORRING'],
                ['value' => 'STRIDOR', 'label' => 'STRIDOR'],
            ],
            'sirkulasi' => [
                ['value' => 'Nadi Teraba', 'label' => 'Nadi Teraba'],
                ['value' => 'Nadi Tidak Teraba', 'label' => 'Nadi Tidak Teraba'],
            ],
            'pupil' => [
                ['value' => 'Baik', 'label' => 'Baik'],
                ['value' => 'Tidak Baik', 'label' => 'Tidak Baik'],
                ['value' => 'Lambat', 'label' => 'Lambat'],
            ],
            'jenis_anamnesis' => [
                ['value' => 'Auto Anamnesis', 'label' => 'Auto Anamnesis'],
                ['value' => 'Allo Anamnesis', 'label' => 'Allo Anamnesis'],
            ],
            'alasan_kunjungan_igd' => [
                ['value' => 'Lemas', 'label' => 'Lemas'],
                ['value' => 'Demam', 'label' => 'Demam'],
                ['value' => 'Pusing', 'label' => 'Pusing'],
                ['value' => 'Sakit Kepala', 'label' => 'Sakit Kepala'],
                ['value' => 'Batuk', 'label' => 'Batuk'],
                ['value' => 'Pilek', 'label' => 'Pilek'],
                ['value' => 'Sesak Napas', 'label' => 'Sesak Napas'],
                ['value' => 'Kontrol', 'label' => 'Kontrol'],
                ['value' => 'Obat Habis', 'label' => 'Obat Habis'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'riwayat_penyakit_igd' => [
                ['value' => 'Hipertensi', 'label' => 'Hipertensi'],
                ['value' => 'DM', 'label' => 'Diabetes Melitus'],
                ['value' => 'Typhoid', 'label' => 'Typhoid'],
                ['value' => 'DHF', 'label' => 'Demam Berdarah (DHF)'],
                ['value' => 'TB', 'label' => 'Tuberkulosis'],
                ['value' => 'Stroke', 'label' => 'Stroke'],
                ['value' => 'Asma', 'label' => 'Asma'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'frekuensi_nyeri' => [
                ['value' => 'Sering', 'label' => 'Sering'],
                ['value' => 'Kadang', 'label' => 'Kadang'],
                ['value' => 'Jarang', 'label' => 'Jarang'],
            ],
            'karakteristik_nyeri' => [
                ['value' => 'Terbakar', 'label' => 'Terbakar'],
                ['value' => 'Tertusuk', 'label' => 'Tertusuk'],
                ['value' => 'Tertindih', 'label' => 'Tertindih'],
                ['value' => 'Menyebar', 'label' => 'Menyebar'],
                ['value' => 'Berdenyut', 'label' => 'Berdenyut'],
            ],
            // 6 nilai legacy (1-5 + HITAM). Sengaja BERBEDA dari key
            // `triase_igd` (Merah/Kuning/Hijau/Hitam) yang dipakai kolom
            // `registrasi_detail.prioritas` - jangan memaksa satu key untuk
            // dua keperluan.
            'prioritas_triase_igd' => [
                ['value' => '1', 'label' => 'Prioritas 1 - Resuscitation', 'class' => 'text-red-600'],
                ['value' => '2', 'label' => 'Prioritas 2 - Emergency', 'class' => 'text-red-600'],
                ['value' => '3', 'label' => 'Prioritas 3 - Urgent', 'class' => 'text-amber-500'],
                ['value' => '4', 'label' => 'Prioritas 4 - Semi Urgent', 'class' => 'text-amber-500'],
                ['value' => '5', 'label' => 'Prioritas 5 - Non Urgent', 'class' => 'text-emerald-500'],
                ['value' => 'HITAM', 'label' => 'HITAM - DOA', 'class' => 'text-slate-800'],
            ],
            // Form 57 Catatan Medis Visum.
            'jenis_visum' => [
                ['value' => 'LAKA / KLL', 'label' => 'LAKA / KLL'],
                ['value' => 'KDRT', 'label' => 'KDRT (Kekerasan Dalam Rumah Tangga)'],
                ['value' => 'LAIN-LAIN', 'label' => 'Lain-lain'],
            ],

            // ===== Fase P2 - Asuhan Keperawatan Lanjutan =====
            // Key `ya_tidak_kapital` dipakai untuk flag Ya/Tidak yang
            // disimpan dengan huruf besar (form 35, 123). Key
            // `flag_ya_tidak` (huruf kecil) khusus form 55 yang mengikuti
            // penamaan legacy `ya` / `tidak`.
            'ya_tidak_kapital' => [
                ['value' => 'Ya', 'label' => 'Ya'],
                ['value' => 'Tidak', 'label' => 'Tidak'],
            ],
            'flag_ya_tidak' => [
                ['value' => 'ya', 'label' => 'Ya'],
                ['value' => 'tidak', 'label' => 'Tidak'],
            ],
            // Key `ada_tidak` untuk pilihan Ada / Tidak Ada (form 31).
            'ada_tidak' => [
                ['value' => 'ada', 'label' => 'Ada'],
                ['value' => 'tidak_ada', 'label' => 'Tidak Ada'],
            ],

            // Form 22 Rencana Asuhan Keperawatan - satuan durasi tujuan.
            'satuan_waktu_tujuan' => [
                ['value' => 'Menit', 'label' => 'Menit'],
                ['value' => 'Jam', 'label' => 'Jam'],
            ],

            // Form 29 Handover - jenis serah terima (objek 40). Nilai A = antar
            // shift, B = transfer ruangan (membuka `ruang_tujuan`),
            // C = consul, D = mortality review.
            'jenis_serah_terima' => [
                ['value' => 'A', 'label' => 'A - Serah Terima Antar Shift'],
                ['value' => 'B', 'label' => 'B - Transfer Ruangan'],
                ['value' => 'C', 'label' => 'C - Permintaan Konsul'],
                ['value' => 'D', 'label' => 'D - Mortality Review'],
            ],
            'status_kelengkapan_serah_terima' => [
                ['value' => 'Selesai', 'label' => 'Selesai'],
                ['value' => 'Belum Lengkap', 'label' => 'Belum Lengkap'],
            ],

            // Form 31 Asesmen Kebutuhan Edukasi.
            'tingkat_pendidikan_edukasi' => [
                ['value' => 'TK', 'label' => 'TK /-Prasekolah'],
                ['value' => 'SD', 'label' => 'SD'],
                ['value' => 'SMP', 'label' => 'SMP / Sederajat'],
                ['value' => 'SMA', 'label' => 'SMA / Sederajat'],
                ['value' => 'PT', 'label' => 'PT /College / Universitas'],
                ['value' => 'Tidak Sekolah', 'label' => 'Tidak Sekolah'],
            ],
            'kemampuan_bicara' => [
                ['value' => 'Normal', 'label' => 'Normal'],
                ['value' => 'Tidak', 'label' => 'Tidak (Perlu Penerjemah)'],
            ],
            'perlu_penerjemah' => [
                ['value' => 'Perlu Translater', 'label' => 'Perlu Translater'],
                ['value' => 'Tidak Perlu Translater', 'label' => 'Tidak Perlu Translater'],
            ],
            'hambatan_belajar' => [
                ['value' => 'Bahasa', 'label' => 'Bahasa'],
                ['value' => 'Hilang Memori', 'label' => 'Hilang Memori'],
                ['value' => 'Gangguan Penglihatan', 'label' => 'Gangguan Penglihatan'],
                ['value' => 'Gangguan Pendengaran', 'label' => 'Gangguan Pendengaran'],
                ['value' => 'Motivasi Buruk', 'label' => 'Motivasi Buruk'],
                ['value' => 'Gangguan Kognitif', 'label' => 'Gangguan Kognitif'],
                ['value' => 'Kesulitan Bicara', 'label' => 'Kesulitan Bicara'],
                ['value' => 'Tidak Ada', 'label' => 'Tidak Ada'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'kebutuhan_belajar' => [
                ['value' => 'Proses Penyakit', 'label' => 'Proses Penyakit'],
                ['value' => 'Pengobatan', 'label' => 'Pengobatan'],
                ['value' => 'Obat/Terapi', 'label' => 'Obat / Terapi'],
                ['value' => 'Manajemen Nyeri', 'label' => 'Manajemen Nyeri'],
                ['value' => 'Exercise', 'label' => 'Exercise'],
                ['value' => 'Perawatan', 'label' => 'Perawatan'],
                ['value' => 'Nutrisi', 'label' => 'Nutrisi'],
                ['value' => 'Tindakan', 'label' => 'Tindakan'],
            ],
            'sedia_terima_edukasi' => [
                ['value' => 'Bersedia', 'label' => 'Bersedia'],
                ['value' => 'Tidak', 'label' => 'Tidak Bersedia'],
            ],
            'alasan_asesmen_ulang' => [
                ['value' => 'Tindakan Baru', 'label' => 'Tindakan Baru'],
                ['value' => 'Terapi Baru', 'label' => 'Terapi Baru'],
                ['value' => 'Perubahan Kondisi', 'label' => 'Perubahan Kondisi Pasien'],
                ['value' => 'Perubahan DPJP', 'label' => 'Perubahan DPJP'],
            ],
            'respon_emosi_edukasi' => [
                ['value' => 'Tenang', 'label' => 'Tenang'],
                ['value' => 'Marah', 'label' => 'Marah'],
                ['value' => 'Rendah Diri', 'label' => 'Rendah Diri'],
                ['value' => 'Gelisah', 'label' => 'Gelisah'],
                ['value' => 'Takut', 'label' => 'Takut'],
                ['value' => 'Sedih', 'label' => 'Sedih'],
                ['value' => 'Menangis', 'label' => 'Menangis'],
                ['value' => 'Mudah Tersinggung', 'label' => 'Mudah Tersinggung'],
                ['value' => 'Cemas', 'label' => 'Cemas'],
            ],

            // Form 35 Pengkajian Keperawatan Kritis.
            'asal_masuk_kritis' => [
                ['value' => 'IGD', 'label' => 'IGD'],
                ['value' => 'Kamar Operasi', 'label' => 'Kamar Operasi'],
                ['value' => 'Ruangan', 'label' => 'Ruangan Perawatan'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'jalan_nafas_kritis' => [
                ['value' => 'Tanpa Alat', 'label' => 'Tanpa Alat'],
                ['value' => 'OPA', 'label' => 'OPA (Orofaringeal Airways)'],
                ['value' => 'ETT/TT', 'label' => 'ETT / Tracheostomy Tube'],
            ],
            'pernapasan_kritis' => [
                ['value' => 'Spontan', 'label' => 'Spontan'],
                ['value' => 'Dibantu alat', 'label' => 'Dibantu Alat'],
            ],
            'suara_nafas' => [
                ['value' => 'Vesikuler', 'label' => 'Vesikuler'],
                ['value' => 'Wheezing', 'label' => 'Wheezing'],
                ['value' => 'Ronchi', 'label' => 'Ronchi'],
                ['value' => 'Tridor', 'label' => 'Tridor'],
            ],
            'sirkulasi_perfusi' => [
                ['value' => 'Al Adequat', 'label' => 'Al Adequat'],
                ['value' => 'Gangguan Perfusi', 'label' => 'Gangguan Perfusi'],
                ['value' => 'Shock', 'label' => 'Shock'],
            ],
            // Kesadaran versi kritis (CM - Koma). BEDA dari key `kesadaran`
            // yang memakai ACMS (Compos Mentis - Coma).
            'kesadaran_kritis' => [
                ['value' => 'CM', 'label' => 'CM (Compos Mentis)'],
                ['value' => 'Somnolen', 'label' => 'Somnolen'],
                ['value' => 'Apatis', 'label' => 'Apatis'],
                ['value' => 'Soporus', 'label' => 'Soporus'],
                ['value' => 'Koma', 'label' => 'Koma'],
            ],
            'nilai_kritis' => [
                ['value' => 'Normal', 'label' => 'Normal'],
                ['value' => 'Abnormal', 'label' => 'Abnormal'],
            ],
            'metode_skala_nyeri' => [
                ['value' => 'VAS', 'label' => 'VAS (Visual Analog Scale)'],
                ['value' => 'FLACC', 'label' => 'FLACC (Face, Leg, Activity, Cry, Consolability)'],
            ],

            // Form 55 Riwayat Penyakit - anamnesis.
            'keluhan_anamnesis' => [
                ['value' => 'Lemas', 'label' => 'Lemas'],
                ['value' => 'Demam', 'label' => 'Demam'],
                ['value' => 'Pusing', 'label' => 'Pusing'],
                ['value' => 'Sakit Kepala', 'label' => 'Sakit Kepala'],
                ['value' => 'Batuk', 'label' => 'Batuk'],
                ['value' => 'Pilek', 'label' => 'Pilek'],
                ['value' => 'Sesak Napas', 'label' => 'Sesak Napas'],
                ['value' => 'Kontrol', 'label' => 'Kontrol'],
                ['value' => 'Obat Habis', 'label' => 'Obat Habis'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'riwayat_penyakit_sebelumnya' => [
                ['value' => 'Hipertensi', 'label' => 'Hipertensi'],
                ['value' => 'DM', 'label' => 'Diabetes Melitus'],
                ['value' => 'Typhoid', 'label' => 'Typhoid'],
                ['value' => 'DHF', 'label' => 'Demam Berdarah (DHF)'],
                ['value' => 'TB', 'label' => 'Tuberkulosis'],
                ['value' => 'Stroke', 'label' => 'Stroke'],
                ['value' => 'Asma', 'label' => 'Asma'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'menular_melalui' => [
                ['value' => 'Udara', 'label' => 'Udara'],
                ['value' => 'Cairan Tubung', 'label' => 'Cairan Tubung'],
                ['value' => 'Kontak Langsung/Kulit', 'label' => 'Kontak Langsung / Kulit'],
                ['value' => 'Makanan', 'label' => 'Makanan'],
                ['value' => 'Hewan', 'label' => 'Hewan'],
                ['value' => 'Susu/ASI', 'label' => 'Susu / ASI'],
                ['value' => 'Air', 'label' => 'Air'],
                ['value' => 'Droplet', 'label' => 'Droplet'],
            ],

            // Form 56 Riwayat Keluarga.
            'penyakit_keluarga' => [
                ['value' => 'Asma', 'label' => 'Asma'],
                ['value' => 'Diabetes', 'label' => 'Diabetes Melitus'],
                ['value' => 'Hipertensi', 'label' => 'Hipertensi'],
                ['value' => 'TB', 'label' => 'Tuberkulosis'],
                ['value' => 'Cancer', 'label' => 'Cancer / Kanker'],
                ['value' => 'Anemia', 'label' => 'Anemia'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'hubungan_keluarga' => [
                ['value' => 'Ayah', 'label' => 'Ayah'],
                ['value' => 'Ibu', 'label' => 'Ibu'],
                ['value' => 'Suami', 'label' => 'Suami'],
                ['value' => 'Istri', 'label' => 'Istri'],
                ['value' => 'Anak', 'label' => 'Anak'],
                ['value' => 'Orang Tua', 'label' => 'Orang Tua'],
                ['value' => 'Saudara Kandung', 'label' => 'Saudara Kandung'],
                ['value' => 'Kakek', 'label' => 'Kakek'],
                ['value' => 'Nenek', 'label' => 'Nenek'],
                ['value' => 'Keponakan', 'label' => 'Keponakan'],
                ['value' => 'Mertua', 'label' => 'Mertua'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],

            // Form 124 Uji Fungsi & Asesment.
            'frekuensi_kunjungan_rehab' => [
                ['value' => 'Harian', 'label' => 'Harian'],
                ['value' => '2x/Minggu', 'label' => '2x / Minggu'],
                ['value' => 'Mingguan', 'label' => 'Mingguan'],
                ['value' => '2x/Bulan', 'label' => '2x / Bulan'],
                ['value' => 'Bulanan', 'label' => 'Bulanan'],
                ['value' => 'Once', 'label' => 'Sekali Saja (One Time Visit)'],
            ],

            // Form 125 Respon Emosi & Status Mental.
            'respon_emosi' => [
                ['value' => 'Tenang', 'label' => 'Tenang'],
                ['value' => 'Marah / Mudah Tersinggung', 'label' => 'Marah / Mudah Tersinggung'],
                ['value' => 'Rendah Diri', 'label' => 'Rendah Diri'],
                ['value' => 'Takut', 'label' => 'Takut'],
                ['value' => 'Sedih', 'label' => 'Sedih'],
                ['value' => 'Cemas', 'label' => 'Cemas'],
                ['value' => 'Gelisah Agitasi', 'label' => 'Gelisah Agitasi'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'status_mental' => [
                ['value' => 'Orientasi Baik', 'label' => 'Orientasi Baik'],
                ['value' => 'Kooperatif', 'label' => 'Kooperatif'],
                ['value' => 'Tidak Ada Respon', 'label' => 'Tidak Ada Respon'],
                ['value' => 'Disorientasi', 'label' => 'Disorientasi'],
                ['value' => 'Menyerang', 'label' => 'Menyerang'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],
            'kebutuhan_teridentifikasi' => [
                ['value' => 'Korban Kekerasan', 'label' => 'Korban Kekerasan'],
                ['value' => 'Terlantar', 'label' => 'Terlantar'],
            ],
            'identifikasi_perlindungan' => [
                ['value' => 'Tidak Mau Dikunjungi', 'label' => 'Tidak Mau Dikunjungi'],
                ['value' => 'Pembatasan Pengunjung', 'label' => 'Pembatasan Pengunjung'],
                ['value' => 'Ruang Khusus', 'label' => 'Ruang Khusus'],
                ['value' => 'CCTV', 'label' => 'CCTV'],
                ['value' => 'Perlindungan Security', 'label' => 'Perlindungan Security'],
                ['value' => 'Perlindungan Hukum', 'label' => 'Perlindungan Hukum'],
                ['value' => 'Dukungan Eksternal', 'label' => 'Dukungan Eksternal (Dinas / Yayasan Sosial)'],
            ],

            // Form 27 Penilaian Nyeri - daftar ini BERBEDA dari key
            // `frekuensi_nyeri` / `karakteristik_nyeri` milik form pengkajian
            // nyeri harian. Dua key itu sengaja dipisah, jangan dipaksa satu
            // key untuk dua keperluan (nilai yang sudah tersimpan di emr_detail
            // tidak boleh berubah artinya).
            'frekuensi_nyeri_penilaian' => [
                ['value' => 'Tidak Ada', 'label' => 'Tidak Ada'],
                ['value' => 'Kadang', 'label' => 'Kadang'],
                ['value' => 'Sering', 'label' => 'Sering'],
                ['value' => 'Jarang', 'label' => 'Jarang'],
            ],
            'karakteristik_nyeri_penilaian' => [
                ['value' => 'Terbakar', 'label' => 'Terbakar'],
                ['value' => 'Tertindih', 'label' => 'Tertindih'],
                ['value' => 'Menyebar', 'label' => 'Menyebar'],
                ['value' => 'Tajam', 'label' => 'Tajam'],
                ['value' => 'Tumpul', 'label' => 'Tumpul'],
                ['value' => 'Berdenyut', 'label' => 'Berdenyut'],
                ['value' => 'Lainnya', 'label' => 'Lainnya'],
            ],

            // Form 37 Body Map Nyeri - sisi lokasi yang ditandai.
            'sisi_lokasi' => [
                ['value' => 'Kiri', 'label' => 'Kiri'],
                ['value' => 'Kanan', 'label' => 'Kanan'],
                ['value' => 'Tengah', 'label' => 'Tengah'],
                ['value' => 'Bilateral', 'label' => 'Bilateral'],
            ],

            // Form 30 Monitoring Risiko Jatuh - status monitoring hasil
            // perbandingan dengan penilaian sebelumnya. Nilai finalnya dihitung
            // ulang di server (lihat MonitoringRisikoJatuhController), jadi
            // daftar ini hanya dipakai untuk dropdown/label di blade.
            'status_monitoring_jatuh' => [
                ['value' => 'Membaik', 'label' => 'Membaik (skor turun dari penilaian sebelumnya)'],
                ['value' => 'Tetap', 'label' => 'Tetap (skor sama dengan penilaian sebelumnya)'],
                ['value' => 'Memburuk', 'label' => 'Memburuk (skor naik dari penilaian sebelumnya)'],
            ],

            // Form 30 Monitoring Risiko Jatuh - checklist intervensi pencegahan
            // jatuh, 6 butir verbatim dari legacy
            // `penatalaksanaan_pencegahan_risiko_jatuh.php`. Nilai yang disimpan
            // adalah TEKS butir itu sendiri (JSON array di emr_detail).
            'intervensi_pencegahan_jatuh' => [
                ['value' => 'Pastikan Stiker Kuning Terpasang pada Gelang', 'label' => 'Pastikan Stiker Kuning Terpasang pada Gelang'],
                ['value' => 'Informasi Pasa Pasien dan Keluarga', 'label' => 'Informasi Pasa Pasien dan Keluarga'],
                ['value' => 'Pastikan Terpasang Hand Rail', 'label' => 'Pastikan Terpasang Hand Rail'],
                ['value' => 'Kondisi Lantai Tidak Licin', 'label' => 'Kondisi Lantai Tidak Licin'],
                ['value' => 'Melakukan Penilaian Ulang Risiko Jatuh Tiap Shift', 'label' => 'Melakukan Penilaian Ulang Risiko Jatuh Tiap Shift'],
                ['value' => 'Kondisi Kamar Mandi Tidak Terkunci', 'label' => 'Kondisi Kamar Mandi Tidak Terkunci'],
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
