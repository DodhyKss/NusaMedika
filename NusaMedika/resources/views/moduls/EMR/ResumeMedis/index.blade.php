@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Resume Medis';
        $subtitleForm = 'Ringkasan episode perawatan: kondisi awal, pemeriksaan fisik, diagnosa, dan kondisi saat pulang.';
        $routeName = null;
        $routeUrl = url('emr/form/resume_medis');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        // Organ yang diperiksa — 12 variabel bersuffix (pf_*) pada satu objek.
        $pfFields = [
            'pf_kepala' => 'Kepala', 'pf_mata' => 'Mata', 'pf_tht' => 'THT',
            'pf_gigidanmulut' => 'Gigi & Mulut', 'pf_leher' => 'Leher', 'pf_toraks' => 'Toraks',
            'pf_jantung' => 'Jantung', 'pf_paru' => 'Paru', 'pf_abdomen' => 'Abdomen',
            'pf_kelenjar' => 'Kelenjar', 'pf_genitalia' => 'Genitalia',
            'pf_ekstremitas' => 'Ekstremitas',
        ];

        $v = fn($key) => old($key, $emr_data[$key] ?? '');
        $kondisiPulang = old('kondisi_saat_pulang', $emr_data['kondisi_saat_pulang'] ?? 'Sembuh');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Resume"
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
                slug="resume_medis"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'diagnosa_primer' => 'Diagnosa Primer',
                    'kondisi_saat_pulang' => 'Kondisi Pulang',
                    'dirujuk_ke' => 'Dirujuk Ke',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= KONDISI AWAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kondisi Awal</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="tanggal_resume" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Resume <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_resume" name="tanggal_resume"
                                   value="{{ $v('tanggal_resume') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_resume')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_resume" class="block font-bold text-slate-800 mb-1.5">
                                Jam Resume <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_resume" name="waktu_resume" step="60"
                                   value="{{ $v('waktu_resume') ? substr((string) $v('waktu_resume'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_resume')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="indikasi_rawat_inap" class="block font-bold text-slate-800 mb-1.5">Indikasi Rawat Inap</label>
                            <textarea id="indikasi_rawat_inap" name="indikasi_rawat_inap" rows="2"
                                      placeholder="Indikasi/rawat inap"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('indikasi_rawat_inap') }}</textarea>
                            @error('indikasi_rawat_inap')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="keluhan_utama" class="block font-bold text-slate-800 mb-1.5">
                            Keluhan Utama <span class="text-red-500">*</span>
                        </label>
                        <textarea id="keluhan_utama" name="keluhan_utama" rows="3"
                                  placeholder="Keluhan utama pasien saat masuk"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('keluhan_utama') }}</textarea>
                        @error('keluhan_utama')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="riwayat_kesehatan_saat_ini" class="block font-bold text-slate-800 mb-1.5">
                                Riwayat Kesehatan Saat Ini <span class="text-red-500">*</span>
                            </label>
                            <textarea id="riwayat_kesehatan_saat_ini" name="riwayat_kesehatan_saat_ini" rows="3"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('riwayat_kesehatan_saat_ini') }}</textarea>
                            @error('riwayat_kesehatan_saat_ini')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="riwayat_penyakit_dahulu" class="block font-bold text-slate-800 mb-1.5">
                                Riwayat Penyakit Dahulu <span class="text-red-500">*</span>
                            </label>
                            <textarea id="riwayat_penyakit_dahulu" name="riwayat_penyakit_dahulu" rows="3"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('riwayat_penyakit_dahulu') }}</textarea>
                            @error('riwayat_penyakit_dahulu')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= TANDA VITAL & SKOR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanda Vital &amp; Skor</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @foreach ([
                            'td_sistolik' => 'TD Sistolik', 'td_diastolik' => 'TD Diastolik',
                            'nadi' => 'Nadi (/menit)', 'pernapasan' => 'Pernapasan (/menit)',
                            'suhu' => 'Suhu (°C)', 'skor_nyeri' => 'Skor Nyeri (0-10)',
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
                                               min="{{ $name === 'gcs_e' ? 1 : 1 }}" max="{{ $name === 'gcs_m' ? 6 : ($name === 'gcs_v' ? 5 : 4) }}"
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
                                    {{ old('gcs_jumlah', $emr_data['gcs_jumlah'] ?? '-') }}
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
                                        {{ old('total_ews', $emr_data['total_ews'] ?? '-') }}
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="text-[11px] uppercase tracking-wider text-slate-500">Kategori</div>
                                    <div id="ews_kategori" class="text-sm font-semibold text-slate-700">
                                        {{ old('kategori_ews', $emr_data['kategori_ews'] ?? '-') }}
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

            {{-- ================= PEMERIKSAAN FISIK ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Pemeriksaan Fisik</span>
                </div>
                <div class="p-5 bg-white">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($pfFields as $name => $label)
                            <div>
                                <label for="{{ $name }}" class="block font-bold text-slate-800 mb-1.5">{{ $label }}</label>
                                <input type="text" id="{{ $name }}" name="{{ $name }}"
                                       value="{{ $v($name) }}"
                                       placeholder="Keterangan / temuan"
                                       class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                                @error($name)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= DIAGNOSA RESUME ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Diagnosa Resume</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <label for="diagnosa_primer" class="block font-bold text-slate-800 mb-1.5">
                            Diagnosa Primer <span class="text-red-500">*</span>
                        </label>
                        <textarea id="diagnosa_primer" name="diagnosa_primer" rows="3"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('diagnosa_primer') }}</textarea>
                        @error('diagnosa_primer')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach (['diagnosa_sekunder' => 'Diagnosa Sekunder', 'prosedur' => 'Prosedur yang Dilakukan', 'komplikasi' => 'Komplikasi'] as $name => $label)
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

            {{-- ================= KONDISI SAAT PULANG ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kondisi Saat Pulang</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="kondisi_saat_pulang" class="block font-bold text-slate-800 mb-1.5">
                                Kondisi Saat Pulang <span class="text-red-500">*</span>
                            </label>
                            <select id="kondisi_saat_pulang" name="kondisi_saat_pulang"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('alasan_pulang', $kondisiPulang, '-- Pilih Kondisi --') !!}
                            </select>
                            @error('kondisi_saat_pulang')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="catatan_alasan_pulang" class="block font-bold text-slate-800 mb-1.5">
                                Catatan Alasan Pulang <span class="text-red-500">*</span>
                            </label>
                            <textarea id="catatan_alasan_pulang" name="catatan_alasan_pulang" rows="3"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('catatan_alasan_pulang') }}</textarea>
                            @error('catatan_alasan_pulang')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Field kondisional: hanya tampil saat kondisi_pulang --}}
                    <div id="wrap-dirujuk" class="{{ $kondisiPulang === 'Dirujuk' ? '' : 'hidden' }}">
                        <label for="dirujuk_ke" class="block font-bold text-slate-800 mb-1.5">Dirujuk Ke</label>
                        <select id="dirujuk_ke" name="dirujuk_ke"
                                class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white outline-none text-slate-700">
                            {!! \App\Helpers\SelectOption::render('tujuan_rujukan', $v('dirujuk_ke'), '-- Pilih Tujuan --') !!}
                        </select>
                        @error('dirujuk_ke')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="wrap-meninggal" class="{{ $kondisiPulang === 'Meninggal' ? '' : 'hidden' }} grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="meninggal" class="block font-bold text-slate-800 mb-1.5">Klasifikasi Kematian</label>
                            <select id="meninggal" name="meninggal"
                                    class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('penyebab_kematian', $v('meninggal'), '-- Pilih --') !!}
                            </select>
                            @error('meninggal')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="catatan_kematian" class="block font-bold text-slate-800 mb-1.5">Tanggal &amp; Jam Kematian</label>
                            <input type="text" id="catatan_kematian" name="catatan_kematian"
                                   value="{{ $v('catatan_kematian') }}"
                                   placeholder="contoh: 12/10/2026 14:30"
                                   class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                            @error('catatan_kematian')
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
            // (lihat AGENTS.md — .hidden utility Tailwind bisa dikalahkan oleh
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

            var kondisi = document.getElementById('kondisi_saat_pulang');
            var wrapDirujuk = document.getElementById('wrap-dirujuk');
            var wrapMeninggal = document.getElementById('wrap-meninggal');

            function syncKondisi() {
                var val = kondisi ? kondisi.value : '';
                tampilkan(wrapDirujuk, val === 'Dirujuk', 'block');
                tampilkan(wrapMeninggal, val === 'Meninggal', 'grid');
            }
            if (kondisi) {
                kondisi.addEventListener('change', syncKondisi);
                syncKondisi();
            }

            // Total GCS — hanya tampilan; server menghitung ulang sendiri.
            ['gcs_e', 'gcs_m', 'gcs_v'].forEach(function (id) {
                var el = document.getElementById(id);
                var out = document.getElementById('gcs_total');
                if (!el || !out) return;
                el.addEventListener('input', function () {
                    var total = 0, lengkap = true;
                    ['gcs_e', 'gcs_m', 'gcs_v'].forEach(function (k) {
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