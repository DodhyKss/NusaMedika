@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Discharge Planning';
        $subtitleForm = 'Penilaian kesiapan pulang: pengaruh kondisi, kebutuhan bantuan sehari-hari, kebutuhan edukasi, dan rencana tanggal pulang.';
        $routeName = null;
        $routeUrl = url('emr/form/discharge_planning');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $icdList = $icdList ?? collect();

        // Blok C - pertanyaan "apakah ada X?" memakai radio Ya / Tidak.
        $radioYaTidak = [
            'pengaruh_pasien_kel' => 'Apakah ada pengaruh pada pasien / keluarga?',
            'pengaruh_kerja' => 'Apakah ada pengaruh terhadap pekerjaan / kemandirian?',
            'pengaruh_keuangan' => 'Apakah ada pengaruh terhadap keuangan keluarga?',
            'tinggal_sendiri' => 'Apakah pasien tinggal sendiri?',
            'membantu_pasien' => 'Apakah ada yang membantu pasien di rumah?',
            'gunakan_alat_medis' => 'Apakah pasien menggunakan alat medis di rumah?',
            'perlu_alat_bantu' => 'Apakah memerlukan alat bantu?',
            'perlu_perawatan_khusus' => 'Apakah memerlukan perawatan khusus?',
            'masalah_kebutuhan_pribadi' => 'Apakah ada masalah kebutuhan pribadi?',
            'nyeri_kronis' => 'Apakah pasien mengalami nyeri kronis?',
            'perlu_edukasi' => 'Apakah memerlukan edukasi?',
            'keterampilan_khusus' => 'Apakah memerlukan keterampilan khusus?',
        ];

        // Blok D - kebutuhan bantuan sehari-hari (checkbox).
        $adlFields = [
            'adl_menyiapkan_makanan' => 'Menyiapkan makanan sendiri',
            'adl_makan' => 'Makan sendiri',
            'adl_diet' => 'Menjalankan diet sesuai anjuran',
            'adl_menyiapkan_obat' => 'Menyiapkan obat sendiri',
            'adl_minum_obat' => 'Meng minum obat sesuai aturan',
            'adl_mandi' => 'Mandi sendiri',
            'adl_berpakaian' => 'Berpakaian sendiri',
            'adl_transportasi' => 'Berjalan / bepergian sendiri',
            'adl_edukasi_kesehatan' => 'Memahami edukasi kesehatan',
        ];

        // Blok E - kebutuhan edukasi (checkbox).
        $edukasiFields = [
            'edukasi_obat_obat' => 'Penggunaan obat',
            'edukasi_nutrisi' => 'Nutrisi / diet',
            'edukasi_perawatan_luka' => 'Perawatan luka',
            'edukasi_mobilisasi' => 'Mobilisasi bertahap',
            'edukasi_manajemen_nyeri' => 'Manajemen nyeri',
            'edukasi_insulin_sc' => 'Pemberian insulin subcutaneous',
        ];

        // WAJIB per variabel: $emr_data hanya mengimplementasikan ArrayAccess dan
        // __toString()-nya kosong, jadi $emr_data scalar selalu bernilai kosong.
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');
        $ya = fn ($key) => old($key, $emr_data[$key] ?? '') === 'Ya' ? 'checked' : '';

        $perluEdukasi = old('perlu_edukasi', $emr_data['perlu_edukasi'] ?? 'Ya');
        $tglMasuk = old('tanggal_masuk_rs', $emr_data['tanggal_masuk_rs'] ?? '');
        $estimasi = old('estimasi_hari_rawat', $emr_data['estimasi_hari_rawat'] ?? '');
        $tglRenPulang = old('tanggal_ren_pulang', $emr_data['tanggal_ren_pulang'] ?? '');
        $diagnosisTerpilih = (string) old('diagnosis_medis', $emr_data['diagnosis_medis'] ?? '');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Discharge Planning"
        :titleForm="$titleForm"
        :subtitleForm="$subtitleForm"
        :historyGrouped="$historyGrouped"
        :routeName="$routeName"
        :routeUrl="$routeUrl"
        :registrasiDetailId="$registrasiDetailId"
        :formAction="$formAction"
        :isEdit="$isEdit"
        :deleteAction="$deleteAction"
        :emrId="$emr_id"
        :isView="$isView"
        :printUrl="''"
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']">

        <x-slot name="listRiwayat">
            <x-emr-history-table
                slug="discharge_planning"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'estimasi_hari_rawat' => 'Estimasi Hari Rawat',
                    'tanggal_ren_pulang' => 'Rencana Tanggal Pulang',
                    'perlu_edukasi' => 'Perlu Edukasi',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= BLOK A - IDENTITAS & PERENCANAAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas &amp; Perencanaan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="tanggal_masuk_rs" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Masuk RS <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_masuk_rs" name="tanggal_masuk_rs" readonly
                                   value="{{ $tglMasuk }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed outline-none">
                            @error('tanggal_masuk_rs')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_masuk_rs" class="block font-bold text-slate-800 mb-1.5">
                                Jam Masuk RS <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_masuk_rs" name="waktu_masuk_rs" readonly step="60"
                                   value="{{ substr((string) $v('waktu_masuk_rs'), 0, 5) }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed outline-none">
                            @error('waktu_masuk_rs')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="estimasi_hari_rawat" class="block font-bold text-slate-800 mb-1.5">
                                Estimasi / Rencana Hari Rawat <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="estimasi_hari_rawat" name="estimasi_hari_rawat" min="0" max="365"
                                   value="{{ $estimasi }}" placeholder="Contoh: 3"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('estimasi_hari_rawat')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="diagnosis_medis" class="block font-bold text-slate-800 mb-1.5">
                            Diagnosa Medis <span class="text-red-500">*</span>
                        </label>
                        <select id="diagnosis_medis" name="diagnosis_medis"
                                class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            <option value="">-- Pilih Diagnosa --</option>
                            @foreach ($icdList as $icd)
                                <option value="{{ $icd->icd_id }}" {{ (string) $icd->icd_id === $diagnosisTerpilih ? 'selected' : '' }}>
                                    {{ $icd->kode_diagnosa }} - {{ $icd->nama_diagnosa }}
                                </option>
                            @endforeach
                        </select>
                        @error('diagnosis_medis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_ren_pulang" class="block font-bold text-slate-800 mb-1.5">
                                Rencana Tanggal Pemulangan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_ren_pulang" name="tanggal_ren_pulang"
                                   value="{{ $tglRenPulang }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_ren_pulang')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-400">Dihitung otomatis dari tanggal masuk + estimasi hari dirawat.</p>
                        </div>
                        <div>
                            <label for="waktu_ren_pulang" class="block font-bold text-slate-800 mb-1.5">Jam Rencana Pemulangan</label>
                            <input type="time" id="waktu_ren_pulang" name="waktu_ren_pulang" step="60"
                                   value="{{ $v('waktu_ren_pulang') ? substr((string) $v('waktu_ren_pulang'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_ren_pulang')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= BLOK B - PENGARUH PERUBAHAN KONDISI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Pengaruh Perubahan Kondisi</span>
                </div>
                <div class="p-5 bg-white space-y-3 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach (['pengaruh_pasien_kel', 'pengaruh_kerja', 'pengaruh_keuangan'] as $name)
                            <div class="p-3 border border-slate-200 rounded">
                                <div class="font-medium text-slate-700 mb-2">{{ $radioYaTidak[$name] }} <span class="text-red-500">*</span></div>
                                <div class="flex items-center gap-4">
                                    @foreach (['Ya', 'Tidak'] as $pilihan)
                                        <label class="inline-flex items-center gap-1.5 text-sm text-slate-600 cursor-pointer">
                                            <input type="radio" name="{{ $name }}" value="{{ $pilihan }}" {{ $ya($name) }}
                                                   class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500/30">
                                            {{ $pilihan }}
                                        </label>
                                    @endforeach
                                </div>
                                @error($name)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= BLOK C - ASPEK KEBUTUHAN PASIEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Aspek Kebutuhan Pasien</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <label for="antisipati_masalah" class="block font-bold text-slate-800 mb-1.5">
                            Antisipasi Masalah <span class="text-red-500">*</span>
                        </label>
                        <textarea id="antisipati_masalah" name="antisipati_masalah" rows="3"
                                  placeholder="Masalah yang diperkirakan muncul setelah pasien pulang"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('antisipati_masalah') }}</textarea>
                        @error('antisipati_masalah')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach (array_diff_key($radioYaTidak, ['pengaruh_pasien_kel' => 1, 'pengaruh_kerja' => 1, 'pengaruh_keuangan' => 1]) as $name => $pertanyaan)
                            <div class="flex items-center justify-between gap-3 p-3 border border-slate-200 rounded">
                                <div class="text-slate-700 font-medium">{{ $pertanyaan }} <span class="text-red-500">*</span></div>
                                <div class="flex items-center gap-3 shrink-0">
                                    @foreach (['Ya', 'Tidak'] as $pilihan)
                                        <label class="inline-flex items-center gap-1.5 text-sm text-slate-600 cursor-pointer">
                                            <input type="radio" name="{{ $name }}" value="{{ $pilihan }}" {{ $ya($name) }}
                                                   class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500/30">
                                            {{ $pilihan }}
                                        </label>
                                    @endforeach
                                </div>
                                @error($name)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= BLOK D - KEBUTUHAN BANTUAN SEHARI-HARI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kebutuhan Bantuan Sehari-hari (ADL)</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">
                    <p class="text-xs text-slate-400">Centang kebutuhan bantuan yang tidak dapat dilakukan pasien sendiri saat di rumah.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach ($adlFields as $name => $label)
                            <label class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" name="{{ $name }}" value="1" {{ $ya($name) }}
                                       class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/30">
                                <span class="text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @foreach ($adlFields as $name => $label)
                        @error($name)
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    @endforeach

                    <div>
                        <label for="adl_edukasi_lain" class="block font-bold text-slate-800 mb-1.5">Kebutuhan Bantuan Lainnya</label>
                        <input type="text" id="adl_edukasi_lain" name="adl_edukasi_lain"
                               value="{{ $v('adl_edukasi_lain') }}" placeholder="Keterangan kebutuhan lain"
                               class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                        @error('adl_edukasi_lain')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= BLOK E - KEBUTUHAN EDUKASI ================= --}}
            <div id="wrap-edukasi" class="{{ $perluEdukasi === 'Tidak' ? 'hidden' : '' }} border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kebutuhan Edukasi</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">
                    <p class="text-xs text-slate-400">Blok ini dilewati bila jawaban "Perlu Edukasi" adalah Tidak.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach ($edukasiFields as $name => $label)
                            <label class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" name="{{ $name }}" value="1" {{ $ya($name) }}
                                       class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/30">
                                <span class="text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @foreach ($edukasiFields as $name => $label)
                        @error($name)
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    @endforeach

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edukasi_lain_1" class="block font-bold text-slate-800 mb-1.5">Edukasi Lainnya 1</label>
                            <input type="text" id="edukasi_lain_1" name="edukasi_lain_1"
                                   value="{{ $v('edukasi_lain_1') }}" placeholder="Materi edukasi tambahan"
                                   class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                            @error('edukasi_lain_1')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="edukasi_lain_2" class="block font-bold text-slate-800 mb-1.5">Edukasi Lainnya 2</label>
                            <input type="text" id="edukasi_lain_2" name="edukasi_lain_2"
                                   value="{{ $v('edukasi_lain_2') }}" placeholder="Materi edukasi tambahan"
                                   class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                            @error('edukasi_lain_2')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= BLOK F - CATATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Catatan</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label for="catatan" class="block font-bold text-slate-800 mb-1.5">Catatan Tambahan</label>
                    <textarea id="catatan" name="catatan" rows="3"
                              placeholder="Catatan discharge planning, mis. keterangan alat bantu yang diperlukan"
                              class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('catatan') }}</textarea>
                    @error('catatan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tampil/sembunyi harus lewat style.display + lepas class "hidden"
            // (lihat AGENTS.md - .hidden utility Tailwind bisa dikalahkan oleh
            // utility display lain).
            function tampilkan(el, tampil, display) {
                if (!el) return;
                if (tampil) {
                    el.classList.remove('hidden');
                    el.style.display = display;
                } else {
                    el.classList.add('hidden');
                    el.style.display = 'none';
                }
            }

            // Blok E - hanya tampil bila "Perlu Edukasi" = Ya.
            var perluEdukasi = document.querySelectorAll('input[name="perlu_edukasi"]');
            var wrapEdukasi = document.getElementById('wrap-edukasi');

            function syncEdukasi() {
                var terpilih = '';
                perluEdukasi.forEach(function (el) {
                    if (el.checked) terpilih = el.value;
                });
                tampilkan(wrapEdukasi, terpilih !== 'Tidak', 'block');
            }
            perluEdukasi.forEach(function (el) {
                el.addEventListener('change', syncEdukasi);
            });
            syncEdukasi();

            // Tanggal rencana pulang = tanggal masuk RS + estimasi hari dirawat.
            var tglMasuk = document.getElementById('tanggal_masuk_rs');
            var estimasi = document.getElementById('estimasi_hari_rawat');
            var tglRenPulang = document.getElementById('tanggal_ren_pulang');
            var waktuRenPulang = document.getElementById('waktu_ren_pulang');

            function hitungRencanaPulang() {
                if (!tglMasuk || !estimasi || !tglRenPulang) return;
                if (!tglMasuk.value) return;
                var hari = parseInt(estimasi.value || '', 10);
                if (isNaN(hari) || hari < 0) return;

                var parts = tglMasuk.value.split('-');
                var tanggal = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
                tanggal.setDate(tanggal.getDate() + hari);

                var bulan = String(tanggal.getMonth() + 1);
                var hariTanggal = String(tanggal.getDate());
                if (bulan.length < 2) bulan = '0' + bulan;
                if (hariTanggal.length < 2) hariTanggal = '0' + hariTanggal;

                tglRenPulang.value = tanggal.getFullYear() + '-' + bulan + '-' + hariTanggal;

                // Jam rencana pulang mengikuti jam masuk, hanya bila belum diisi.
                var jamMasuk = document.getElementById('waktu_masuk_rs');
                if (jamMasuk && waktuRenPulang && !waktuRenPulang.value && jamMasuk.value) {
                    waktuRenPulang.value = jamMasuk.value.substring(0, 5);
                }
            }
            if (tglMasuk) tglMasuk.addEventListener('change', hitungRencanaPulang);
            if (estimasi) estimasi.addEventListener('input', hitungRencanaPulang);
        });
    </script>

@endsection