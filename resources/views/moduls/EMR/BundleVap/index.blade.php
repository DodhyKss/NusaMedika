@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Bundle VAP';
        $subtitleForm = 'Checklist 10 butir pencegahan Ventilator-Associated Pneumonia beserta skor kepatuhannya.';
        $routeName = null;
        $routeUrl = url('emr/form/bundle_vap');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $butir = \App\Http\Controllers\EMR\BundleVap\BundleVapController::BUTIR;
        $jawaban = \App\Http\Controllers\EMR\BundleVap\BundleVapController::JAWABAN;

        // Daftar nama variabel butir, dihitung di sini (bukan inline di @json)
        // karena Blade memecah argumen @json() dengan explode(',').
        $varianButir = array_values(array_map(fn ($b) => $b['varian'], $butir));

        // Kategori kepatuhan belum tampil bila butir belum lengkap; warna
        // diambil dari helper yang sama supaya server & browser tidak berbeda.
        $warnaKategori = [
            'Lengkap' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Sebagian' => 'bg-amber-50 text-amber-700 border-amber-200',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Bundle VAP"
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
                slug="bundle_vap"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'tanggal_bundle' => 'Tanggal',
                    'waktu_bundle' => 'Jam',
                    'vap_skor' => 'Skor',
                    'vap_persen' => 'Kepatuhan',
                    'vap_kategori' => 'Kategori',
                ]"
                :badges="[
                    'vap_kategori' => [
                        'Lengkap' => ['label' => 'Lengkap', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                        'Sebagian' => ['label' => 'Sebagian', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                    ],
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= Identitas Checklist ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Checklist</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_bundle" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Tanggal Checklist <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_bundle" name="tanggal_bundle"
                                   value="{{ old('tanggal_bundle', $emr_data['tanggal_bundle'] ?? '') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('tanggal_bundle')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_bundle" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Waktu Checklist <span class="text-red-500">*</span>
                            </label>
                            {{-- Dipotong 5 karakter: nilai tersimpan bisa berupa "HH:MM:SS". --}}
                            <input type="time" id="waktu_bundle" name="waktu_bundle" step="60"
                                   value="{{ old('waktu_bundle', substr((string) ($emr_data['waktu_bundle'] ?? ''), 0, 5)) }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('waktu_bundle')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= Butir Bundle ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between gap-3">
                    <span class="text-slate-600 font-medium text-[15px]">Butir Bundle Pencegahan VAP</span>
                    <span class="text-xs text-slate-500">Isi seluruh butir &mdash; pilih <strong>N/A</strong> bila tidak berlaku pada pasien ini</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">

                    @foreach ($butir as $nomor => $b)
                        <?php $terpilih = (string) old($b['varian'], $emr_data[$b['varian']] ?? ''); ?>
                        <div class="border border-slate-200 rounded-lg {{ $terpilih === 'Ya' ? 'bg-emerald-50/40' : ($terpilih === 'Tidak' ? 'bg-red-50/40' : 'bg-white') }}">
                            <div class="px-4 py-3 flex flex-col lg:flex-row lg:items-center gap-3">
                                <div class="flex items-start gap-3 flex-1">
                                    <span class="inline-flex items-center justify-center w-6 h-6 shrink-0 rounded bg-slate-600 text-white text-[11px] font-bold mt-0.5">{{ $nomor }}</span>
                                    <div>
                                        <label for="{{ $b['varian'] }}_{{ $nomor }}" class="block font-semibold text-slate-700 cursor-pointer">
                                            {{ $b['nilai'] }} <span class="text-red-500">*</span>
                                        </label>
                                        <p class="text-xs text-slate-400">{{ $b['ket'] }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 shrink-0 lg:ml-4">
                                    @foreach ($jawaban as $jwb)
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            {{-- `name` WAJIB berbeda per butir (vap_1..vap_10).
                                                 Kalau semuanya `name="vap"`, hanya jawaban
                                                 butir terakhir yang tersimpan. --}}
                                            <input type="radio" name="{{ $b['varian'] }}" value="{{ $jwb }}" class="vap-jawab rounded text-blue-600 focus:ring-blue-500 w-4 h-4"
                                                   @checked($terpilih === $jwb)>
                                            <span class="text-xs text-slate-600">{{ $jwb }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @error($b['varian'])
                                <p class="px-4 pb-3 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= Ringkasan Kepatuhan =================
                 Skor, persen, dan kategori adalah field TURUNAN: dihitung ulang
                 di server dari jawaban butir (nilai browser diabaikan). --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200 flex items-center justify-between gap-3">
                    <span class="text-emerald-600 font-medium text-[15px]">Ringkasan Kepatuhan Bundle</span>
                    <span id="vap_status" class="text-xs text-right {{ $ringkasan['lengkap'] ? 'text-emerald-600' : 'text-amber-600' }}">
                        @if ($ringkasan['lengkap'])
                            Semua butir terjawab
                        @else
                            Belum lengkap ({{ $ringkasan['terisi'] }}/{{ count($butir) }})
                        @endif
                    </span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="flex items-center gap-3 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                            <span class="text-[11px] uppercase tracking-wider text-slate-500">Skor</span>
                            <span id="vap_skor" class="text-lg font-bold text-slate-800">{{ $ringkasan['skor'] }}</span>
                        </div>
                        <div class="flex items-center gap-3 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                            <span class="text-[11px] uppercase tracking-wider text-slate-500">Kepatuhan</span>
                            <span id="vap_persen" class="text-lg font-bold text-slate-800">{{ $ringkasan['persen'] }}%</span>
                            <span id="vap_dinilai" class="text-xs text-slate-500"></span>
                        </div>
                        <div class="flex items-center gap-3 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg">
                            <span class="text-[11px] uppercase tracking-wider text-slate-500">Kategori</span>
                            <span id="vap_kategori" class="inline-block border rounded px-2 py-0.5 text-[11px] font-semibold {{ $ringkasan['kategori'] ? ($warnaKategori[$ringkasan['kategori']] ?? 'bg-slate-50 text-slate-600 border-slate-200') : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                {{ $ringkasan['kategori'] ?? 'Belum Lengkap' }}
                            </span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-400">
                        Kepatuhan = jumlah butir <strong>Ya</strong> dibagi butir yang dinilai
                        (Ya atau Tidak). Butir <strong>N/A</strong> dikeluarkan dari pembagi karena
                        memang tidak berlaku pada pasien ini, sehingga tidak menurunkan kepatuhan.
                        Kategori <strong>Lengkap</strong> bila &ge; 80% dan seluruh butir terjawab.
                        Angka final selalu dihitung ulang di server.
                    </p>

                    <div>
                        <label for="catatan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                            Catatan
                        </label>
                        <textarea id="catatan" name="catatan" rows="3"
                                  placeholder="Contoh: suction subglottik N/A karena pasien memakai trachostomy tanpa cuff"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('catatan', $emr_data['catatan'] ?? '') }}</textarea>
                        @error('catatan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ---------------------------------------------------------------
        // Kepatuhan bundle: pratinjau live.
        //
        // Skor di bawah MENCERMINKAN BundleVapController::hitungKepatuhan().
        // Nilai final tetap dihitung ulang di server saat disimpan, jadi angka
        // di bawah murni tampilan bantu.
        // ---------------------------------------------------------------
        var BUTIR = @json($varianButir);
        var AMBANG_LENGKAP = @json(\App\Http\Controllers\EMR\BundleVap\BundleVapController::AMBANG_LENGKAP);
        var WARNA_KATEGORI = @json($warnaKategori);

        var outSkor = document.getElementById('vap_skor');
        var outPersen = document.getElementById('vap_persen');
        var outDinilai = document.getElementById('vap_dinilai');
        var outKategori = document.getElementById('vap_kategori');
        var outStatus = document.getElementById('vap_status');

        function hitungKepatuhan() {
            var skor = 0;
            var dinilai = 0;
            var terisi = 0;

            BUTIR.forEach(function (varian) {
                var dipilih = document.querySelector('input[name="' + varian + '"]:checked');
                var nilai = dipilih ? dipilih.value : '';

                if (nilai === '') return;
                terisi++;

                if (nilai === 'Ya') { skor++; dinilai++; }
                else if (nilai === 'Tidak') { dinilai++; }
                // N/A sengaja TIDAK masuk pembagi.
            });

            var persen = dinilai > 0 ? Math.round((skor / dinilai) * 1000) / 10 : 0;
            var lengkap = terisi === BUTIR.length;

            if (outSkor) outSkor.textContent = skor;
            if (outPersen) outPersen.textContent = persen + '%';
            if (outDinilai) outDinilai.textContent = menilai > 0 ? '(' + dinilai + ' dinilai)' : '';

            if (outKategori) {
                if (lengkap && dinilai > 0) {
                    var kategori = persen >= AMBANG_LENGKAP ? 'Lengkap' : 'Sebagian';
                    outKategori.textContent = kategori;
                    outKategori.className = 'inline-block border rounded px-2 py-0.5 text-[11px] font-semibold ' +
                        (WARNA_KATEGORI[kategori] || 'bg-slate-50 text-slate-600 border-slate-200');
                } else {
                    // Kategori hanya bermakna bila seluruh butir terjawab;
                    // kepatuhan parsial bisa menyesatkan.
                    outKategori.textContent = 'Belum Lengkap';
                    outKategori.className = 'inline-block border rounded px-2 py-0.5 text-[11px] font-semibold bg-amber-50 text-amber-700 border-amber-200';
                }
            }

            if (outStatus) {
                outStatus.textContent = lengkap
                    ? 'Semua butir terjawab'
                    : 'Belum lengkap (' + terisi + '/' + BUTIR.length + ')';
                outStatus.className = lengkap
                    ? 'text-xs text-right text-emerald-600'
                    : 'text-xs text-right text-amber-600';
            }
        }

        BUTIR.forEach(function (varian) {
            document.querySelectorAll('input[name="' + varian + '"]').forEach(function (el) {
                el.addEventListener('change', hitungKepatuhan);
            });
        });
        hitungKepatuhan();
    });
    </script>

@endsection()