@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Penilaian Nyeri';
        $subtitleForm = 'Pengkajian nyeri utama: metode skala, frekuensi, karakteristik, lokasi, dan intervensi.';
        $routeName = null;
        $routeUrl = url('emr/form/penilaian_nyeri');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $nyeri = old('nyeri', $emr_data['nyeri'] ?? 'Ya');
        $metode = old('metode_skala_nyeri', $emr_data['metode_skala_nyeri'] ?? 'VAS');

        // Karakteristik disimpan sebagai JSON array di emr_detail.
        $karakteristik = old('karakteristik_nyeri', $emr_data['karakteristik_nyeri'] ?? []);
        if (is_string($karakteristik)) {
            $karakteristik = json_decode($karakteristik, true) ?: [];
        }
        if (! is_array($karakteristik)) {
            $karakteristik = [];
        }

        // Keterangan bebas untuk pilihan "Lainnya" disimpan sebagai "Lainnya: ...".
        $karakteristikLainnya = '';
        foreach ($karakteristik as $item) {
            if (str_starts_with((string) $item, 'Lainnya')) {
                $karakteristikLainnya = trim(substr((string) $item, 8), ': ');
            }
        }

        $opsiFrekuensi = \App\Helpers\SelectOption::get('frekuensi_nyeri_penilaian');
        $opsiKarakteristik = \App\Helpers\SelectOption::get('karakteristik_nyeri_penilaian');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Penilaian Nyeri"
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
                slug="penilaian_nyeri"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'nyeri' => 'Nyeri',
                    'frekuensi_nyeri' => 'Frekuensi',
                    'lokasi_nyeri' => 'Lokasi',
                    'skor_nyeri' => 'Skor VAS',
                    'skor_flacc' => 'Skor FLACC',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= OBSERVASI & FLAG NYERI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Observasi &amp; Flag Nyeri</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="tanggal_nyeri" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_nyeri" name="tanggal_nyeri"
                                   value="{{ $v('tanggal_nyeri') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_nyeri')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_nyeri" class="block font-bold text-slate-800 mb-1.5">
                                Waktu Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_nyeri" name="waktu_nyeri" step="60"
                                   value="{{ $v('waktu_nyeri') ? substr((string) $v('waktu_nyeri'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_nyeri')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <span class="block font-bold text-slate-800 mb-1.5">
                                Pasien Merasakan Nyeri <span class="text-red-500">*</span>
                            </span>
                            <div class="flex gap-4 pt-2">
                                @foreach (\App\Helpers\SelectOption::get('ya_tidak_kapital') as $opsi)
                                    <label for="nyeri-{{ $opsi['value'] }}" class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" id="nyeri-{{ $opsi['value'] }}" name="nyeri"
                                               value="{{ $opsi['value'] }}" @checked($nyeri === $opsi['value'])>
                                        <span class="text-slate-700">{{ $opsi['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('nyeri')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= SKALA NYERI ================= --}}
            <div id="panel-nyeri" class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Penilaian Skala Nyeri</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <span class="block font-bold text-slate-800 mb-2">
                            Metode Skala Nyeri <span class="text-red-500">*</span>
                        </span>
                        <div class="flex flex-wrap gap-4">
                            @foreach (\App\Helpers\SelectOption::get('metode_skala_nyeri') as $opsi)
                                <label for="metode-{{ $opsi['value'] }}" class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" id="metode-{{ $opsi['value'] }}" name="metode_skala_nyeri"
                                           value="{{ $opsi['value'] }}" @checked($metode === $opsi['value'])>
                                    <span class="text-slate-700">{{ $opsi['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('metode_skala_nyeri')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="panel-vas">
                        @include('moduls.EMR.PartialForm._vas_scale', ['value' => $v('skor_nyeri'), 'prefix' => 'vas27'])
                    </div>

                    <div id="panel-flacc">
                        {{-- Butir FLACC form 27 tidak punya objek sendiri (nilainya
                             sudah terwakili skor_flacc), jadi isian pastanya tidak
                             bisa ditampilkan ulang saat edit. --}}
                        @include('moduls.EMR.PartialForm._flacc_scale', ['values' => [], 'idPrefix' => 'flacc27'])
                        <div class="mt-3 flex flex-wrap items-center gap-4">
                            <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded">
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">Total Skor FLACC</div>
                                <div id="flacc_total" class="text-xl font-bold text-slate-700">
                                    {{ old('skor_flacc', $emr_data['skor_flacc'] ?? '-') }}
                                </div>
                            </div>
                            <p class="text-xs text-slate-400">
                                Skor dihitung ulang di server. Skor hanya disimpan bila 5 dari 5 butir terisi.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= KARAKTERISTIK NYERI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Karakteristik Nyeri</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <span class="block font-bold text-slate-800 mb-2">
                            Frekuensi Nyeri <span class="text-red-500">*</span>
                        </span>
                        <div class="flex flex-wrap gap-4">
                            @foreach ($opsiFrekuensi as $opsi)
                                <label for="frekuensi-{{ $opsi['value'] }}" class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" id="frekuensi-{{ $opsi['value'] }}" name="frekuensi_nyeri"
                                           value="{{ $opsi['value'] }}"
                                           @checked((string) $v('frekuensi_nyeri') === $opsi['value'])>
                                    <span class="text-slate-700">{{ $opsi['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('frekuensi_nyeri')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <span class="block font-bold text-slate-800 mb-2">
                            Karakteristik Nyeri <span class="text-red-500">*</span>
                            <span class="text-xs font-normal text-slate-400">(boleh lebih dari satu)</span>
                        </span>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach ($opsiKarakteristik as $opsi)
                                <label for="kar-{{ $opsi['value'] }}" class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="checkbox" id="kar-{{ $opsi['value'] }}" name="karakteristik_nyeri[]"
                                           value="{{ $opsi['value'] }}"
                                           @checked(in_array($opsi['value'], $karakteristik, true))>
                                    <span class="text-slate-700">{{ $opsi['label'] }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div id="wrap-karakteristik-lainnya" class="mt-3 {{ in_array('Lainnya', $karakteristik, true) ? '' : 'hidden' }}">
                            <label for="karakteristik_lainnya" class="block font-bold text-slate-800 mb-1.5">
                                Keterangan Karakteristik Lainnya
                            </label>
                            <textarea id="karakteristik_lainnya" rows="2" maxlength="80"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $karakteristikLainnya }}</textarea>
                            <p class="mt-1 text-xs text-slate-400">
                                Otomatis disimpan sebagai salah satu karakteristik (maks 80 huruf).
                            </p>
                        </div>

                        @error('karakteristik_nyeri')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        @error('karakteristik_nyeri.*')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= LOKASI, PEMICU & INTERVENSI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Lokasi, Pemicu &amp; Intervensi</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <label for="lokasi_nyeri" class="block font-bold text-slate-800 mb-1.5">
                            Lokasi Nyeri (Teks) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="lokasi_nyeri" name="lokasi_nyeri" maxlength="200"
                               value="{{ $v('lokasi_nyeri') }}"
                               placeholder="Contoh: region lumbar kanan, paha kiri depan"
                               class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                        <p class="mt-1 text-xs text-slate-400">
                            Tuliskan lokasi bila pasien tidak dapat menandai body map.
                        </p>
                        @error('lokasi_nyeri')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    @include('moduls.EMR.PartialForm._body_map_nyeri', [
                        'value' => (string) $v('gambar_lokasi_nyeri'),
                        'idPrefix' => 'bodymap27',
                    ])

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="pemicu_nyeri" class="block font-bold text-slate-800 mb-1.5">
                                Faktor Pemicu yang Memperberat Nyeri <span class="text-red-500">*</span>
                            </label>
                            <textarea id="pemicu_nyeri" name="pemicu_nyeri" rows="3" maxlength="1000"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('pemicu_nyeri') }}</textarea>
                            @error('pemicu_nyeri')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="meringankan_nyeri" class="block font-bold text-slate-800 mb-1.5">
                                Faktor yang Mengurangi Nyeri <span class="text-red-500">*</span>
                            </label>
                            <textarea id="meringankan_nyeri" name="meringankan_nyeri" rows="3" maxlength="1000"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('meringankan_nyeri') }}</textarea>
                            @error('meringankan_nyeri')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="intervensi_nyeri" class="block font-bold text-slate-800 mb-1.5">
                            Instruksi / Intervensi Nyeri <span class="text-red-500">*</span>
                        </label>
                        <textarea id="intervensi_nyeri" name="intervensi_nyeri" rows="3" maxlength="2000"
                                  placeholder="Contoh: berikan analgesik 30 menit sebelum prosedur, bantu posisi nyaman"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('intervensi_nyeri') }}</textarea>
                        @error('intervensi_nyeri')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="catatan" class="block font-bold text-slate-800 mb-1.5">Keterangan</label>
                        <textarea id="catatan" name="catatan" rows="2" maxlength="1000"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('catatan') }}</textarea>
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
            // Tampil/sembunyi harus lewat style.display DAN class "hidden" — utility
            // display lain di Tailwind bisa mengalahkan .hidden (lihat AGENTS.md).
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

            var radioNyeri = document.querySelectorAll('input[name="nyeri"]');
            var radioMetode = document.querySelectorAll('input[name="metode_skala_nyeri"]');
            var panelNyeri = document.getElementById('panel-nyeri');
            var panelVas = document.getElementById('panel-vas');
            var panelFlacc = document.getElementById('panel-flacc');
            var totalFlacc = document.getElementById('flacc_total');

            function nilaiRadio(list) {
                for (var i = 0; i < list.length; i++) {
                    if (list[i].checked) return list[i].value;
                }
                return '';
            }

            function hitungTotalFlacc() {
                if (!totalFlacc) return;
                var total = 0;
                var terisi = 0;
                ['flacc_wajah', 'flacc_kaki', 'flacc_aktivitas', 'flacc_menangis', 'flacc_konsolabilitas']
                    .forEach(function (nama) {
                        var el = document.querySelector('input[name="' + nama + '"]:checked');
                        if (el) { total += parseInt(el.value, 10) || 0; terisi++; }
                    });
                // Hanya tampil bila 5/5 terisi; kalau tidak, belum dinilai.
                totalFlacc.textContent = terisi === 5 ? total : '-';
            }

            function syncPanel() {
                var adaNyeri = nilaiRadio(radioNyeri) === 'Ya';
                var metode = nilaiRadio(radioMetode);

                tampilkan(panelNyeri, adaNyeri, 'block');

                if (adaNyeri) {
                    tampilkan(panelVas, metode === 'VAS', 'block');
                    tampilkan(panelFlacc, metode === 'FLACC', 'block');
                } else {
                    tampilkan(panelVas, false, 'block');
                    tampilkan(panelFlacc, false, 'block');
                }
            }

            Array.prototype.forEach.call(radioNyeri, function (el) { el.addEventListener('change', syncPanel); });
            Array.prototype.forEach.call(radioMetode, function (el) { el.addEventListener('change', syncPanel); });
            ['flacc_wajah', 'flacc_kaki', 'flacc_aktivitas', 'flacc_menangis', 'flacc_konsolabilitas']
                .forEach(function (nama) {
                    Array.prototype.forEach.call(document.querySelectorAll('input[name="' + nama + '"]'), function (el) {
                        el.addEventListener('change', hitungTotalFlacc);
                    });
                });

            syncPanel();
            hitungTotalFlacc();

            // Keterangan bebas untuk karakteristik "Lainnya". Textarea sengaja
            // TANPA atribut name; nilainya dikirim sebagai input tersembunyi
            // bernama karakteristik_nyeri[].
            var cekLainnya = document.getElementById('kar-Lainnya');
            var wrapLainnya = document.getElementById('wrap-karakteristik-lainnya');
            var teksLainnya = document.getElementById('karakteristik_lainnya');

            function sembunyikanTeksLainnya() {
                Array.prototype.forEach.call(document.querySelectorAll('input[data-karakteristik-lainnya]'), function (el) {
                    el.parentNode.removeChild(el);
                });
            }

            function kirimTeksLainnya() {
                sembunyikanTeksLainnya();
                var teks = (teksLainnya.value || '').trim();
                if (teks === '') return;

                var tersembunyi = document.createElement('input');
                tersembunyi.type = 'hidden';
                tersembunyi.name = 'karakteristik_nyeri[]';
                tersembunyi.value = 'Lainnya: ' + teks;
                tersembunyi.setAttribute('data-karakteristik-lainnya', '1');
                teksLainnya.parentNode.insertBefore(tersembunyi, teksLainnya);
            }

            if (cekLainnya) {
                cekLainnya.addEventListener('change', function () {
                    tampilkan(wrapLainnya, cekLainnya.checked, 'block');
                    if (!cekLainnya.checked) {
                        if (teksLainnya) teksLainnya.value = '';
                        sembunyikanTeksLainnya();
                    } else {
                        kirimTeksLainnya();
                    }
                });
            }
            if (teksLainnya) {
                teksLainnya.addEventListener('input', kirimTeksLainnya);
            }
        });
    </script>

@endsection