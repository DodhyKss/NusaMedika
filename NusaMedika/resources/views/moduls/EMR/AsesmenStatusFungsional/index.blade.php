@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Asesmen Status Fungsional';
        $subtitleForm = 'Penilaian 10 item kemampuan ADL (skor maksimum 20). Berbeda dari Barthel Index baku (form 33) yang dipakai untuk rehabilitasi dan discharge planning.';
        $routeName = null;
        $routeUrl = url('emr/form/asesmen_status_fungsional');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $interpretasiJson = json_encode(array_map(fn($b) => [
            'label' => $b['label'],
            'min' => $b['min'],
            'max' => $b['max'],
            'class' => $b['class'],
        ], $interpretasi));

        // Ratakan ke indexed list: `$loop` milik loop dalam menimpa `$loop` luar,
        // jadi nomor baris diambil dari index, bukan dari `$loop->outer`.
        $barisFungsional = [];
        foreach ($itemFungsional as $variabel => $item) {
            $barisFungsional[] = ['variabel' => $variabel, 'label' => $item['label'], 'opsi' => $item['opsi']];
        }

        $headers = [
            'sf_skor' => 'Skor',
            'sf_interpretasi' => 'Interpretasi',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Status Fungsional"
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
                slug="asesmen_status_fungsional"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= RINGKASAN SKOR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Ringkasan Status Fungsional</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="px-5 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Total Skor</div>
                            <div id="sf-skor" class="text-2xl font-bold text-slate-700">
                                {{ $v('sf_skor') !== '' ? $v('sf_skor') : '-' }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-[200px] px-5 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Interpretasi</div>
                            <div id="sf-interpretasi" class="text-base font-semibold text-slate-700">
                                {{ $v('sf_interpretasi') !== '' ? $v('sf_interpretasi') : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500">
                                    <th class="px-2 py-2">Total Skor</th>
                                    <th class="px-2 py-2">Interpretasi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($interpretasi as $b)
                                    <tr>
                                        <td class="px-2 py-2 text-slate-700 whitespace-nowrap">{{ $b['min'] }} &ndash; {{ $b['max'] }}</td>
                                        <td class="px-2 py-2 font-semibold {{ $b['class'] }}">{{ $b['label'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-3 text-xs text-slate-400">
                        Skor dan interpretasi dihitung ulang di server dari 10 item di bawah. Kategori hanya
                        disimpan bila seluruh 10 item terisi.
                    </p>
                </div>
            </div>

            {{-- ================= ITEM ADL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Item Aktivitas Kehidupan Sehari-hari</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="overflow-x-auto drag-scroll">
                        <table class="w-full text-left border-collapse" style="min-width: 720px;">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500">
                                    <th class="px-3 py-3 w-10">No</th>
                                    <th class="px-3 py-3 w-56">Item</th>
                                    <th class="px-3 py-3">Pilihan Skor <span class="text-red-500">*</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($barisFungsional as $nomor => $baris)
                                    <tr>
                                        <td class="px-3 py-3 text-slate-500">{{ $nomor + 1 }}</td>
                                        <td class="px-3 py-3 font-medium text-slate-800">{{ $baris['label'] }}</td>
                                        <td class="px-3 py-3">
                                            <div class="flex flex-col gap-1">
                                                @foreach ($baris['opsi'] as $opt)
                                                    <?php $id = 'sf_'.$nomor.'_'.md5($opt['value']); ?>
                                                    <label for="{{ $id }}" class="flex items-center gap-2 px-2 py-1.5 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                                        <input type="radio" id="{{ $id }}" name="{{ $baris['variabel'] }}"
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

        </fieldset>

    </x-emr-split-layout>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Skor di layar HANYA tampilan. Server menghitung ulang sendiri dan
            // membuang nilai ini dari browser.
            var daftar = {!! $interpretasiJson !!};

            function warnaSkor(skor) {
                for (var i = 0; i < daftar.length; i++) {
                    if (skor >= daftar[i].min && skor <= daftar[i].max) {
                        return daftar[i].class;
                    }
                }
                return 'text-slate-700';
            }

            function hitungSkor() {
                var inputs = document.querySelectorAll('input[type="radio"][name^="sf_"]');
                var total = 0;
                var terisi = 0;
                var kelompok = {};

                Array.prototype.forEach.call(inputs, function (el) {
                    kelompok[el.name] = kelompok[el.name] || [];
                    kelompok[el.name].push(el);
                });

                Object.keys(kelompok).forEach(function (nama) {
                    var terpilih = kelompok[nama].filter(function (el) { return el.checked; })[0];
                    if (!terpilih || terpilih.value === '') return;
                    terisi++;
                    total += parseInt(terpilih.value, 10);
                });

                var outSkor = document.getElementById('sf-skor');
                var outInter = document.getElementById('sf-interpretasi');
                var jumlah = Object.keys(kelompok).length;

                if (!outSkor || !outInter) return;

                if (terisi < jumlah) {
                    outSkor.textContent = '-';
                    outInter.textContent = '-';
                    outInter.className = 'text-base font-semibold text-slate-700';
                    return;
                }

                outSkor.textContent = total;
                var interpretasi = '-';
                for (var i = 0; i < daftar.length; i++) {
                    if (total >= daftar[i].min && total <= daftar[i].max) {
                        interpretasi = daftar[i].label;
                        break;
                    }
                }
                outInter.textContent = interpretasi;
                outInter.className = 'text-base font-semibold ' + warnaSkor(total);
            }

            Array.prototype.forEach.call(
                document.querySelectorAll('input[type="radio"][name^="sf_"]'),
                function (el) {
                    el.addEventListener('change', hitungSkor);
                }
            );
            hitungSkor();
        });
    </script>

@endsection
