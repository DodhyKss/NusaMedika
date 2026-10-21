@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Triage IGD';
        $subtitleForm = 'Pengkajian awal pasien di pintu IGD sebagai dasar penetapan prioritas triase.';
        $routeName = null;
        $routeUrl = url('emr/form/triage_igd');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        // Header pasien + registrasi hanya untuk tampilan (baca-saja).
        $registrasi = $registrasi_detail->registrasi;
        $pasien = $registrasi?->pasien;
        $nasabah = $registrasi?->pasienNasabah;
        $sep = $registrasi?->rujukanSep;
        $prioritasRegistrasi = $registrasi?->prioritas;

        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        // Nilai controlling untuk blok kondisional.
        $alasanKunjungan = (string) $v('alasan_kunjungan');
        $nyeri = (string) $v('nyeri');
        $jenisAnamnesis = (string) $v('jenis_anamnesis');
        $prioritasTriase = (string) $v('prioritas_triase');

        $riwayatFields = [
            'riwayat_penyakit_1', 'riwayat_penyakit_2', 'riwayat_penyakit_3', 'riwayat_penyakit_4',
            'riwayat_penyakit_5', 'riwayat_penyakit_6', 'riwayat_penyakit_7', 'riwayat_penyakit_8',
        ];
        $opsiRiwayat = \App\Helpers\SelectOption::get('riwayat_penyakit_igd');
        $opsiTipeNafas = \App\Helpers\SelectOption::get('tipe_nafas');

        // Badge pada daftar riwayat, mengikuti warna zona triase.
        $badgePrioritas = [];
        foreach (($metaTriase ?? []) as $nilai => $meta) {
            $badgePrioritas[$nilai] = [
                'label' => $nilai === 'HITAM' ? 'HITAM - DOA' : 'Prioritas '.$nilai.' - '.$meta['zona'],
                'class' => $meta['warna'],
            ];
        }

        // Opsi <select> yang tidak punya key SelectOption (skala 0-10 dan
        // "tipe napas" legacy yang hanya menyediakan satu opsi).
        $opsiSkorNyeriHtml = '<option value="">-- Pilih Skor --</option>';
        foreach (range(0, 10) as $skor) {
            $opsiSkorNyeriHtml .= '<option value="'.$skor.'"'.((string) $v('skor_nyeri') === (string) $skor ? ' selected' : '').'>'.$skor.'</option>';
        }

        $opsiTipeNafasHtml = '<option value="">-- Pilih --</option>';
        foreach ($opsiTipeNafas as $opsi) {
            $opsiTipeNafasHtml .= '<option value="'.e($opsi['value']).'"'.((string) $v('tipe_nafas') === (string) $opsi['value'] ? ' selected' : '').'>'.e($opsi['label']).'</option>';
        }
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Triage"
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
                slug="triage_igd"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'tanggal_triase' => 'Tanggal Triase',
                    'prioritas_triase' => 'Prioritas',
                    'nyeri' => 'Nyeri',
                    'jenis_anamnesis' => 'Anamnesis',
                ]"
                :badges="['prioritas_triase' => $badgePrioritas]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= HEADER PASIEN (baca-saja) ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Data Pasien</span>
                </div>
                <div class="p-5 bg-white">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 text-sm">
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">No. MR</div>
                            <div class="font-medium text-slate-700">{{ $pasien?->no_mr ?? '-' }}</div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">Nama Pasien</div>
                            <div class="font-medium text-slate-700">{{ $pasien?->nama_pasien ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">Tempat, Tanggal Lahir</div>
                            <div class="font-medium text-slate-700">
                                {{ $pasien?->tempat_lahir ?? '-' }}{{ $pasien?->tgl_lahir ? ', '.\Carbon\Carbon::parse($pasien->tgl_lahir)->format('d/m/Y') : '' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">Umur</div>
                            <div class="font-medium text-slate-700">
                                {{ $pasien?->tgl_lahir ? \Carbon\Carbon::parse($pasien->tgl_lahir)->diff(\Carbon\Carbon::now())->format('%y th %m bl %d hr') : '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">Tanggal Layanan</div>
                            <div class="font-medium text-slate-700">
                                {{ $registrasi?->tgl_masuk ? \Carbon\Carbon::parse($registrasi->tgl_masuk)->format('d/m/Y H:i') : '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">No. Bukti Layanan</div>
                            <div class="font-medium text-slate-700">{{ $sep?->no_sep ?? $nasabah?->no_peserta ?? '-' }}</div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">Prioritas Registrasi (pembanding)</div>
                            <div class="font-medium text-slate-700">
                                {{ $prioritasRegistrasi ?: 'Belum diisi' }}
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">
                        Prioritas pada form ini tidak menulis ulang kolom prioritas registrasi. Ubah melalui menu IGD.
                    </p>
                </div>
            </div>

            {{-- ================= RINGKASAN PENGKAJIAN AWAL KEPERAWATAN ================= --}}
            @if (! empty($ringkasan['ada']))
                <div class="border border-slate-200 rounded-sm shadow-sm">
                    <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                        <span class="text-cyan-600 font-medium text-[15px]">Ringkasan Pengkajian Awal Keperawatan</span>
                    </div>
                    <div class="p-5 bg-white">
                        <div class="text-xs text-slate-400 mb-3">
                            Terakhir diisi {{ $ringkasan['waktu'] ?? '-' }} - panel baca-saja, bukan bagian dari triase ini.
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">Keluhan</div>
                                <div class="text-slate-700">{{ $ringkasan['nilai']['keluhan'] ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">Diagnosa Medis</div>
                                <div class="text-slate-700">{{ $ringkasan['nilai']['diagnosa_medis'] ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">Riwayat Penyakit Sebelumnya</div>
                                <div class="text-slate-700">{{ $ringkasan['nilai']['riwayat_penyakit_sebelumnya'] ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">Alergi</div>
                                <div class="text-slate-700">{{ $ringkasan['nilai']['alergi'] ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ================= WAKTU PENGKAJIAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Waktu Pengkajian</span>
                </div>
                <div class="p-5 bg-white">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="tanggal_triase" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Triase <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_triase" name="tanggal_triase"
                                   value="{{ $v('tanggal_triase') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_triase')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_triase" class="block font-bold text-slate-800 mb-1.5">
                                Jam Triase <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_triase" name="waktu_triase" step="60"
                                   value="{{ $v('waktu_triase') ? substr((string) $v('waktu_triase'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_triase')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PEMERIKSAAN FISIK ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Pemeriksaan Fisik</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="keadaan_umum" class="block font-bold text-slate-800 mb-1.5">
                                Keadaan Umum <span class="text-red-500">*</span>
                            </label>
                            <select id="keadaan_umum" name="keadaan_umum"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('keadaan_umum_igd', $v('keadaan_umum'), '-- Pilih Keadaan Umum --') !!}
                            </select>
                            @error('keadaan_umum')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="berat_badan" class="block font-bold text-slate-800 mb-1.5">
                                Berat Badan (kg) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="berat_badan" name="berat_badan" step="any" min="0.5" max="400"
                                   value="{{ $v('berat_badan') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('berat_badan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="tinggi_badan" class="block font-bold text-slate-800 mb-1.5">
                                Tinggi Badan (cm) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="tinggi_badan" name="tinggi_badan" step="any" min="30" max="250"
                                   value="{{ $v('tinggi_badan') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tinggi_badan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @foreach ([
                            'suhu' => 'Suhu (C)',
                            'frekuensi_nafas' => 'Frekuensi Nafas (/menit)',
                            'saturasi' => 'Saturasi (%)',
                            'nadi' => 'Nadi (/menit)',
                            'td_sistolik' => 'TD Sistolik (mmHg)',
                            'td_diastolik' => 'TD Diastolik (mmHg)',
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

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="jalan_nafas" class="block font-bold text-slate-800 mb-1.5">
                                Jalan Nafas <span class="text-red-500">*</span>
                            </label>
                            <select id="jalan_nafas" name="jalan_nafas"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('jalan_nafas', $v('jalan_nafas'), '-- Pilih Jalan Nafas --') !!}
                            </select>
                            @error('jalan_nafas')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="tipe_nafas" class="block font-bold text-slate-800 mb-1.5">Tipe Nafas</label>
                            <select id="tipe_nafas" name="tipe_nafas"
                                    class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! $opsiTipeNafasHtml !!}
                            </select>
                            @error('tipe_nafas')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="sirkulasi" class="block font-bold text-slate-800 mb-1.5">
                                Sirkulasi <span class="text-red-500">*</span>
                            </label>
                            <select id="sirkulasi" name="sirkulasi"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('sirkulasi', $v('sirkulasi'), '-- Pilih Sirkulasi --') !!}
                            </select>
                            @error('sirkulasi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="akral" class="block font-bold text-slate-800 mb-1.5">Akral (Kapasitas Vital)</label>
                            <input type="text" id="akral" name="akral" maxlength="100"
                                   value="{{ $v('akral') }}"
                                   placeholder="Contoh: Baik"
                                   class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                            @error('akral')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="kesadaran" class="block font-bold text-slate-800 mb-1.5">
                                Tingkat Kesadaran <span class="text-red-500">*</span>
                            </label>
                            <select id="kesadaran" name="kesadaran"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('kesadaran', $v('kesadaran'), '-- Pilih Kesadaran --') !!}
                            </select>
                            @error('kesadaran')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="pupil" class="block font-bold text-slate-800 mb-1.5">
                                Pupil <span class="text-red-500">*</span>
                            </label>
                            <select id="pupil" name="pupil"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('pupil', $v('pupil'), '-- Pilih Pupil --') !!}
                            </select>
                            @error('pupil')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="max-w-xs">
                        <div>
                            <label for="refleks_cahaya" class="block font-bold text-slate-800 mb-1.5">
                                Refleks Cahaya (mm) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="refleks_cahaya" name="refleks_cahaya" step="1" min="0" max="10"
                                   value="{{ $v('refleks_cahaya') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('refleks_cahaya')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= KELUHAN UTAMA & ANAMNESIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Keluhan Utama &amp; Anamnesis</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <div class="block font-bold text-slate-800 mb-1.5">
                            Jenis Anamnesis <span class="text-red-500">*</span>
                        </div>
                        <div class="flex flex-wrap gap-4">
                            @foreach (\App\Helpers\SelectOption::get('jenis_anamnesis') as $opsi)
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="jenis_anamnesis" value="{{ $opsi['value'] }}"
                                           class="form-radio text-blue-600 focus:ring-blue-500"
                                           {{ (string) $v('jenis_anamnesis') === (string) $opsi['value'] ? 'checked' : '' }}>
                                    <span class="text-slate-700">{{ $opsi['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('jenis_anamnesis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="alasan_kunjungan" class="block font-bold text-slate-800 mb-1.5">
                                Alasan Kunjungan <span class="text-red-500">*</span>
                            </label>
                            <select id="alasan_kunjungan" name="alasan_kunjungan"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('alasan_kunjungan_igd', $v('alasan_kunjungan'), '-- Pilih Alasan Kunjungan --') !!}
                            </select>
                            @error('alasan_kunjungan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div id="wrap-alasan-lain" class="{{ $alasanKunjungan === 'Lainnya' ? '' : 'hidden' }}">
                            <label for="alasan_kunjungan_lain" class="block font-bold text-slate-800 mb-1.5">
                                Keterangan Alasan Kunjungan Lainnya
                            </label>
                            <input type="text" id="alasan_kunjungan_lain" name="alasan_kunjungan_lain" maxlength="200"
                                   value="{{ $v('alasan_kunjungan_lain') }}"
                                   placeholder="Tuliskan alasan kunjungan"
                                   class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                            @error('alasan_kunjungan_lain')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= SKALA NYERI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Skala Nyeri</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <div class="block font-bold text-slate-800 mb-1.5">
                            Nyeri <span class="text-red-500">*</span>
                        </div>
                        <div class="flex flex-wrap gap-4">
                            @foreach (['Ya, Nyeri' => 'Ya, Nyeri', 'Tidak Nyeri' => 'Tidak Nyeri'] as $nilai => $label)
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="nyeri" value="{{ $nilai }}"
                                           class="form-radio text-blue-600 focus:ring-blue-500"
                                           {{ $nyeri === $nilai ? 'checked' : '' }}>
                                    <span class="text-slate-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('nyeri')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="wrap-nyeri" class="{{ $nyeri === 'Ya, Nyeri' ? '' : 'hidden' }} space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="skor_nyeri" class="block font-bold text-slate-800 mb-1.5">
                                    Skor Nyeri (0 - 10)
                                </label>
                                <select id="skor_nyeri" name="skor_nyeri"
                                        class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                    {!! $opsiSkorNyeriHtml !!}
                                </select>
                                @error('skor_nyeri')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="lokasi_nyeri" class="block font-bold text-slate-800 mb-1.5">Lokasi Nyeri</label>
                                <input type="text" id="lokasi_nyeri" name="lokasi_nyeri" maxlength="200"
                                       value="{{ $v('lokasi_nyeri') }}"
                                       placeholder="Contoh: Dada kanan"
                                       class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                                @error('lokasi_nyeri')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="frekuensi_nyeri" class="block font-bold text-slate-800 mb-1.5">Frekuensi Nyeri</label>
                                <select id="frekuensi_nyeri" name="frekuensi_nyeri"
                                        class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                    {!! \App\Helpers\SelectOption::render('frekuensi_nyeri', $v('frekuensi_nyeri'), '-- Pilih Frekuensi --') !!}
                                </select>
                                @error('frekuensi_nyeri')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="karakteristik_nyeri" class="block font-bold text-slate-800 mb-1.5">Karakteristik Nyeri</label>
                                <select id="karakteristik_nyeri" name="karakteristik_nyeri"
                                        class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                    {!! \App\Helpers\SelectOption::render('karakteristik_nyeri', $v('karakteristik_nyeri'), '-- Pilih Karakteristik --') !!}
                                </select>
                                @error('karakteristik_nyeri')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= RIWAYAT PENYAKIT ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Penyakit</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">

                    <div id="wrap-riwayat" class="{{ $jenisAnamnesis === 'Allo Anamnesis' ? '' : 'hidden' }} space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($riwayatFields as $riwayat)
                                <?php
                                    $noRiwayat = substr($riwayat, strlen('riwayat_penyakit_'));
                                    $terpakaiLain = [];
                                    foreach ($riwayatFields as $cek) {
                                        if ($cek !== $riwayat && $v($cek) !== '') {
                                            $terpakaiLain[] = (string) $v($cek);
                                        }
                                    }
                                ?>
                                <div>
                                    <label for="{{ $riwayat }}" class="block font-bold text-slate-800 mb-1.5">Riwayat Penyakit {{ $noRiwayat }}</label>
                                    <select id="{{ $riwayat }}" name="{{ $riwayat }}"
                                            class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                        <option value="">-- Pilih Riwayat Penyakit --</option>
                                        @foreach ($opsiRiwayat as $opsi)
                                            @continue(in_array((string) $opsi['value'], $terpakaiLain, true))
                                            <option value="{{ $opsi['value'] }}" {{ (string) $v($riwayat) === (string) $opsi['value'] ? 'selected' : '' }}>
                                                {{ $opsi['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error($riwayat)
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        <div id="wrap-riwayat-lain" class="{{ (string) $v('riwayat_penyakit_8') === 'Lainnya' ? '' : 'hidden' }}">
                            <label for="riwayat_penyakit_lain" class="block font-bold text-slate-800 mb-1.5">
                                Riwayat Penyakit Lainnya
                            </label>
                            <input type="text" id="riwayat_penyakit_lain" name="riwayat_penyakit_lain" maxlength="200"
                                   value="{{ $v('riwayat_penyakit_lain') }}"
                                   placeholder="Tuliskan riwayat penyakit lainnya"
                                   class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                            @error('riwayat_penyakit_lain')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <p id="info-riwayat" class="{{ $jenisAnamnesis === 'Allo Anamnesis' ? 'hidden' : '' }} text-xs text-slate-400">
                        Blok riwayat penyakit hanya dipakai bila jenis anamnesis adalah Allo Anamnesis.
                    </p>
                </div>
            </div>

            {{-- ================= SKRINING RISIKO JATUH ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Skrining Risiko Jatuh</span>
                </div>
                <div class="p-5 bg-white">
                    <div class="block font-bold text-slate-800 mb-1.5">
                        Tingkat Risiko Jatuh <span class="text-red-500">*</span>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        @foreach (['Beresiko', 'Tidak Beresiko'] as $label)
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="risiko_jatuh" value="{{ $label }}"
                                       class="form-radio text-blue-600 focus:ring-blue-500"
                                       {{ (string) $v('risiko_jatuh') === $label ? 'checked' : '' }}>
                                <span class="text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('risiko_jatuh')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- ================= ZONA TRIAGE IGD ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Zona Triase IGD</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="prioritas_triase" class="block font-bold text-slate-800 mb-1.5">
                                Prioritas Triase <span class="text-red-500">*</span>
                            </label>
                            <select id="prioritas_triase" name="prioritas_triase"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('prioritas_triase_igd', $v('prioritas_triase'), '-- Pilih Prioritas Triase --') !!}
                            </select>
                            @error('prioritas_triase')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <div class="block font-bold text-slate-800 mb-1.5">Zona Perawatan &amp; Respon Time</div>
                            <div id="triase_badge" class="px-3 py-2.5 border border-slate-200 rounded text-sm font-medium text-slate-500 bg-slate-50">
                                Belum ada prioritas dipilih
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto border border-slate-200 rounded">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="px-3 py-2">Prioritas</th>
                                    <th class="px-3 py-2">Keterangan</th>
                                    <th class="px-3 py-2">Zona Perawatan</th>
                                    <th class="px-3 py-2">Respon Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-600">
                                @foreach (\App\Helpers\SelectOption::get('prioritas_triase_igd') as $opsi)
                                    <tr class="{{ $prioritasTriase === (string) $opsi['value'] ? 'bg-blue-50/70 font-semibold text-slate-800' : '' }}">
                                        <td class="px-3 py-2 {{ $opsi['class'] ?? '' }}">{{ $opsi['value'] }}</td>
                                        <td class="px-3 py-2">{{ $opsi['label'] }}</td>
                                        <td class="px-3 py-2">{{ ($metaTriase[$opsi['value']]['zona'] ?? '-') }}</td>
                                        <td class="px-3 py-2">{{ ($metaTriase[$opsi['value']]['waktu'] ?? '-') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ================= CATATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Catatan</span>
                </div>
                <div class="p-5 bg-white">
                    <label for="catatan" class="block font-bold text-slate-800 mb-1.5">Catatan Tambahan</label>
                    <textarea id="catatan" name="catatan" rows="3" maxlength="2000"
                              placeholder="Tindak lanjut, konsultan yang diminta, atau keterangan lain"
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
            // (lihat AGENTS.md - utility .hidden Tailwind bisa dikalahkan oleh
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

            function nilaiTerpilih(name) {
                var el = document.querySelector('input[name="' + name + '"]:checked');
                return el ? el.value : '';
            }

            // Blok riwayat penyakit: hanya untuk Allo Anamnesis.
            var riwayat = document.getElementById('wrap-riwayat');
            var infoRiwayat = document.getElementById('info-riwayat');

            function syncRiwayat() {
                var allo = nilaiTerpilih('jenis_anamnesis') === 'Allo Anamnesis';
                tampilkan(riwayat, allo, 'block');
                tampilkan(infoRiwayat, ! allo, 'block');
            }

            document.querySelectorAll('input[name="jenis_anamnesis"]').forEach(function (el) {
                el.addEventListener('change', syncRiwayat);
            });
            syncRiwayat();

            // Keterangan riwayat penyakit "Lainnya" pada baris ke-8.
            var riwayat8 = document.getElementById('riwayat_penyakit_8');
            var wrapRiwayatLain = document.getElementById('wrap-riwayat-lain');

            function syncRiwayatLain() {
                var perlu = riwayat8 && riwayat8.value === 'Lainnya';
                tampilkan(wrapRiwayatLain, !! perlu, 'block');
            }
            if (riwayat8) {
                riwayat8.addEventListener('change', syncRiwayatLain);
                syncRiwayatLain();
            }

            // Keterangan alasan kunjungan "Lainnya".
            var alasanKunjungan = document.getElementById('alasan_kunjungan');
            var wrapAlasanLain = document.getElementById('wrap-alasan-lain');

            function syncAlasanLain() {
                var perlu = alasanKunjungan && alasanKunjungan.value === 'Lainnya';
                tampilkan(wrapAlasanLain, !! perlu, 'block');
            }
            if (alasanKunjungan) {
                alasanKunjungan.addEventListener('change', syncAlasanLain);
                syncAlasanLain();
            }

            // Detail skala nyeri: hanya saat pasien dinyatakan nyeri.
            var wrapNyeri = document.getElementById('wrap-nyeri');

            function syncNyeri() {
                var ya = nilaiTerpilih('nyeri') === 'Ya, Nyeri';
                tampilkan(wrapNyeri, ya, 'block');
            }

            document.querySelectorAll('input[name="nyeri"]').forEach(function (el) {
                el.addEventListener('change', syncNyeri);
            });
            syncNyeri();

            // Badge zona perawatan + respon time mengikuti prioritas terpilih.
            var meta = @json($metaTriase);
            var prioritas = document.getElementById('prioritas_triase');
            var badge = document.getElementById('triase_badge');

            function syncBadge() {
                if (!badge) return;
                var nilai = prioritas ? prioritas.value : '';
                var info = meta[nilai];
                if (! info) {
                    badge.className = 'px-3 py-2.5 border border-slate-200 rounded text-sm font-medium text-slate-500 bg-slate-50';
                    badge.textContent = 'Belum ada prioritas dipilih';
                    return;
                }
                badge.className = 'px-3 py-2.5 border border-slate-200 rounded text-sm font-semibold ' + info.warna;
                badge.textContent = info.zona + ' - Respon Time ' + info.waktu;
            }
            if (prioritas) {
                prioritas.addEventListener('change', syncBadge);
                syncBadge();
            }
        });
    </script>

@endsection
