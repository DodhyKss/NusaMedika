@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Barthel Index';
        $subtitleForm = 'Sepuluh butir kemandirian aktivitas sehari-hari, skor maksimum 20. Perhatikan arahnya: skor rendah berarti ketergantungan tinggi.';
        $routeName = null;
        $routeUrl = url('emr/form/barthel_index');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        // Ratakan ke indexed list supaya nomor baris diambil dari index, bukan
        // dari `$loop->outer` (yang tertimpa oleh loop di dalam).
        $barisBarthel = [];
        foreach ($itemBarthel as $variabel => $item) {
            $barisBarthel[] = ['variabel' => $variabel, 'label' => $item['label'], 'opsi' => $item['opsi']];
        }

        $kategoriJson = json_encode(array_map(fn($k) => [
            'label' => $k['label'],
            'min' => $k['min'],
            'max' => $k['max'],
            'warna' => $k['warna'],
            'keterangan' => $k['keterangan'],
        ], $kategoriBarthel));

        $headers = [
            'barthel_skor' => 'Skor',
            'tingkat_ketergantungan' => 'Tingkat Ketergantungan',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Barthel Index"
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
                slug="barthel_index"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS PENGAMATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanggal &amp; Waktu Penilaian</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="tanggal_barthel" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Penilaian <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_barthel" name="tanggal_barthel"
                                   value="{{ $v('tanggal_barthel') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_barthel')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_barthel" class="block font-bold text-slate-800 mb-1.5">
                                Waktu Penilaian <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_barthel" name="waktu_barthel" step="60"
                                   value="{{ $v('waktu_barthel') ? substr((string) $v('waktu_barthel'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_barthel')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= RINGKASAN SKOR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Ringkasan Barthel Index</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="px-5 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Total Skor</div>
                            <div id="barthel-skor" class="text-2xl font-bold text-slate-700">
                                {{ $v('barthel_skor') !== '' ? $v('barthel_skor') : '-' }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-[260px] px-5 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Tingkat Ketergantungan</div>
                            <div id="barthel-tingkat" class="text-base font-semibold text-slate-700">
                                {{ $v('tingkat_ketergantungan') !== '' ? $v('tingkat_ketergantungan') : '-' }}
                            </div>
                            <div id="barthel-keterangan" class="text-xs text-slate-500 mt-1">
                                {{ $v('tingkat_ketergantungan') !== '' ? 'Skor lengkap, hasil dihitung di server.' : 'Lengkapi sepuluh butir di bawah.' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500">
                                    <th class="px-2 py-2 w-32">Total Skor</th>
                                    <th class="px-2 py-2">Tingkat Ketergantungan</th>
                                    <th class="px-2 py-2">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($kategoriBarthel as $k)
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
                        Arah skor Barthel: <strong>semakin rendah skor, semakin tinggi tingkat ketergantungan</strong>.
                        Skor 20 berarti mandiri, skor 0 berarti ketergantungan total.
                        Skor dan tingkat ketergantungan dihitung ulang di server; nilai hanya disimpan bila sepuluh butir terisi.
                    </p>

                    <p class="mt-2 text-xs text-slate-400">
                        Catatan: inkontinensia pada Barthel memakai skor 1 berarti kadang inkontinen dan skor 0 berarti inkontinen.
                        Arah ini berlawanan dengan tabel status fungsional, jadi kedua form sengaja dipisahkan.
                    </p>
                </div>
            </div>

            {{-- ================= BUTIR BARTHEL INDEX ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Butir Penilaian Barthel Index</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="overflow-x-auto drag-scroll">
                        <table class="w-full text-left border-collapse" style="min-width: 720px;">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500">
                                    <th class="px-3 py-3 w-10">No</th>
                                    <th class="px-3 py-3 w-56">Butir</th>
                                    <th class="px-3 py-3">Kemampuan Pasien <span class="text-red-500">*</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($barisBarthel as $nomor => $baris)
                                    <tr>
                                        <td class="px-3 py-3 text-slate-500">{{ $nomor + 1 }}</td>
                                        <td class="px-3 py-3 font-medium text-slate-800">{{ $baris['label'] }}</td>
                                        <td class="px-3 py-3">
                                            <div class="flex flex-col gap-1">
                                                @foreach ($baris['opsi'] as $opt)
                                                    <?php $idRadio = 'barthel_'.$nomor.'_'.$opt['value']; ?>
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

            {{-- ================= CATATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Catatan</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label for="catatan" class="block font-bold text-slate-800 mb-1.5">Keterangan</label>
                    <textarea id="catatan" name="catatan" rows="3"
                              class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('catatan') }}</textarea>
                    @error('catatan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Skor di layar HANYA tampilan. Server menghitung ulang sendiri dari
            // sepuluh butir mentah dan membuang nilai yang dikirim browser.
            var kategori = {!! $kategoriJson !!};
            var groups = {!! json_encode(array_column($barisBarthel, 'variabel')) !!};

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
                    yellow: 'bg-yellow-100 text-yellow-700',
                    slate: 'bg-slate-100 text-slate-600',
                    blue: 'bg-blue-100 text-blue-700',
                    green: 'bg-emerald-100 text-emerald-700'
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

                var outSkor = document.getElementById('barthel-skor');
                var outTingkat = document.getElementById('barthel-tingkat');
                var outKeterangan = document.getElementById('barthel-keterangan');
                if (!outSkor || !outTingkat) return;

                if (terisi < groups.length) {
                    outSkor.textContent = '-';
                    outTingkat.textContent = '-';
                    outTingkat.className = 'text-base font-semibold text-slate-700';
                    outKeterangan.textContent = 'Lengkapi sepuluh butir di bawah (' + terisi + ' dari ' + groups.length + ').';
                    return;
                }

                var kat = null;
                for (var i = 0; i < kategori.length; i++) {
                    if (total >= kategori[i].min && total <= kategori[i].max) { kat = kategori[i]; break; }
                }

                outSkor.textContent = total;
                outTingkat.innerHTML = kat ? badge(kat) : '-';
                outTingkat.className = 'text-base font-semibold text-slate-700';
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
