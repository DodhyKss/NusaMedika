@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Pelaksanaan Bayi Baru Lahir';
        $subtitleForm = 'Immediate newborn care — penilaian awal, skin-to-skin, dan daftar tindakan bayi normal / ada penyulit.';
        $routeName = null;
        $routeUrl = url('emr/form/bayi_baru_lahir');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'penilaian_bayi_baru_lahir' => 'Penilaian',
            'penilaian_bayi_baru_lahir_ya' => 'Pelaksanaan',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Pelaksanaan Bayi Baru Lahir"
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
                slug="bayi_baru_lahir"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= PENILAIAN AWAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Penilaian Bayi Baru Lahir</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-2">Penilaian <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2"><input type="radio" name="penilaian_bayi_baru_lahir" value="Baik" {{ $v('penilaian_bayi_baru_lahir') === 'Baik' ? 'checked' : '' }}> Baik</label>
                            <label class="inline-flex items-center gap-2"><input type="radio" name="penilaian_bayi_baru_lahir" value="Ada penyulit" class="penyulit-toggle" {{ $v('penilaian_bayi_baru_lahir') === 'Ada penyulit' ? 'checked' : '' }}> Ada penyulit</label>
                        </div>
                        @error('penilaian_bayi_baru_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-2">Pelaksanaan Penilaian <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2"><input type="radio" name="penilaian_bayi_baru_lahir_ya" value="Ya" class="ya-toggle" {{ $v('penilaian_bayi_baru_lahir_ya') === 'Ya' ? 'checked' : '' }}> Ya</label>
                            <label class="inline-flex items-center gap-2"><input type="radio" name="penilaian_bayi_baru_lahir_ya" value="Tidak diberikan" class="ya-toggle" {{ $v('penilaian_bayi_baru_lahir_ya') === 'Tidak diberikan' ? 'checked' : '' }}> Tidak diberikan</label>
                        </div>
                        @error('penilaian_bayi_baru_lahir_ya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-waktu" style="{{ $v('penilaian_bayi_baru_lahir_ya') === 'Ya' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Waktu Skin-to-skin (… setelah lahir) <span class="text-red-500">*</span></label>
                        <input type="text" name="waktu_penilaian_bayi" value="{{ $v('waktu_penilaian_bayi') }}"
                            placeholder="mis. 1 jam" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('waktu_penilaian_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-alasan" style="{{ $v('penilaian_bayi_baru_lahir_ya') === 'Tidak diberikan' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Alasan Tidak Diberikan <span class="text-red-500">*</span></label>
                        <input type="text" name="alasan_tidak_penilaian" value="{{ $v('alasan_tidak_penilaian') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('alasan_tidak_penilaian') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= TINDAKAN BAYI NORMAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tindakan Bayi Lahir Normal <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach ($tindakanNormal as $i => $label)
                            @php $idx = $i + 1; @endphp
                            <label class="inline-flex items-center gap-2">
                                <input type="hidden" name="tindakan_bayi_normal_{{ $idx }}" value="0">
                                <input type="checkbox" name="tindakan_bayi_normal_{{ $idx }}" value="1"
                                    {{ $v('tindakan_bayi_normal_'.$idx) === '1' ? 'checked' : '' }}> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('tindakan_bayi_normal_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= TINDAKAN BAYI TIDAK NORMAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm" id="blok-abnormal" style="{{ $v('penilaian_bayi_baru_lahir') === 'Ada penyulit' ? '' : 'display:none' }}">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Tindakan Bayi Lahir Tidak Normal</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        @foreach ($tindakanAbnormal as $i => $label)
                            @php $idx = $i + 1; @endphp
                            <label class="inline-flex items-center gap-2">
                                <input type="hidden" name="tindakan_bayi_abnormal_{{ $idx }}" value="0">
                                <input type="checkbox" name="tindakan_bayi_abnormal_{{ $idx }}" value="1"
                                    {{ $v('tindakan_bayi_abnormal_'.$idx) === '1' ? 'checked' : '' }}> {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('tindakan_bayi_abnormal_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                var blokWaktu = document.getElementById('blok-waktu');
                var blokAlasan = document.getElementById('blok-alasan');
                var blokAbnormal = document.getElementById('blok-abnormal');
                document.querySelectorAll('.ya-toggle').forEach(function (r) {
                    r.addEventListener('change', function () {
                        blokWaktu.style.display = this.value === 'Ya' ? '' : 'none';
                        blokAlasan.style.display = this.value === 'Tidak diberikan' ? '' : 'none';
                    });
                });
                document.querySelectorAll('.penyulit-toggle').forEach(function (r) {
                    r.addEventListener('change', function () {
                        blokAbnormal.style.display = this.value === 'Ada penyulit' ? '' : 'none';
                    });
                });
            })();
        </script>
    </x-emr-split-layout>
@endsection
