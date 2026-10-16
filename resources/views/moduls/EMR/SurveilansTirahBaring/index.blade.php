@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Surveilans Pasien Tirah Baring Total';
        $subtitleForm = 'Bundle pencegahan HAP harian — suhu, leukosit, sekresi trakea, kultur & antibiotik.';
        $routeName = null;
        $routeUrl = url('emr/form/surveilans_tirah_baring');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_evaluasi' => 'Tgl Evaluasi',
            'hap_ab_1' => 'Antibiotik',
            'keterangan' => 'Keterangan',
        ];

        $jawaban = ['Ya', 'Tidak', 'N/A'];
        $sel = function ($name, $list) use ($v) {
            $html = '<select name="'.$name.'" class="w-full border rounded-sm px-2 py-1.5 text-sm"><option value="">-- Pilih --</option>';
            foreach ($list as $opt) {
                $attr = $v($name) === $opt ? ' selected' : '';
                $html .= '<option value="'.$opt.'"'.$attr.'>'.$opt.'</option>';
            }
            return $html.'</select>';
        };

        $bundleHap = [
            'hap_1' => 'Cuci tangan sesuai 5 moment',
            'hap_2' => 'Oral hygiene 2x/day',
            'hap_3' => 'Posisi kepala 30-45 derajat',
            'hap_4' => 'Ganti/bersihkan peralatan bila dipakai bersama',
        ];
        $gejalaHap = [
            'Suhu tubuh' => [
                'hap_suhu_1' => '36 c - 38 c',
                'hap_suhu_2' => '> 38 c',
                'hap_suhu_3' => '< 36 c',
            ],
            'Leukosit' => [
                'hap_leukosit_1' => '4000 - 11.999/mm3',
                'hap_leukosit_2' => '>= 12000/mm3',
                'hap_leukosit_3' => '<= 4000/mm3',
            ],
            'Sekresi trakea' => [
                'hap_sekresi_1' => 'Tidak ada / ada non-purulent',
                'hap_sekresi_2' => 'Ada purulent',
            ],
            'Hasil kultur sputum' => [
                'hap_kultur_1' => 'Tidak ada pertumbuhan kuman (tumbuh yeast/candida)',
                'hap_kultur_2' => 'Ada pertumbuhan kuman selain yeast/candida',
                'hap_kultur_3' => 'Tumbuh kuman selain yeast/candida dengan jumlah >= 10 CFU/ml',
            ],
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Surveilans Tirah Baring"
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
                slug="surveilans_tirah_baring"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS EVALUASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Evaluasi</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_evaluasi" value="{{ $v('tanggal_evaluasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Waktu Evaluasi <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_evaluasi" value="{{ $v('waktu_evaluasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('waktu_evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="hidden md:block"></div>
                </div>
            </div>

            {{-- ================= BUNDLE PENCEGAHAN HAP ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-sky-50 border-b border-slate-200">
                    <span class="text-sky-700 font-medium text-[15px]">Bundle Pencegahan HAP</span>
                </div>
                <div class="p-5 bg-white text-sm divide-y divide-slate-100">
                    @foreach ($bundleHap as $key => $label)
                        <div class="py-2 grid grid-cols-1 md:grid-cols-[1fr_140px] gap-3 items-center">
                            <span class="text-sm">{{ $loop->iteration }}. {{ $label }}</span>
                            <div>
                                {!! $sel($key, $jawaban) !!}
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= GEJALA HAP ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-red-50 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-red-700 font-medium text-[15px]">Gejala &amp; Tanda HAP</span>
                    <span id="badge-hap" style="display:none"
                        class="text-xs font-semibold text-red-700 bg-red-100 border border-red-300 rounded-full px-3 py-1">
                        Gejala HAP ditemukan — evaluasi lanjutan
                    </span>
                </div>
                <div class="p-5 bg-white text-sm space-y-5">
                    @foreach ($gejalaHap as $grup => $items)
                        <div>
                            <p class="text-xs font-semibold text-slate-600 mb-2">{{ $grup }}</p>
                            <div class="space-y-1.5 max-w-3xl">
                                @foreach ($items as $key => $label)
                                    <div class="grid grid-cols-1 md:grid-cols-[1fr_140px] gap-3 items-center">
                                        <span class="text-sm">{{ $label }}</span>
                                        <div>
                                            {!! $sel($key, $jawaban) !!}
                                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= ANTIBIOTIK & KETERANGAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Antibiotik yang Diberikan &amp; Keterangan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Nama Ab 1 <span class="text-red-500">*</span></label>
                        <input type="text" name="hap_ab_1" value="{{ $v('hap_ab_1') }}" maxlength="200" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('hap_ab_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Nama Ab 2</label>
                        <input type="text" name="hap_ab_2" value="{{ $v('hap_ab_2') }}" maxlength="200" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('hap_ab_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Nama Ab 3</label>
                        <input type="text" name="hap_ab_3" value="{{ $v('hap_ab_3') }}" maxlength="200" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('hap_ab_3') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium mb-1">Keterangan (nama kuman hasil kultur &amp; sensitivitas)</label>
                        <textarea name="keterangan" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keterangan') }}</textarea>
                        @error('keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function cek119Hap() {
                    var ya = function (name) {
                        var el = document.querySelector('select[name="' + name + '"]');
                        return el && el.value === 'Ya';
                    };
                    var gejala = ya('hap_suhu_2') || ya('hap_leukosit_2')
                        || ya('hap_sekresi_2') || ya('hap_kultur_3');

                    var badge = document.getElementById('badge-hap');
                    if (badge) { badge.style.display = gejala ? '' : 'none'; }
                }

                document.querySelectorAll('select[name^="hap_"]').forEach(function (el) {
                    if (el.name.indexOf('hap_ab_') === 0) { return; }
                    el.addEventListener('change', cek119Hap);
                });
                cek119Hap();
            })();
        </script>
    </x-emr-split-layout>
@endsection
