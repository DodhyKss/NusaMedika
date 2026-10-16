@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Asesmen Spiritual';
        $subtitleForm = 'Manajemen gejala saat ini, orientasi & kebutuhan pelayanan kerohanian pasien dan keluarga.';
        $routeName = null;
        $routeUrl = url('emr/form/assesmen_spiritual');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $cb = function ($name) use ($v) {
            $checked = $v($name) === '1' ? 'checked' : '';
            return '<input type="hidden" name="'.$name.'" value="0">'
                .'<input type="checkbox" name="'.$name.'" value="1" '.$checked.'>';
        };

        $headers = [
            'spiritual' => 'Layanan Spiritual',
            'rohani_1' => 'Perlu Didoakan',
            'rohani_3' => 'Pendamping',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Spiritual"
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
                slug="assesmen_spiritual"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= MANAJEMEN GEJALA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Managemen Gejala Saat Ini dan Respon Pasien</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <p class="text-xs font-semibold text-slate-500 mb-2">Masalah Keperawatan (boleh lebih dari satu / tidak ada)</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <label class="inline-flex items-center gap-2 text-sm">{!! $cb('masalah_1') !!}<span>Bersihan Jalan Tidak Efektif</span></label>
                        <label class="inline-flex items-center gap-2 text-sm">{!! $cb('masalah_2') !!}<span>Pola Nafas Tidak Efektif</span></label>
                        <label class="inline-flex items-center gap-2 text-sm">{!! $cb('masalah_3') !!}<span>Mual</span></label>
                        <label class="inline-flex items-center gap-2 text-sm">{!! $cb('masalah_4') !!}<span>Perubahan Persepsi Sensori</span></label>
                        <label class="inline-flex items-center gap-2 text-sm">{!! $cb('masalah_5') !!}<span>Konstipasi</span></label>
                        <label class="inline-flex items-center gap-2 text-sm">{!! $cb('masalah_6') !!}<span>Defisit Perawatan Diri</span></label>
                        <label class="inline-flex items-center gap-2 text-sm">{!! $cb('masalah_7') !!}<span>Nyeri Akut</span></label>
                        <label class="inline-flex items-center gap-2 text-sm">{!! $cb('masalah_8') !!}<span>Nyeri Kronis</span></label>
                    </div>
                </div>
            </div>

            {{-- ================= ORIENTASI SPIRITUAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Orientasi Spiritual Pasien dan Keluarga</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Apakah perlu pelayanan spiritual? <span class="text-red-500">*</span></label>
                            <div class="flex gap-6 mt-1">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="spiritual" value="Tidak" {{ $v('spiritual') === 'Tidak' ? 'checked' : '' }}>
                                    <span>Tidak</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="spiritual" value="Ya" {{ $v('spiritual') === 'Ya' ? 'checked' : '' }}>
                                    <span>Ya</span>
                                </label>
                            </div>
                            @error('spiritual') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div id="blok-spiritual">
                            <label class="block text-xs font-medium mb-1">Petugas Kerohanian <span class="text-red-500">*</span> (bila perlu)</label>
                            <input type="text" id="petugas_spiritual" name="petugas_spiritual" value="{{ $v('petugas_spiritual') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('petugas_spiritual') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= URUSAN & KEBUTUHAN SPIRITUAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-violet-50 border-b border-slate-200">
                    <span class="text-violet-700 font-medium text-[15px]">Urusan dan Kebutuhan Spiritual</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Perlu Didoakan <span class="text-red-500">*</span></label>
                            <div class="flex gap-6 mt-1">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="rohani_1" value="Tidak" {{ $v('rohani_1') === 'Tidak' ? 'checked' : '' }}>
                                    <span>Tidak</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="rohani_1" value="Ya" {{ $v('rohani_1') === 'Ya' ? 'checked' : '' }}>
                                    <span>Ya</span>
                                </label>
                            </div>
                            @error('rohani_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            <div class="mt-2" id="blok-rohani-1">
                                <input type="text" id="petugas_rohani_1" name="petugas_rohani_1" value="{{ $v('petugas_rohani_1') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Nama petugas">
                                @error('petugas_rohani_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Perlu Bimbingan Rohani <span class="text-red-500">*</span></label>
                            <div class="flex gap-6 mt-1">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="rohani_2" value="Tidak" {{ $v('rohani_2') === 'Tidak' ? 'checked' : '' }}>
                                    <span>Tidak</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="rohani_2" value="Ya" {{ $v('rohani_2') === 'Ya' ? 'checked' : '' }}>
                                    <span>Ya</span>
                                </label>
                            </div>
                            @error('rohani_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            <div class="mt-2" id="blok-rohani-2">
                                <input type="text" id="petugas_rohani_2" name="petugas_rohani_2" value="{{ $v('petugas_rohani_2') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Nama petugas">
                                @error('petugas_rohani_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Perlu Pendamping Rohani <span class="text-red-500">*</span></label>
                            <div class="flex gap-6 mt-1">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="rohani_3" value="Tidak" {{ $v('rohani_3') === 'Tidak' ? 'checked' : '' }}>
                                    <span>Tidak</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="rohani_3" value="Ya" {{ $v('rohani_3') === 'Ya' ? 'checked' : '' }}>
                                    <span>Ya</span>
                                </label>
                            </div>
                            @error('rohani_3') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            <div class="mt-2" id="blok-rohani-3">
                                <input type="text" id="petugas_rohani_3" name="petugas_rohani_3" value="{{ $v('petugas_rohani_3') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Nama petugas">
                                @error('petugas_rohani_3') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function radioVal(name) {
                    var el = document.querySelector('input[name="' + name + '"]:checked');
                    return el ? el.value : '';
                }

                function toggle(blokId, inputId, tampil) {
                    var blok = document.getElementById(blokId);
                    if (blok) { blok.style.display = tampil ? '' : 'none'; }
                    var input = document.getElementById(inputId);
                    if (input) { input.disabled = !tampil; }
                }

                function sync() {
                    toggle('blok-spiritual', 'petugas_spiritual', radioVal('spiritual') === 'Ya');
                    toggle('blok-rohani-1', 'petugas_rohani_1', radioVal('rohani_1') === 'Ya');
                    toggle('blok-rohani-2', 'petugas_rohani_2', radioVal('rohani_2') === 'Ya');
                    toggle('blok-rohani-3', 'petugas_rohani_3', radioVal('rohani_3') === 'Ya');
                }

                ['spiritual', 'rohani_1', 'rohani_2', 'rohani_3'].forEach(function (name) {
                    document.querySelectorAll('input[name="' + name + '"]')
                        .forEach(function (radio) { radio.addEventListener('change', sync); });
                });
                sync();
            })();
        </script>
    </x-emr-split-layout>
@endsection
