@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Pemulangan Pasien';
        $subtitleForm = 'Dokumen pulang: kondisi saat pulang, hasil pemeriksaan, anjuran pulang, barang yang diserahkan, dan rencana kontrol.';
        $routeName = null;
        $routeUrl = url('emr/form/pemulangan_pasien');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $opsi = $opsi ?? [];
        $masalahKeperawatan = $masalahKeperawatan ?? [];
        $perluNyeri = $perluNyeri ?? true;

        // WAJIB per variabel: $emr_data hanya mengimplementasikan ArrayAccess dan
        // __toString()-nya kosong, jadi $emr_data scalar selalu bernilai kosong.
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');
        $ya = fn ($key) => old($key, $emr_data[$key] ?? '') === 'Ya' ? 'checked' : '';

        // Nilai multi select disimpan sebagai JSON - decode sebelum dipakai ulang.
        $multi = function ($key) use ($emr_data) {
            $val = old($key, null);

            if ($val === null) {
                $val = $emr_data[$key] ?? '';
            }
            if (is_array($val)) {
                return array_map('strval', $val);
            }
            if ($val === '' || $val === null) {
                return [];
            }

            $decoded = json_decode((string) $val, true);

            return is_array($decoded) ? array_map('strval', $decoded) : [(string) $val];
        };

        // Opsi select yang sudah punya key di SelectOption diambil dari sana,
        // sisanya dikirim controller sebagai array nilai/label.
        $daftarOpsi = function ($key, $selected, $placeholder) use ($opsi) {
            $html = '<option value="">'.e($placeholder).'</option>';
            foreach ($opsi[$key] ?? [] as $o) {
                $html .= '<option value="'.e($o['value']).'"'.((string) $o['value'] === (string) $selected ? ' selected' : '').'>'.e($o['label']).'</option>';
            }

            return $html;
        };

        $kondisiPulang = old('kondisi_pulang', $emr_data['kondisi_pulang'] ?? 'Sembuh');
        $dietJenis = old('diet_jenis', $emr_data['diet_jenis'] ?? '');
        $bak = old('bak', $emr_data['bak'] ?? '');
        $luka = old('luka', $emr_data['luka'] ?? '');
        $masalahTerpilih = $multi('masalah_keperawatan');

        $edukasiItems = [
            'edukasi_1' => 'Penyakit & Pengobatannya',
            'edukasi_2' => 'Perawatan di rumah',
            'edukasi_3' => 'Perawatan Ibu & Bayi',
            'edukasi_4' => 'Mengatasi Nyeri',
            'edukasi_5' => 'Perawatan Luka',
            'edukasi_6' => 'Persiapan Lingkungan & fasilitas di rumah',
            'edukasi_7' => 'Nasehat Keluarga Berencana',
        ];

        $serahJumlah = [
            'serah_lab' => 'Hasil Laboratorium',
            'serah_rontgen' => 'Hasil Rontgen',
            'serah_ct_scan' => 'Hasil CT Scan',
            'serah_mri' => 'Hasil MRI',
            'serah_usg' => 'Hasil USG / Echo',
            'serah_surat_sakit' => 'Surat Keterangan Sakit',
        ];

        $serahCeklis = [
            'serah_surat_asuransi' => 'Surat Asuransi',
            'serah_resume' => 'Resume Pasien Pulang',
            'serah_buku_bayi' => 'Buku Bayi',
            'serah_gol_darah' => 'Kartu Golongan Darah',
            'serah_skl_bayi' => 'Surat Keterangan Lahir',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Pemulangan"
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
                slug="pemulangan_pasien"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'kondisi_pulang' => 'Kondisi Pulang',
                    'diet_jenis' => 'Diet',
                    'transfer' => 'Mobilisasi',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= BLOK A - IDENTITAS WAKTU ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Waktu Pulang</span>
                </div>
                <div class="p-5 bg-white space-y-3 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_pulang" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Pulang <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_pulang" name="tanggal_pulang" readonly
                                   value="{{ $v('tanggal_pulang') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed outline-none">
                            @error('tanggal_pulang')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_pulang" class="block font-bold text-slate-800 mb-1.5">
                                Jam Pulang <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_pulang" name="waktu_pulang" readonly step="60"
                                   value="{{ substr((string) $v('waktu_pulang'), 0, 5) }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-100 text-slate-500 cursor-not-allowed outline-none">
                            @error('waktu_pulang')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">Tanggal dan jam pulang dicatat otomatis oleh server saat dokumen disimpan.</p>
                </div>
            </div>

            {{-- ================= BLOK B - KONDISI SAAT PULANG ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kondisi Saat Pulang</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="kondisi_pulang" class="block font-bold text-slate-800 mb-1.5">
                                Kondisi Saat Pulang <span class="text-red-500">*</span>
                            </label>
                            <select id="kondisi_pulang" name="kondisi_pulang"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! $daftarOpsi('alasan_pulang', $kondisiPulang, '-- Pilih Kondisi --') !!}
                            </select>
                            @error('kondisi_pulang')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="catatan_alasan_pulang" class="block font-bold text-slate-800 mb-1.5">
                                Catatan Alasan Pulang <span class="text-red-500">*</span>
                            </label>
                            <textarea id="catatan_alasan_pulang" name="catatan_alasan_pulang" rows="3"
                                      placeholder="Penjelasan singkat alasan pasien pulang"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('catatan_alasan_pulang') }}</textarea>
                            @error('catatan_alasan_pulang')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Field kondisional: hanya tampil saat kondisi pulang "Dirujuk" --}}
                    <div id="wrap-dirujuk" class="{{ $kondisiPulang === 'Dirujuk' ? '' : 'hidden' }} grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="dirujuk_ke" class="block font-bold text-slate-800 mb-1.5">
                                Dirujuk Ke <span class="text-red-500">*</span>
                            </label>
                            <select id="dirujuk_ke" name="dirujuk_ke"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white outline-none text-slate-700">
                                {!! $daftarOpsi('tujuan_rujukan', $v('dirujuk_ke'), '-- Pilih Tujuan --') !!}
                            </select>
                            @error('dirujuk_ke')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="rujuk_di_area_sama" class="block font-bold text-slate-800 mb-1.5">
                                Rujukan Berada di Area/Kabupaten/Kota yang Sama <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-4 pt-2">
                                @foreach (['Ya', 'Tidak'] as $pilihan)
                                    <label class="inline-flex items-center gap-1.5 text-sm text-slate-600 cursor-pointer">
                                        <input type="radio" name="rujuk_di_area_sama" value="{{ $pilihan }}" {{ $ya('rujuk_di_area_sama') }}
                                               class="h-4 w-4 border-slate-300 text-blue-600 focus:ring-blue-500/30">
                                        {{ $pilihan }}
                                    </label>
                                @endforeach
                            </div>
                            @error('rujuk_di_area_sama')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Field kondisional: hanya tampil saat kondisi pulang "Meninggal" --}}
                    <div id="wrap-meninggal" class="{{ $kondisiPulang === 'Meninggal' ? '' : 'hidden' }} grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="meninggal" class="block font-bold text-slate-800 mb-1.5">
                                Klasifikasi Kematian <span class="text-red-500">*</span>
                            </label>
                            <select id="meninggal" name="meninggal"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white outline-none text-slate-700">
                                {!! $daftarOpsi('penyebab_kematian', $v('meninggal'), '-- Pilih --') !!}
                            </select>
                            @error('meninggal')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="catatan_meninggal" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal &amp; Jam Kematian <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="catatan_meninggal" name="catatan_meninggal"
                                   value="{{ $v('catatan_meninggal') }}" placeholder="contoh: 12/10/2026 14:30"
                                   class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                            @error('catatan_meninggal')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= BLOK C - TANDA VITAL, EWS & GCS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanda Vital, Skor Nyeri, EWS &amp; GCS</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @foreach ([
                            'td_sistolik' => 'TD Sistolik', 'td_diastolik' => 'TD Diastolik',
                            'nadi' => 'Nadi (/menit)', 'pernapasan' => 'Pernapasan (/menit)',
                            'suhu' => 'Suhu (C)', 'skor_nyeri' => 'Skor Nyeri (0-10)',
                        ] as $name => $label)
                            <div>
                                <label for="{{ $name }}" class="block font-bold text-slate-800 mb-1.5">
                                    {{ $label }} <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="{{ $name }}" name="{{ $name }}" step="any"
                                       value="{{ $v($name) }}"
                                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                @error($name)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- GCS --}}
                        <div class="p-4 border border-slate-200 rounded-lg">
                            <div class="font-bold text-slate-800 mb-3">Glasgow Coma Scale</div>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach (['gcs_e' => 'Eye', 'gcs_m' => 'Motorik', 'gcs_v' => 'Verbal'] as $name => $label)
                                    <div>
                                        <label for="{{ $name }}" class="block text-xs font-medium text-slate-600 mb-1">
                                            {{ $label }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" id="{{ $name }}" name="{{ $name }}"
                                               min="1" max="{{ $name === 'gcs_m' ? 6 : ($name === 'gcs_v' ? 5 : 4) }}"
                                               value="{{ $v($name) }}"
                                               class="w-full border border-slate-300 rounded px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                        @error($name)
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 px-3 py-2 bg-slate-50 border border-slate-200 rounded">
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">Total GCS</div>
                                <div id="gcs_total" class="text-sm font-semibold text-slate-700">
                                    {{ $v('gcs_jumlah') !== '' ? $v('gcs_jumlah') : '-' }}
                                </div>
                            </div>
                        </div>

                        {{-- EWS (computed server-side) --}}
                        <div class="p-4 border border-slate-200 rounded-lg">
                            <div class="font-bold text-slate-800 mb-3">Early Warning Score</div>
                            <div class="flex items-center gap-3">
                                <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded">
                                    <div class="text-[11px] uppercase tracking-wider text-slate-500">Total</div>
                                    <div id="ews_total" class="text-xl font-bold text-slate-700">
                                        {{ $v('total_ews') !== '' ? $v('total_ews') : '-' }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="text-[11px] uppercase tracking-wider text-slate-500">Kategori</div>
                                    <div id="ews_kategori" class="text-sm font-semibold text-slate-700">
                                        {{ $v('kategori_ews') !== '' ? $v('kategori_ews') : '-' }}
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-slate-400">
                                Dihitung otomatis di server dari pernapasan, TD sistolik, nadi, dan suhu.
                                Parameter yang tidak ada di form ini dianggap tidak diukur.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= BLOK D - DIET, ELIMINASI, LUKA & MOBILISASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Diet, Eliminasi, Luka &amp; Mobilisasi</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="diet_jenis" class="block font-bold text-slate-800 mb-1.5">
                                Diet / Nutrisi <span class="text-red-500">*</span>
                            </label>
                            <select id="diet_jenis" name="diet_jenis"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white outline-none text-slate-700">
                                {!! $daftarOpsi('diet_jenis', $dietJenis, '-- Pilih Diet --') !!}
                            </select>
                            @error('diet_jenis')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div id="wrap-diet" class="{{ $dietJenis === 'Diet Khusus' ? '' : 'hidden' }}">
                            <label for="diet_keterangan" class="block font-bold text-slate-800 mb-1.5">
                                Keterangan Diet (Batas Cairan) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="diet_keterangan" name="diet_keterangan"
                                   value="{{ $v('diet_keterangan') }}" placeholder="Contoh: batas cairan 2000 ml/hari"
                                   class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                            @error('diet_keterangan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="bab" class="block font-bold text-slate-800 mb-1.5">
                                Eliminasi BAB <span class="text-red-500">*</span>
                            </label>
                            <select id="bab" name="bab"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white outline-none text-slate-700">
                                {!! $daftarOpsi('eliminasi_bab', $v('bab'), '-- Pilih --') !!}
                            </select>
                            @error('bab')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="bak" class="block font-bold text-slate-800 mb-1.5">
                                Eliminasi BAK <span class="text-red-500">*</span>
                            </label>
                            <select id="bak" name="bak"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white outline-none text-slate-700">
                                {!! $daftarOpsi('eliminasi_bak', $bak, '-- Pilih --') !!}
                            </select>
                            @error('bak')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div id="wrap-bak" class="{{ $bak === 'Inkontinensia' ? '' : 'hidden' }}">
                        <label for="bak_keterangan" class="block font-bold text-slate-800 mb-1.5">
                            Kateter &amp; Tanggal Pemasangan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="bak_keterangan" name="bak_keterangan"
                               value="{{ $v('bak_keterangan') }}" placeholder="Contoh: Kateter Foley, dipasang 12/10/2026"
                               class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                        @error('bak_keterangan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="luka" class="block font-bold text-slate-800 mb-1.5">
                                Luka / Operasi <span class="text-red-500">*</span>
                            </label>
                            <select id="luka" name="luka"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white outline-none text-slate-700">
                                {!! $daftarOpsi('kondisi_luka', $luka, '-- Pilih --') !!}
                            </select>
                            @error('luka')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="transfer" class="block font-bold text-slate-800 mb-1.5">
                                Transfer &amp; Mobilisasi <span class="text-red-500">*</span>
                            </label>
                            <select id="transfer" name="transfer"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white outline-none text-slate-700">
                                {!! $daftarOpsi('tingkat_kemandirian', $v('transfer'), '-- Pilih --') !!}
                            </select>
                            @error('transfer')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div id="wrap-luka" class="{{ $luka === 'Kering' ? '' : 'hidden' }}">
                        <label for="luka_keterangan" class="block font-bold text-slate-800 mb-1.5">
                            Cairan / Keluhan pada Luka <span class="text-red-500">*</span>
                        </label>
                        <textarea id="luka_keterangan" name="luka_keterangan" rows="2"
                                  placeholder="Jelaskan kondisi luka, mis. keluar cairan lokal"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('luka_keterangan') }}</textarea>
                        @error('luka_keterangan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= BLOK E - EDUKASI YANG SUDAH DIBERIKAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Edukasi / Penyuluhan yang Sudah Diberikan</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach ($edukasiItems as $name => $label)
                            <label class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" name="{{ $name }}" value="1" {{ $ya($name) }}
                                       class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/30">
                                <span class="text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @foreach ($edukasiItems as $name => $label)
                        @error($name)
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    @endforeach

                    <div>
                        <label for="edukasi_keterangan" class="block font-bold text-slate-800 mb-1.5">Catatan Materi Edukasi</label>
                        <textarea id="edukasi_keterangan" name="edukasi_keterangan" rows="2"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('edukasi_keterangan') }}</textarea>
                        @error('edukasi_keterangan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= BLOK F - MANAJEMEN NYERI ================= --}}
            <div id="wrap-nyeri" class="{{ $perluNyeri ? '' : 'hidden' }} border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Manajemen Nyeri</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">
                    <p class="text-xs text-slate-400">
                        Blok ini ditampilkan karena discharge planning terakhir menandai pasien membutuhkan manajemen nyeri.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ([
                            'nyeri_terapi' => 'Obat yang Diminum / Anti Nyeri',
                            'nyeri_efek_samping' => 'Efek Samping yang Mungkin Timbul',
                            'nyeri_kapan_ke_rs' => 'Bila Nyeri Bertambah Berat',
                        ] as $name => $label)
                            <div>
                                <label for="{{ $name }}" class="block font-bold text-slate-800 mb-1.5">{{ $label }}</label>
                                <textarea id="{{ $name }}" name="{{ $name }}" rows="3"
                                          class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v($name) }}</textarea>
                                @error($name)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= BLOK G - ANJURAN & MASALAH KEPERAWATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Anjuran &amp; Masalah Keperawatan Selama Dirawat</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">

                    <div>
                        <label for="masalah_keperawatan" class="block font-bold text-slate-800 mb-1.5">
                            Masalah Keperawatan / Kebidanan Selama Dirawat <span class="text-red-500">*</span>
                        </label>
                        <select id="masalah_keperawatan" name="masalah_keperawatan[]" multiple
                                class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white outline-none text-slate-700">
                            @foreach ($masalahKeperawatan as $item)
                                <option value="{{ $item }}" {{ in_array((string) $item, $masalahTerpilih, true) ? 'selected' : '' }}>{{ $item }}</option>
                            @endforeach
                        </select>
                        @error('masalah_keperawatan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="anjuran_pulang" class="block font-bold text-slate-800 mb-1.5">
                            Anjuran Keperawatan Setelah Pulang <span class="text-red-500">*</span>
                        </label>
                        <textarea id="anjuran_pulang" name="anjuran_pulang" rows="4"
                                  placeholder="Anjuran obat, kontrol, aktivitas, dan tanda bahaya"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('anjuran_pulang') }}</textarea>
                        @error('anjuran_pulang')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="keterangan" class="block font-bold text-slate-800 mb-1.5">Keterangan</label>
                        <textarea id="keterangan" name="keterangan" rows="2"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('keterangan') }}</textarea>
                        @error('keterangan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= BLOK H - BARANG & HASIL YANG DISYERAHKAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Barang &amp; Hasil yang Disyerahkan kepada Keluarga</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($serahJumlah as $name => $label)
                            <div>
                                <label for="{{ $name }}" class="block font-bold text-slate-800 mb-1.5">{{ $label }} (lembar)</label>
                                <input type="number" id="{{ $name }}" name="{{ $name }}" min="0" max="99"
                                       value="{{ $v($name) }}" placeholder="0"
                                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                @error($name)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach ($serahCeklis as $name => $label)
                            <label class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" name="{{ $name }}" value="1" {{ $ya($name) }}
                                       class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/30">
                                <span class="text-slate-700">Diserahkan: {{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @foreach ($serahCeklis as $name => $label)
                        @error($name)
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    @endforeach

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="serah_penyerah_bayi" class="block font-bold text-slate-800 mb-1.5">Bayi Disyerahkan Oleh</label>
                            <input type="text" id="serah_penyerah_bayi" name="serah_penyerah_bayi"
                                   value="{{ $v('serah_penyerah_bayi') }}" placeholder="Nama orang yang menerima bayi"
                                   class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                            @error('serah_penyerah_bayi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="serah_lainnya" class="block font-bold text-slate-800 mb-1.5">Lain-lain yang Disyerahkan</label>
                            <input type="text" id="serah_lainnya" name="serah_lainnya"
                                   value="{{ $v('serah_lainnya') }}" placeholder="Keterangan barang lain"
                                   class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                            @error('serah_lainnya')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
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

            // Kondisi pulang: blok rujukan & kematian hanya tampil bila relevan.
            var kondisi = document.getElementById('kondisi_pulang');
            var wrapDirujuk = document.getElementById('wrap-dirujuk');
            var wrapMeninggal = document.getElementById('wrap-meninggal');

            function syncKondisi() {
                var val = kondisi ? kondisi.value : '';
                tampilkan(wrapDirujuk, val === 'Dirujuk', 'grid');
                tampilkan(wrapMeninggal, val === 'Meninggal', 'grid');
            }
            if (kondisi) {
                kondisi.addEventListener('change', syncKondisi);
                syncKondisi();
            }

            // Detail yang hanya relevan untuk pilihan tertentu.
            var diet = document.getElementById('diet_jenis');
            var wrapDiet = document.getElementById('wrap-diet');
            var bak = document.getElementById('bak');
            var wrapBak = document.getElementById('wrap-bak');
            var luka = document.getElementById('luka');
            var wrapLuka = document.getElementById('wrap-luka');

            function syncDetail() {
                tampilkan(wrapDiet, diet ? diet.value === 'Diet Khusus' : false, 'block');
                tampilkan(wrapBak, bak ? bak.value === 'Inkontinensia' : false, 'block');
                tampilkan(wrapLuka, luka ? luka.value === 'Kering' : false, 'block');
            }
            [diet, bak, luka].forEach(function (el) {
                if (el) el.addEventListener('change', syncDetail);
            });
            syncDetail();

            // Total GCS - hanya tampilan; server menghitung ulang sendiri.
            var gcsIds = ['gcs_e', 'gcs_m', 'gcs_v'];
            gcsIds.forEach(function (id) {
                var el = document.getElementById(id);
                var out = document.getElementById('gcs_total');
                if (!el || !out) return;
                el.addEventListener('input', function () {
                    var total = 0, lengkap = true;
                    gcsIds.forEach(function (k) {
                        var v = parseInt((document.getElementById(k) || {}).value || '', 10);
                        if (isNaN(v)) { lengkap = false; return; }
                        total += v;
                    });
                    out.textContent = lengkap ? total : '-';
                });
            });
        });
    </script>

@endsection