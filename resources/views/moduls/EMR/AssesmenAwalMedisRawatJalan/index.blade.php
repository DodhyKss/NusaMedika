@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Assesmen Awal Medis Rawat Jalan';
        $subtitleForm = 'Penilaian awal pasien saat datang di rawat jalan: keluhan utama, tanda vital, dan diagnosa kerja.';
        $routeName = null;
        $routeUrl = url('emr/form/assesmen_awal_medis_rawat_jalan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        // Nilai yang sudah tersimpan (atau kosong saat membuat baru).
        $nyeriTerpilih = (string) old('nyeri', $emr_data['nyeri'] ?? 'Tidak');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Assesmen Awal"
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
                slug="assesmen_awal_medis_rawat_jalan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="['tanggal_assesmen' => 'Tanggal', 'keluhan_utama' => 'Keluhan Utama', 'total_ews' => 'Skor EWS', 'kategori_ews' => 'Risiko EWS', 'diagnosa_kerja' => 'Diagnosa Kerja']"
                :badges="[
                    'kategori_ews' => [
                        'Rendah' => ['label' => 'Risiko Rendah', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                        'Sedang' => ['label' => 'Risiko Sedang', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                        'Tinggi' => ['label' => 'Risiko Tinggi', 'class' => 'bg-red-50 text-red-700 border-red-200'],
                    ],
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= Keluhan Utama ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Keluhan Utama</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <label for="keluhan_utama" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Keluhan Utama <span class="text-red-500">*</span>
                        </label>
                        <textarea id="keluhan_utama" name="keluhan_utama" rows="3"
                                  placeholder="Contoh: Demam sejak 3 hari disertai batuk dan pilek"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('keluhan_utama', $emr_data['keluhan_utama'] ?? '') }}</textarea>
                        @error('keluhan_utama')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal & jam: wajib diisi manual, tanpa default --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_assesmen" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Tanggal Assesmen <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_assesmen" name="tanggal_assesmen"
                                   value="{{ old('tanggal_assesmen', $emr_data['tanggal_assesmen'] ?? '') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_assesmen')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_assesmen" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Jam Assesmen <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_assesmen" name="waktu_assesmen" step="60"
                                   value="{{ old('waktu_assesmen', substr((string) ($emr_data['waktu_assesmen'] ?? ''), 0, 5)) }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_assesmen')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Dokter pemeriksa --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-select_dokter
                            :selected="old('dokter_pemeriksa_id', $emr_data['dokter_pemeriksa_id'] ?? '')"
                            name="dokter_pemeriksa_id"
                            id="dokter_pemeriksa_id"
                            label="Dokter Pemeriksa"
                            placeholder="-- Pilih Dokter --"
                            required
                        />
                    </div>
                </div>
            </div>

            {{-- ================= Anamnesis & Riwayat Penyakit ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-purple-50 border-b border-slate-200">
                    <span class="text-purple-600 font-medium text-[15px]">Anamnesis &amp; Riwayat Penyakit</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    {{-- Anamnesis --}}
                    <div>
                        <label for="anamnesis" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Anamnesis
                        </label>
                        <textarea id="anamnesis" name="anamnesis" rows="5"
                                  placeholder="Tulis hasil anamnesis: riwayat penyakit dan keluhan utama, riwayat kehamilan dan persalinan, riwayat ginekologis, riwayat kontrasepsi, kebiasaan (merokok, alkohol), riwayat obat yang biasa dikonsumsi, dan lain-lain."
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('anamnesis', $emr_data['anamnesis'] ?? '') }}</textarea>
                        <p class="mt-1.5 text-xs text-slate-400">Uraikan hasil anamnesis sesuai kelainan yang ditemukan. Kosongkan bila tidak ada kelainan.</p>
                        @error('anamnesis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Riwayat penyakit dahulu --}}
                    <div>
                        <label for="riwayat_penyakit_dahulu" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Riwayat Penyakit Dahulu
                        </label>
                        <textarea id="riwayat_penyakit_dahulu" name="riwayat_penyakit_dahulu" rows="4"
                                  placeholder="Contoh: Hipertensi sejak 2018, DM tipe 2 sejak 2020, riwayat operasi appendectomy 2015. Tulis Tidak ada bila tidak ada riwayat."
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('riwayat_penyakit_dahulu', $emr_data['riwayat_penyakit_dahulu'] ?? '') }}</textarea>
                        <p class="mt-1.5 text-xs text-slate-400">Riwayat penyakit yang pernah dialami sebelumnya, termasuk komorbid dan riwayat operasi.</p>
                        @error('riwayat_penyakit_dahulu')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            {{-- ================= Tanda Vital ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                    <span class="text-slate-600 font-medium text-[15px]">Tanda Vital</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                        {{-- Tekanan darah: sistolik & diastolik terpisah --}}
                        <div>
                            <label for="td_sistolik" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Tekanan Sistolik (mmHg)
                            </label>
                            <input type="number" step="1" min="0" id="td_sistolik" name="td_sistolik"
                                   value="{{ old('td_sistolik', $emr_data['td_sistolik'] ?? '') }}" placeholder="120"
                                   class="w-full text-sm text-right border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('td_sistolik')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="td_diastolik" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Tekanan Diastolik (mmHg)
                            </label>
                            <input type="number" step="1" min="0" id="td_diastolik" name="td_diastolik"
                                   value="{{ old('td_diastolik', $emr_data['td_diastolik'] ?? '') }}" placeholder="80"
                                   class="w-full text-sm text-right border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('td_diastolik')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nadi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Nadi (/menit)
                            </label>
                            <input type="number" step="1" min="0" id="nadi" name="nadi"
                                   value="{{ old('nadi', $emr_data['nadi'] ?? '') }}" placeholder="80"
                                   class="w-full text-sm text-right border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('nadi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="suhu" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Suhu (&#176;C)
                            </label>
                            <input type="number" step="0.1" min="0" id="suhu" name="suhu"
                                   value="{{ old('suhu', $emr_data['suhu'] ?? '') }}" placeholder="36.5"
                                   class="w-full text-sm text-right border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('suhu')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pernapasan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Pernapasan (/menit)
                            </label>
                            <input type="number" step="1" min="0" id="pernapasan" name="pernapasan"
                                   value="{{ old('pernapasan', $emr_data['pernapasan'] ?? '') }}" placeholder="20"
                                   class="w-full text-sm text-right border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('pernapasan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="saturasi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                SpO2 (%)
                            </label>
                            <input type="number" step="1" min="0" max="100" id="saturasi" name="saturasi"
                                   value="{{ old('saturasi', $emr_data['saturasi'] ?? '') }}" placeholder="98"
                                   class="w-full text-sm text-right border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('saturasi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="berat_badan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Berat Badan (kg)
                            </label>
                            <input type="number" step="0.1" min="0" id="berat_badan" name="berat_badan"
                                   value="{{ old('berat_badan', $emr_data['berat_badan'] ?? '') }}" placeholder="60"
                                   class="w-full text-sm text-right border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('berat_badan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="tinggi_badan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Tinggi Badan (cm)
                            </label>
                            <input type="number" step="0.1" min="0" id="tinggi_badan" name="tinggi_badan"
                                   value="{{ old('tinggi_badan', $emr_data['tinggi_badan'] ?? '') }}" placeholder="165"
                                   class="w-full text-sm text-right border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('tinggi_badan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Kesadaran & oksigen juga masuk Tanda Vital karena keduanya
                         dipakai langsung oleh skor EVM. Dipindah ke sini supaya
                         semua input EWS terkumpul di satu panel, bukan tersebar
                         di panel Tanda Vital, EVM, dan Assesmen. --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label for="kesadaran" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Tingkat Kesadaran
                            </label>
                            <select id="kesadaran" name="kesadaran"
                                    class="select2 w-full text-sm border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('kesadaran', old('kesadaran', $emr_data['kesadaran'] ?? null), '-- Pilih Kesadaran --') !!}
                            </select>
                            @error('kesadaran')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="oksigen" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Air atau Oksigen
                            </label>
                            <select id="oksigen" name="oksigen"
                                    class="select2 w-full text-sm border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('oksigen', old('oksigen', $emr_data['oksigen'] ?? null), '-- Pilih --') !!}
                            </select>
                            @error('oksigen')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <p class="text-xs text-slate-400">
                        Ketujuh parameter EVM (pernapasan, SpO2, oksigen, sistolik, nadi, kesadaran, suhu)
                        diisi di panel ini &mdash; tidak perlu diketik ulang di tabel EVM.
                    </p>

                    {{-- BMI: TURUNAN, dihitung ulang di server (filteredData).
                         Kolom ini hanya tampilan, TIDAK punya input name. --}}
                    <div class="flex items-center gap-3 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                        <span class="text-[11px] uppercase tracking-wider text-slate-500">BMI</span>
                        <span id="preview_bmi" class="text-sm font-bold text-slate-700">
                            {{ old('bmi', $emr_data['bmi'] ?? '') !== '' ? old('bmi', $emr_data['bmi'] ?? '') : '-' }}
                        </span>
                        <span id="preview_bmi_kategori" class="text-xs text-slate-500"></span>
                        <p class="ml-auto text-xs text-slate-400">Dihitung otomatis dari berat &amp; tinggi badan.</p>
                    </div>
                </div>
            </div>

            {{-- ================= EVM / Early Warning Score =================
                 Panel ini RINGKASAN: tidak ada input angka di sini. Semua 7
                 parameter diisi pada panel "Tanda Vital" di atas — klik tombol
                 "Isi" pada baris untuk melompat ke inputnya. --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-red-50 border-b border-slate-200 flex items-center justify-between gap-3">
                    <span class="text-red-600 font-medium text-[15px]">EVM (Early Warning Score)</span>
                    <span id="ews_status" class="text-xs text-right {{ $ews['lengkap'] ? 'text-emerald-600' : 'text-slate-500' }}">
                        @if ($ews['lengkap'])
                            Lengkap{{ $ews['na'] ? ' · '.$ews['terukur'].' terukur, '.count($ews['na']).' tidak diukur' : '' }}
                        @else
                            Belum lengkap ({{ $ews['terisi'] }}/{{ count(\App\Helpers\EwsHelper::PARAMETER) }})
                        @endif
                    </span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">

                    {{-- Peringatan parameter yang belum "diselesaikan": masih kosong, dan
                         tidak ditandai tidak diukur. Dirender dari server supaya
                         tetap informatif sebelum JS berjalan. --}}
                    <div id="ews_peringatan" class="px-3 py-2.5 rounded-lg border text-xs {{ $ews['lengkap'] ? 'hidden' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
                        @if (! $ews['lengkap'])
                            <span class="font-semibold">Parameter belum diisi:</span>
                            {{ collect($ews['detail'])->filter(fn ($b) => $b['skor'] === null && ! $b['na'])->pluck('label')->join(', ') ?: '-' }}.
                            Klik <strong>Isi</strong> untuk melompat ke inputnya, atau centang
                            <strong>Tidak Diukur</strong> bila tidak bisa diukur.
                        @endif
                    </div>

                    {{-- Rincian skor per parameter.
                         Kolom "Tidak Diukur" untuk parameter yang tidak bisa diukur
                         (mis. tidak ada pulse oximeter). Parameter bertanda ini
                         dihitung sudah selesai dengan kontribusi 0, dan namanya
                         disimpan supaya jejaknya tetap terbaca. --}}
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="w-full text-left" style="min-width: 680px;">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Parameter</th>
                                    @unless ($isView)
                                        <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:80px;">Isi</th>
                                    @endunless
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:110px;">Nilai</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:90px;">Skor</th>
                                    @unless ($isView)
                                        <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:120px;">Tidak Diukur</th>
                                    @endunless
                                </tr>
                            </thead>
                            {{-- Skor & total di-render dari hasil hitung SERVER supaya tampilan benar
                                 sejak halaman dibuka (sebelum JS jalan). JS lalu
                                 menghitung ulang saat input berubah.

                                 Total SELALU ditampilkan, bahkan saat parameter belum
                                 lengkap: memakai jumlah berjalan (running total) dari
                                 parameter yang sudah terisi. Kategori risiko hanya
                                 ditampilkan bila semua parameter terisi atau ditandai
                                 tidak diukur, karena skor parsial bisa terlalu rendah. --}}
                            <tbody id="ews_body" class="divide-y divide-slate-100">
                                @foreach ($ews['detail'] as $baris)
                                    <tr data-ews-param="{{ $baris['parameter'] }}" class="{{ $baris['na'] ? 'bg-slate-50' : '' }}">
                                        <td class="px-3 py-2 text-slate-700">
                                            {{ $baris['label'] }}
                                            @unless ($isView)
                                                <span class="block text-[10px] text-slate-400 font-normal normal-case">diisi di panel Tanda Vital</span>
                                            @endunless
                                        </td>
                                        @unless ($isView)
                                            {{-- Tombol lompat:_scroll ke input parameter lalu
                                                 sorot, jadi petugas tidak perlu mencari
                                                 sendirian di panel lain. --}}
                                            <td class="px-3 py-2 text-center">
                                                <button type="button" data-ews-lompat="{{ $baris['parameter'] }}"
                                                        class="ews-lompat inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded transition-colors"
                                                        title="Lompat ke input {{ $baris['label'] }}">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                                    Isi
                                                </button>
                                            </td>
                                        @endunless
                                        <td class="px-3 py-2 text-center text-slate-600" data-ews-nilai>{{ $baris['nilai'] }}</td>
                                        <td data-ews-skor class="{{ $baris['na']
                                            ? 'px-3 py-2 text-center text-slate-400 bg-slate-100 rounded'
                                            : ($baris['skor'] === null
                                                ? 'px-3 py-2 text-center text-slate-300'
                                                : 'px-3 py-2 text-center font-bold border rounded '.\App\Helpers\EwsHelper::badgeSkor((int) $baris['skor'])) }}">
                                            {{ $baris['skor'] ?? \App\Helpers\EwsHelper::LABEL_TIDAK_DIUKUR }}
                                        </td>
                                        @unless ($isView)
                                            <td class="px-3 py-2 text-center">
                                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                                    <input type="checkbox" name="ews_na[]" value="{{ $baris['parameter'] }}"
                                                           class="ews-na rounded text-amber-600 focus:ring-amber-500 w-3.5 h-3.5"
                                                           @checked(in_array($baris['parameter'], $ews['na'], true))>
                                                    <span class="text-[11px] text-slate-500">Tidak diukur</span>
                                                </label>
                                            </td>
                                        @endunless
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-slate-50 border-t border-slate-200">
                                <tr>
                                    <th class="px-3 py-2.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Total Skor</th>
                                    @unless ($isView)
                                        <td></td>
                                    @endunless
                                    <td class="px-3 py-2.5 text-center text-lg font-bold text-slate-800" id="ews_total">
                                        {{ $ews['total'] }}
                                    </td>
                                    <td class="px-3 py-2.5 text-center">
                                        <span id="ews_kategori" class="inline-block border rounded px-2 py-0.5 text-[11px] font-semibold {{ $ews['lengkap'] ? \App\Helpers\EwsHelper::badgeClass($ews['kategori']) : 'bg-slate-50 text-slate-400 border-slate-200' }}">
                                            {{ $ews['lengkap'] ? 'Risiko '.$ews['kategori'] : 'Belum lengkap' }}
                                            @if ($ews['lengkap'] && $ews['na'])
                                                <span class="font-normal">({{ $ews['terukur'] }}/{{ count(\App\Helpers\EwsHelper::PARAMETER) }} terukur)</span>
                                            @endif
                                        </span>
                                    </td>
                                    @unless ($isView)
                                        <td class="px-3 py-2.5 text-center text-[11px] text-slate-500" id="ews_na_info">
                                            {{ $ews['na'] ? count($ews['na']).' parameter tidak diukur' : '' }}
                                        </td>
                                    @endunless
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <p class="text-xs text-slate-400">
                        Skor 0&ndash;3 = risiko rendah, 4&ndash;6 = sedang, &ge; 7 = tinggi.
                        Parameter yang tidak bisa diukur (mis. tidak ada alat) centang
                        <strong>Tidak Diukur</strong> &mdash; parameter itu dihitung selesai dengan kontribusi 0,
                        dan namanya ikut tersimpan di riwayat. Angka final selalu dihitung ulang di server.
                    </p>
                </div>
            </div>

            {{-- ================= Assesmen ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-yellow-50 border-b border-slate-200">
                    <span class="text-yellow-700 font-medium text-[15px]">Assesmen</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    {{-- Kesadaran sudah pindah ke panel Tanda Vital (dipakai
                         langsung oleh skor EVM), jadi di sini tidak ada lagi. --}}
                    {{-- Tujuan kunjungan --}}
                    <div>
                        <label for="tujuan_kunjungan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Tujuan Kunjungan <span class="text-red-500">*</span>
                        </label>
                        <select id="tujuan_kunjungan" name="tujuan_kunjungan"
                                class="select2 w-full sm:w-80 text-sm border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            {!! \App\Helpers\SelectOption::render('tujuan_kunjungan', old('tujuan_kunjungan', $emr_data['tujuan_kunjungan'] ?? null), '-- Pilih Tujuan --') !!}
                        </select>
                        @error('tujuan_kunjungan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nyeri --}}
                    <div>
                        <span class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Nyeri <span class="text-red-500">*</span>
                        </span>
                        <div class="flex gap-6">
                            @foreach (['Ya', 'Tidak'] as $pilihan)
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="nyeri" value="{{ $pilihan }}" class="nyeri-pilih text-amber-600 focus:ring-amber-500 w-4 h-4"
                                           @checked(old('nyeri', $emr_data['nyeri'] ?? 'Tidak') === $pilihan)>
                                    <span class="text-slate-700">{{ $pilihan }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('nyeri')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror

                        {{-- Skor nyeri: hanya tampil saat Nyeri = Ya.
                             State awal sudah benar sejak render (belum bergantung
                             JS) memakai inline style + class `hidden` sekaligus —
                             setting style saja tidak cukup karena class `hidden`
                             tetap memberi display:none. --}}
                        <div id="wrapSkorNyeri" class="mt-3 {{ $nyeriTerpilih === 'Ya' ? '' : 'hidden' }}" style="{{ $nyeriTerpilih === 'Ya' ? '' : 'display: none;' }}">
                            <label for="skor_nyeri" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Skor Nyeri (0&#8211;10)
                            </label>
                            <select id="skor_nyeri" name="skor_nyeri"
                                    class="select2 w-full sm:w-40 text-sm border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                <option value="">-- Pilih Skor --</option>
                                @for ($skor = 0; $skor <= 10; $skor++)
                                    <option value="{{ $skor }}" @selected((string) old('skor_nyeri', $emr_data['skor_nyeri'] ?? '') === (string) $skor)>{{ $skor }}</option>
                                @endfor
                            </select>
                            @error('skor_nyeri')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Diagnosa kerja --}}
                    <div>
                        <label for="diagnosa_kerja" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Diagnosa Kerja <span class="text-red-500">*</span>
                        </label>
                        <textarea id="diagnosa_kerja" name="diagnosa_kerja" rows="3"
                                  placeholder="Contoh: Demam eruptif suspect DHF, Observe Observation"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('diagnosa_kerja', $emr_data['diagnosa_kerja'] ?? '') }}</textarea>
                        @error('diagnosa_kerja')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Catatan tambahan --}}
                    <div>
                        <label for="catatan_tambahan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Catatan Tambahan
                        </label>
                        <textarea id="catatan_tambahan" name="catatan_tambahan" rows="3"
                                  placeholder="Catatan lain bila diperlukan"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('catatan_tambahan', $emr_data['catatan_tambahan'] ?? '') }}</textarea>
                        @error('catatan_tambahan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Lompat ke input parameter EVM: scroll ke field lalu sorot singkat,
        // supaya petugas tahu persis di mana harus mengetik (semua input EVM
        // berada di panel "Tanda Vital", bukan di tabel ini).
        function lompatKeInput(parameter) {
            var el = document.getElementById(parameter);
            if (!el) return;

            // Untuk select2, sorot wrapper-nya; select aslinya disembunyikan
            // oleh Select2 sehingga ring di elemen itu tidak terlihat.
            var target = el.closest('.relative') || el.parentElement || el;

            if (target.scrollIntoView) {
                target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            target.classList.add('ring-2', 'ring-amber-400', 'ring-offset-2', 'rounded');
            setTimeout(function () {
                target.classList.remove('ring-2', 'ring-amber-400', 'ring-offset-2');
            }, 2400);

            // Fokuskan juga elemen aslinya supaya bisa langsung diketik.
            try {
                el.focus({ preventScroll: true });
            } catch (e) {
                /* beberapa browser menolak focus pada select yang disabled */
            }
        }

        document.querySelectorAll('[data-ews-lompat]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                lompatKeInput(btn.getAttribute('data-ews-lompat'));
            });
        });

        // Tampil/sembunyi memakai INLINE STYLE, bukan class `hidden` saja.
        //
        // Tailwind v4 menaruh `.hidden` (display:none) SEBELUM `.inline-flex`
        // (display:inline-flex) di hasil CSS. Keduanya specificity sama, jadi
        // aturan yang belakangan menang. Kelas `hidden` ikut dilepas/dipasang
        // karena mengosongkan inline style saja tidak cukup: markup yang masih
        // memuat class `hidden` tetap `display:none`.
        function tampilkan(el, tampil, display) {
            if (!el) return;

            el.classList.toggle('hidden', !tampil);
            el.style.display = tampil ? (display || '') : 'none';
        }

        // ---------------------------------------------------------------
        // Skor nyeri: tampil hanya saat Nyeri = Ya.
        // ---------------------------------------------------------------
        var wrapSkor = document.getElementById('wrapSkorNyeri');
        var inputSkor = document.getElementById('skor_nyeri');
        var isView = {{ $isView ? 'true' : 'false' }};

        function syncNyeri() {
            var dipilih = document.querySelector('input.nyeri-pilih:checked');
            var ada = !!(dipilih && dipilih.value === 'Ya');

            tampilkan(wrapSkor, ada);

            // Nilai dikirim apa adanya; yang mengosongkannya saat Nyeri = Tidak
            // adalah filteredData() di server, bukan browser.
            if (inputSkor) inputSkor.setAttribute('aria-hidden', ada ? 'false' : 'true');
        }

        document.querySelectorAll('input.nyeri-pilih').forEach(function (r) {
            r.addEventListener('change', syncNyeri);
        });
        syncNyeri();

        // ---------------------------------------------------------------
        // BMI: pratinjau di browser. Angka FINAL tetap dihitung ulang di
        // server (filteredData) sehingga tidak bisa dimanipulasi.
        // ---------------------------------------------------------------
        var berat = document.getElementById('berat_badan');
        var tinggi = document.getElementById('tinggi_badan');
        var outBmi = document.getElementById('preview_bmi');
        var outKategori = document.getElementById('preview_bmi_kategori');

        function hitungBmi() {
            if (!outBmi || !berat || !tinggi) return;

            var b = parseFloat(berat.value);
            var t = parseFloat(tinggi.value);

            if (!b || b <= 0 || !t || t <= 0) {
                // Kosongkan hanya bila belum ada nilai tersimpan di server.
                if (isView) return;
                outBmi.textContent = '-';
                if (outKategori) outKategori.textContent = '';
                return;
            }

            var bmi = b / Math.pow(t / 100, 2);
            outBmi.textContent = bmi.toFixed(1);

            if (outKategori) {
                if (bmi < 18.5) outKategori.textContent = '(Kurus)';
                else if (bmi < 25) outKategori.textContent = '(Normal)';
                else if (bmi < 30) outKategori.textContent = '(Overweight)';
                else outKategori.textContent = '(Obesitas)';
            }
        }

        if (berat) berat.addEventListener('input', hitungBmi);
        if (tinggi) tinggi.addEventListener('input', hitungBmi);

        // ---------------------------------------------------------------
        // EVM / Early Warning Score — pratinjau live.
        //
        // Skor di bawah MENCERMINKAN App\Helpers\EwsHelper (RCPCH, 7
        // parameter). Nilai final tetap dihitung ulang di server saat disimpan
        // (filteredData), jadi tabel ini murni tampilan bantu.
        //
        // Parameter diambil dari input tanda vital di panel Tanda Vital, bukan
        // diketik ulang, jadi nilai yang tampil dan yang disimpan selalu sama.
        // ---------------------------------------------------------------
        var PARAMETER_EWS = @json(\App\Helpers\EwsHelper::PARAMETER);

        // Warna badge diambil dari helper yang sama supaya JS dan server
        // tidak pernah berbeda (sumber kebenaran tetap App\Helpers\EwsHelper).
        // Skor 0 = normal (hijau), 1-2 = perlu perhatian (kuning), 3 = tinggi (merah).
        var WARNA_SKOR = @json(array_values(\App\Helpers\EwsHelper::SKOR_WARNA));
        var BADGE_KATEGORI = @json(\App\Helpers\EwsHelper::KATEGORI_WARNA);
        var LABEL_TIDAK_DIUKUR = @json(\App\Helpers\EwsHelper::LABEL_TIDAK_DIUKUR);

        // Skor satu parameter. Mengembalikan null bila nilai kosong atau di luar
        // rentang wajar — sama seperti EwsHelper::skor() di server.
        function skorEws(parameter, mentah) {
            var v = (mentah === null || mentah === undefined) ? '' : String(mentah).trim();
            if (v === '') return null;

            var n = parseFloat(v);

            switch (parameter) {
                case 'pernapasan':
                    if (isNaN(n) || n < 1 || n > 60) return null;
                    return n <= 8 ? 3 : (n <= 11 ? 1 : (n <= 20 ? 0 : (n <= 24 ? 2 : 3)));
                case 'saturasi':
                    if (isNaN(n) || n < 50 || n > 100) return null;
                    return n <= 89 ? 3 : (n <= 91 ? 2 : (n <= 95 ? 1 : 0));
                case 'oksigen':
                    return v === 'Air' ? 0 : (v === 'Oksigen' ? 2 : null);
                case 'td_sistolik':
                    if (isNaN(n) || n < 50 || n > 300) return null;
                    return n <= 90 ? 3 : (n <= 100 ? 2 : (n <= 110 ? 1 : (n <= 219 ? 0 : 3)));
                case 'nadi':
                    if (isNaN(n) || n < 20 || n > 250) return null;
                    return n <= 40 ? 3 : (n <= 50 ? 1 : (n <= 90 ? 0 : (n <= 110 ? 1 : (n <= 130 ? 2 : 3))));
                case 'kesadaran':
                    return v === 'Compos Mentis' ? 0 : 3;
                case 'suhu':
                    if (isNaN(n) || n < 25 || n > 45) return null;
                    return n <= 35 ? 3 : (n <= 36 ? 1 : (n <= 38 ? 0 : (n <= 39 ? 1 : 2)));
                default:
                    return null;
            }
        }

        function nilaiParamEws(parameter) {
            var el = document.getElementById(parameter);
            return el ? el.value : '';
        }

        // Apakah parameter ini ditandai "tidak diukur" oleh petugas?
        function paramNa(parameter) {
            var cb = document.querySelector('input.ews-na[value="' + parameter + '"]');
            return !!(cb && cb.checked);
        }

        function hitungEws() {
            var total = 0;
            var terukur = 0;
            var selesai = 0;
            var jumlahNa = 0;
            var kurang = [];

            PARAMETER_EWS.forEach(function (parameter) {
                var baris = document.querySelector('tr[data-ews-param="' + parameter + '"]');
                if (!baris) return;

                var na = paramNa(parameter);
                var mentah = nilaiParamEws(parameter);
                var ada = !na && String(mentah === null ? '' : mentah).trim() !== '';
                var skor = ada ? skorEws(parameter, mentah) : null;

                var selNilai = baris.querySelector('[data-ews-nilai]');
                var selSkor = baris.querySelector('[data-ews-skor]');
                var selLabel = baris.querySelector('td');

                // Parameter yang ditandai tidak diukur: dianggap SUDAH SELESAI
                // dengan kontribusi 0, sama seperti EwsHelper::hitung() di server.
                if (na) {
                    jumlahNa++;
                    selesai++;

                    if (selNilai) selNilai.textContent = LABEL_TIDAK_DIUKUR;
                    if (selSkor) {
                        selSkor.textContent = LABEL_TIDAK_DIUKUR;
                        selSkor.className = 'px-3 py-2 text-center text-slate-400 bg-slate-100 rounded';
                    }
                    if (baris.classList) baris.classList.add('bg-slate-50');
                    return;
                }

                if (baris.classList) baris.classList.remove('bg-slate-50');

                if (selNilai) selNilai.textContent = ada ? String(mentah) : '-';

                if (skor === null) {
                    // Kosong ATAU di luar rentang wajar: belum bisa dinilai.
                    // Label diambil dari sel pertama baris tabel, jadi tidak
                    // perlu salinan daftar label di JS.
                    kurang.push(selLabel ? selLabel.textContent.trim() : parameter);

                    if (selSkor) {
                        selSkor.textContent = '-';
                        selSkor.className = 'px-3 py-2 text-center text-slate-300';
                    }
                    return;
                }

                total += skor;
                terukur++;
                selesai++;

                if (selSkor) {
                    selSkor.textContent = skor;
                    selSkor.className = 'px-3 py-2 text-center font-bold border rounded ' + WARNA_SKOR[skor];
                }
            });

            // Lengkap = semua parameter terisi ATAU ditandai tidak diukur.
            var lengkap = selesai === PARAMETER_EWS.length;

            // Total SELALU ditampilkan: jumlah berjalan dari parameter yang
            // sudah dinilai. Sebelumnya total disembunyikan sampai 7 parameter
            // lengkap, sehingga angka tidak pernah muncul bagi pengguna yang
            // baru mengisi sebagian.
            var outTotal = document.getElementById('ews_total');
            if (outTotal) {
                outTotal.textContent = String(total);
                outTotal.className = 'px-3 py-2.5 text-center text-lg font-bold ' +
                    (lengkap ? 'text-slate-800' : 'text-amber-600');
            }

            var outKategori = document.getElementById('ews_kategori');
            if (outKategori) {
                outKategori.className = 'inline-block border rounded px-2 py-0.5 text-[11px] font-semibold';
                if (lengkap) {
                    var kategori = total >= 7 ? 'Tinggi' : (total >= 4 ? 'Sedang' : 'Rendah');
                    outKategori.textContent = 'Risiko ' + kategori;
                    outKategori.className += ' ' + BADGE_KATEGORI[kategori];
                } else {
                    // Kategori hanya bermakna bila semua parameter terisi atau
                    // ditandai tidak diukur; skor parsial bisa terlalu rendah.
                    outKategori.textContent = 'Belum lengkap';
                    outKategori.className += ' bg-slate-50 text-slate-400 border-slate-200';
                }
            }

            var outStatus = document.getElementById('ews_status');
            if (outStatus) {
                var teksStatus = lengkap
                    ? 'Lengkap'
                    : 'Belum lengkap (' + selesai + '/' + PARAMETER_EWS.length + ')';
                if (lengkap && jumlahNa > 0) {
                    teksStatus += ' \u00b7 ' + terukur + ' terukur, ' + jumlahNa + ' tidak diukur';
                }
                outStatus.textContent = teksStatus;
                outStatus.className = lengkap
                    ? 'text-xs text-right text-emerald-600'
                    : 'text-xs text-right text-slate-500';
            }

            var outNaInfo = document.getElementById('ews_na_info');
            if (outNaInfo) {
                outNaInfo.textContent = jumlahNa > 0
                    ? jumlahNa + ' parameter tidak diukur'
                    : '';
            }

            // Sebutkan persis parameter mana yang belum terisi, supaya petugas
            // tahu apa yang perlu diisi atau dicentang "Tidak Diukur".
            var outPeringatan = document.getElementById('ews_peringatan');
            if (outPeringatan) {
                if (lengkap) {
                    outPeringatan.className = 'px-3 py-2.5 rounded-lg border text-xs hidden';
                    outPeringatan.textContent = '';
                } else {
                    outPeringatan.className = 'px-3 py-2.5 rounded-lg border text-xs bg-amber-50 border-amber-200 text-amber-800';
                    outPeringatan.textContent = 'Parameter belum diisi: ' + kurang.join(', ') +
                        '. Isi nilainya, atau centang "Tidak Diukur" bila tidak bisa diukur.';
                }
            }
        }

        // Input EWS adalah input tanda vital itu sendiri, jadi cukup ikat
        // event ke keduanya.
        PARAMETER_EWS.forEach(function (parameter) {
            var el = document.getElementById(parameter);
            if (el) {
                el.addEventListener('input', hitungEws);
                el.addEventListener('change', hitungEws);
            }

            var cb = document.querySelector('input.ews-na[value="' + parameter + '"]');
            if (cb) {
                cb.addEventListener('change', function () {
                    // Mengosongkan nilai vital saat ditandai tidak diukur,
                    // supaya angka sisa tidak ikut diskor DAN tidak tersimpan
                    // seolah-olah pernah diukur.
                    if (cb.checked && el) el.value = '';

                    hitungEws();
                });
            }
        });
        hitungEws();
    });
    </script>

@endsection()