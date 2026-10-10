@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Alat Invasif';
        $subtitleForm = 'Pencatatan alat invasif yang terpasang pada pasien beserta lokasi pemasangan dan tanggal pelepasan.';
        $routeName = null;
        $routeUrl = url('emr/form/alat_invasif');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $nilaiAlat = (string) old('alat_invasif', $emr_data['alat_invasif'] ?? '');
        $nilaiLokasi = (string) old('lokasi_pemasangan', $emr_data['lokasi_pemasangan'] ?? '');

        // Lama pemasangan (turunan). `old()` selalu menang, sama seperti field
        // lainnya, supaya form yang gagal validasi tetap menampilkan nilai.
        $lamaTersimpan = old('lama_pemasangan', $emr_data['lama_pemasangan'] ?? '');
        $teksLama = ($lamaTersimpan !== '' && $lamaTersimpan !== null)
            ? $lamaTersimpan.' hari'
            : '-';
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Alat Invasif"
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
                slug="alat_invasif"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'alat_invasif' => 'Alat',
                    'lokasi_pemasangan' => 'Lokasi',
                    'tanggal_pasang' => 'Tanggal Pasang',
                    'tanggal_lepas' => 'Tanggal Lepas',
                    'lama_pemasangan' => 'Lama (Hari)',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= Alat Invasif =================
                 Satu baris per EMR: satu catatan untuk satu alat. Pasien yang
                 memakai beberapa alat dicatat pada EMR terpisah. --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Data Alat Invasif</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    {{-- Tanggal & jam pemasangan. TIDAK wajib: alat bisa sudah
                         terpasang sebelum pasien masuk (mis. dari IGD atau
             instalasi), sehingga titik pemasangannya tidak diketahui. --}}
                    <div>
                        <span class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Tanggal Pemasangan
                        </span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="tanggal_pasang" class="sr-only">Tanggal Pemasangan</label>
                                <input type="date" id="tanggal_pasang" name="tanggal_pasang"
                                       value="{{ old('tanggal_pasang', $emr_data['tanggal_pasang'] ?? '') }}"
                                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                @error('tanggal_pasang')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="jam_pasang" class="sr-only">Jam Pemasangan</label>
                                {{-- Dipotong 5 karakter: nilai tersimpan bisa berupa "HH:MM:SS". --}}
                                <input type="time" id="jam_pasang" name="jam_pasang" step="60"
                                       value="{{ old('jam_pasang', substr((string) ($emr_data['jam_pasang'] ?? ''), 0, 5)) }}"
                                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                @error('jam_pasang')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Kosongkan bila alat sudah terpasang sebelum pasien masuk.
                        </p>
                    </div>

                    {{-- Jenis alat & lokasi — keduanya wajib. --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="alat_invasif" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Alat Invasif <span class="text-red-500">*</span>
                            </label>
                            <select id="alat_invasif" name="alat_invasif"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('alat_invasif', $nilaiAlat, '-- Pilih Alat Invasif --') !!}
                            </select>
                            @error('alat_invasif')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="lokasi_pemasangan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Lokasi Pemasangan <span class="text-red-500">*</span>
                            </label>
                            <select id="lokasi_pemasangan" name="lokasi_pemasangan"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('lokasi_alat_invasif', $nilaiLokasi, '-- Pilih Lokasi --') !!}
                            </select>
                            @error('lokasi_pemasangan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Tanggal & jam pelepasan. Kosong = alat masih terpasang. --}}
                    <div>
                        <span class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Tanggal Lepas
                        </span>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="tanggal_lepas" class="sr-only">Tanggal Lepas</label>
                                <input type="date" id="tanggal_lepas" name="tanggal_lepas"
                                       value="{{ old('tanggal_lepas', $emr_data['tanggal_lepas'] ?? '') }}"
                                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                @error('tanggal_lepas')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="jam_lepas" class="sr-only">Jam Lepas</label>
                                <input type="time" id="jam_lepas" name="jam_lepas" step="60"
                                       value="{{ old('jam_lepas', substr((string) ($emr_data['jam_lepas'] ?? ''), 0, 5)) }}"
                                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                @error('jam_lepas')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="mt-1.5 text-xs text-slate-400">
                            Kosongkan selama alat masih terpasang pada pasien.
                        </p>
                    </div>

                    {{-- Lama pemasangan: TURUNAN, dihitung ulang di server
                         (filteredData). Kolom ini hanya tampilan, TIDAK punya
                         input name. --}}
                    <div class="flex items-center gap-3 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                        <span class="text-[11px] uppercase tracking-wider text-slate-500">Lama Pemasangan</span>
                        <span id="preview_lama" class="text-sm font-bold text-slate-700">{{ $teksLama }}</span>
                        <span id="preview_lama_keterangan" class="text-xs text-slate-500"></span>
                        <p class="ml-auto text-xs text-slate-400">Dihitung otomatis dari tanggal pasang &amp; lepas.</p>
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ---------------------------------------------------------------
        // Lama pemasangan: pratinjau di browser. Angka FINAL tetap dihitung
        // ulang di server (filteredData) sehingga tidak bisa dimanipulasi.
        //
        // Mengikuti AlatInvasifController::lamaPemasangan(): butuh tanggal
        // pasang DAN tanggal lepas; hasil dibulatkan ke ATAS; tanggal lepas
        // yang lebih awal dianggap tidak sah (tampilkan "-", bukan negatif).
        // ---------------------------------------------------------------
        var tglPasang = document.getElementById('tanggal_pasang');
        var jamPasang = document.getElementById('jam_pasang');
        var tglLepas = document.getElementById('tanggal_lepas');
        var jamLepas = document.getElementById('jam_lepas');
        var outLama = document.getElementById('preview_lama');
        var outKet = document.getElementById('preview_lama_keterangan');

        // Jam kosong -&gt; tengah hari, sama seperti waktuLengkap() di server.
        function titikWaktu(elTanggal, elJam) {
            if (!elTanggal || !elTanggal.value) return null;

            var jam = (elJam && elJam.value) ? elJam.value : '12:00';
            var w = new Date(elTanggal.value + 'T' + jam);

            return isNaN(w.getTime()) ? null : w;
        }

        function hitungLama() {
            if (!outLama) return;

            var pasang = titikWaktu(tglPasang, jamPasang);
            var lepas = titikWaktu(tglLepas, jamLepas);

            if (!pasang) {
                outLama.textContent = '-';
                if (outKet) outKet.textContent = tglPasang && tglPasang.value ? '' : 'Tanggal pemasangan belum diisi';
                return;
            }

            if (!lepas) {
                outLama.textContent = '-';
                if (outKet) outKet.textContent = 'Alat masih terpasang';
                return;
            }

            if (lepas < pasang) {
                outLama.textContent = '-';
                if (outKet) outKet.textContent = 'Tanggal lepas lebih awal dari tanggal pasang';
                return;
            }

            var msPerHari = 24 * 60 * 60 * 1000;
            // ceil: pasang 08:00, lepas 20:00 di hari yang sama tetap 1 hari.
            var hari = Math.ceil((lepas - pasang) / msPerDay);

            outLama.textContent = hari + ' hari';
            if (outKet) outKet.textContent = '(' + Math.floor(hari) + ' hari penuh)';
        }

        [tglPasang, jamPasang, tglLepas, jamLepas].forEach(function (el) {
            if (el) {
                el.addEventListener('input', hitungLama);
                el.addEventListener('change', hitungLama);
            }
        });
        hitungLama();
    });
    </script>

@endsection()