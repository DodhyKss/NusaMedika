@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Penilaian Risiko Dekubitus (Norton)';
        $subtitleForm = 'Lima butir Skala Norton, skor 5 sampai 20. Perhatikan arahnya: skor rendah berarti risiko tinggi.';
        $routeName = null;
        $routeUrl = url('emr/form/penilaian_risiko_dekubitus');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        // Ratakan ke indexed list supaya nomor baris diambil dari index, bukan
        // dari `$loop->outer` (yang tertimpa oleh loop di dalam).
        $barisNorton = [];
        foreach ($itemNorton as $variabel => $item) {
            $barisNorton[] = ['variabel' => $variabel, 'label' => $item['label'], 'opsi' => $item['opsi']];
        }

        $kategoriJson = json_encode(array_map(fn($k) => [
            'label' => $k['label'],
            'min' => $k['min'],
            'max' => $k['max'],
            'warna' => $k['warna'],
            'keterangan' => $k['keterangan'],
        ], $kategoriNorton));

        $headers = [
            'skor_norton' => 'Skor',
            'kategori_dekubitus' => 'Kategori Risiko',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Penilaian Dekubitus"
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
                slug="penilaian_risiko_dekubitus"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS PENGAMATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanggal &amp; Waktu Pengamatan</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="tanggal_norton" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_norton" name="tanggal_norton"
                                   value="{{ $v('tanggal_norton') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_norton')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_norton" class="block font-bold text-slate-800 mb-1.5">
                                Waktu Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_norton" name="waktu_norton" step="60"
                                   value="{{ $v('waktu_norton') ? substr((string) $v('waktu_norton'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_norton')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= RINGKASAN SKOR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Ringkasan Skala Norton</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="px-5 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Total Skor</div>
                            <div id="norton-skor" class="text-2xl font-bold text-slate-700">
                                {{ $v('skor_norton') !== '' ? $v('skor_norton') : '-' }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-[260px] px-5 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Kategori Risiko Dekubitus</div>
                            <div id="norton-kategori" class="text-base font-semibold text-slate-700">
                                {{ $v('kategori_dekubitus') !== '' ? $v('kategori_dekubitus') : '-' }}
                            </div>
                            <div id="norton-keterangan" class="text-xs text-slate-500 mt-1">
                                {{ $v('kategori_dekubitus') !== '' ? 'Skor lengkap, hasil dihitung di server.' : 'Lengkapi kelima butir di bawah.' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500">
                                    <th class="px-2 py-2 w-32">Total Skor</th>
                                    <th class="px-2 py-2">Kategori Risiko</th>
                                    <th class="px-2 py-2">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($kategoriNorton as $k)
                                    <tr>
                                        <td class="px-2 py-2 text-slate-700 whitespace-nowrap">{{ $k['min'] }} &ndash; {{ $k['max'] }}</td>
                                        <td class="px-2 py-2 font-semibold {{ $k['warna_class'] }}">{{ $k['label'] }}</td>
                                        <td class="px-2 py-2 text-slate-600">{{ $k['keterangan'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-3 py-2">
                        Arah skor Norton: <strong>semakin rendah skor, semakin tinggi risiko dekubitus</strong>.
                        Skor 5 berarti risiko tinggi, skor 20 berarti tidak berisiko.
                        Skor dan kategori dihitung ulang di server; nilai hanya disimpan bila kelima butir terisi.
                    </p>
                </div>
            </div>

            {{-- ================= BUTIR SKALA NORTON ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Butir Penilaian Skala Norton</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="overflow-x-auto drag-scroll">
                        <table class="w-full text-left border-collapse" style="min-width: 720px;">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500">
                                    <th class="px-3 py-3 w-10">No</th>
                                    <th class="px-3 py-3 w-56">Butir</th>
                                    <th class="px-3 py-3">Kondisi Pasien <span class="text-red-500">*</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($barisNorton as $nomor => $baris)
                                    <tr>
                                        <td class="px-3 py-3 text-slate-500">{{ $nomor + 1 }}</td>
                                        <td class="px-3 py-3 font-medium text-slate-800">{{ $baris['label'] }}</td>
                                        <td class="px-3 py-3">
                                            <div class="flex flex-col gap-1">
                                                @foreach ($baris['opsi'] as $opt)
                                                    <?php $idRadio = 'norton_'.$nomor.'_'.$opt['value']; ?>
                                                    <label for="{{ $idRadio }}" class="flex items-center gap-2 px-2 py-1.5 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                                        <input type="radio" id="{{ $idRadio }}" name="{{ $baris['variabel'] }}"
                                                               value="{{ $opt['value'] }}"
                                                               @checked((string) $v($baris['variabel']) === (string) $opt['value'])
                                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            @error($baris['variabel'])
                                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ================= KESADARAN & CATATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kesadaran &amp; Catatan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1.5">
                            Kesadaran
                            <span class="text-xs font-normal text-slate-400">(konsistensi dengan asesmen sebelumnya)</span>
                        </label>
                        <div class="flex flex-wrap gap-3">
                            @foreach (\App\Helpers\SelectOption::get('kesadaran') as $opt)
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" name="kesadaran" value="{{ $opt['value'] }}"
                                           @checked((string) $v('kesadaran') === (string) $opt['value'])
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('kesadaran')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="catatan" class="block font-bold text-slate-800 mb-1.5">Keterangan</label>
                        <textarea id="catatan" name="catatan" rows="3"
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
            // Skor di layar HANYA tampilan. Server menghitung ulang sendiri dari
            // lima butir mentah dan membuang nilai yang dikirim browser.
            var kategori = {!! $kategoriJson !!};

            var groups = {!! json_encode(array_column($barisNorton, 'variabel')) !!};

            // PENTING: simpan SELURUH grup radio, bukan hanya radio pertama.
            // Kalau hanya radio pertama yang diambil lalu dibaca lewat .checked,
            // memilih opsi kedua akan membuat radio pertama ter-uncheck sehingga
            // jawaban terbaca kosong dan skor tidak pernah berubah (bug historis,
            // lihat AGENTS.md section Pengkajian Risiko Jatuh).
            var grupRadios = {};
            groups.forEach(function (nama) {
                grupRadios[nama] = Array.prototype.slice.call(
                    document.querySelectorAll('input[type="radio"][name="' + nama + '"]')
                );
            });

            function terpilih(nama) {
                var els = grupRadios[nama] || [];
                for (var i = 0; i < els.length; i++) {
                    if (els[i].checked) return els[i].value;
                }
                return null;
            }

            function badge(kat) {
                var kelas = {
                    red: 'bg-red-100 text-red-700',
                    amber: 'bg-amber-100 text-amber-700',
                    lime: 'bg-lime-100 text-lime-700',
                    slate: 'bg-slate-100 text-slate-600'
                };
                return '<span class="inline-flex px-3 py-1 rounded-full text-xs font-bold '
                    + (kelas[kat.warna] || kelas.slate) + '">' + kat.label + '</span>';
            }

            function hitungSkor() {
                var total = 0;
                var terisi = 0;

                groups.forEach(function (nama) {
                    var v = terpilih(nama);
                    if (v === null || v === '') return;
                    terisi++;
                    total += parseInt(v, 10);
                });

                var outSkor = document.getElementById('norton-skor');
                var outKategori = document.getElementById('norton-kategori');
                var outKeterangan = document.getElementById('norton-keterangan');
                if (!outSkor || !outKategori) return;

                if (terisi < groups.length) {
                    outSkor.textContent = '-';
                    outKategori.textContent = '-';
                    outKategori.className = 'text-base font-semibold text-slate-700';
                    outKeterangan.textContent = 'Lengkapi kelima butir di bawah (' + terisi + ' dari ' + groups.length + ').';
                    return;
                }

                var kat = null;
                for (var i = 0; i < kategori.length; i++) {
                    if (total >= kategori[i].min && total <= kategori[i].max) { kat = kategori[i]; break; }
                }

                outSkor.textContent = total;
                outKategori.innerHTML = kat ? badge(kat) : '-';
                outKategori.className = 'text-base font-semibold text-slate-700';
                outKeterangan.textContent = kat ? kat.keterangan : '';
            }

            Object.keys(grupRadios).forEach(function (nama) {
                (grupRadios[nama] || []).forEach(function (el) {
                    el.addEventListener('change', hitungSkor);
                });
            });

            hitungSkor();
        });
    </script>

@endsection
