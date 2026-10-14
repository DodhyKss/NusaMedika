@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Riwayat Resusitasi Bayi';
        $subtitleForm = 'Dokumentasi tindakan resusitasi neonatal dan parameter CPAP bila diberikan.';
        $routeName = null;
        $routeUrl = url('emr/form/riwayat_resusitasi_bayi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $headers = ['riwayat_resusitasi' => 'Resusitasi'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Resusitasi Bayi"
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
                slug="riwayat_resusitasi_bayi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Resusitasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-2">Apakah resusitasi dilakukan? <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2"><input type="radio" name="riwayat_resusitasi" value="Ya" class="res-toggle" {{ $v('riwayat_resusitasi') === 'Ya' ? 'checked' : '' }}> Ya</label>
                            <label class="inline-flex items-center gap-2"><input type="radio" name="riwayat_resusitasi" value="Tidak" class="res-toggle" {{ $v('riwayat_resusitasi') === 'Tidak' ? 'checked' : '' }}> Tidak</label>
                        </div>
                        @error('riwayat_resusitasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="blok-resusitasi" style="{{ $v('riwayat_resusitasi') === 'Ya' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-2">Tindakan Resusitasi</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach ($tindakanResusitasi as $i => $label)
                                @php $idx = $i + 1; @endphp
                                <label class="inline-flex items-center gap-2">
                                    <input type="hidden" name="tindakan_resusitasi_{{ $idx }}" value="0">
                                    <input type="checkbox" name="tindakan_resusitasi_{{ $idx }}" value="1" class="tindakan-res"
                                        {{ $v('tindakan_resusitasi_'.$idx) === '1' ? 'checked' : '' }}> {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('tindakan_resusitasi_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                        <div id="blok-cpap" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4" style="{{ $v('tindakan_resusitasi_3') === '1' ? '' : 'display:none' }}">
                            <div>
                                <label class="block text-xs font-medium mb-1">PEEP CPAP (cmH₂O)</label>
                                <input type="number" step="0.1" name="peep_cpap" value="{{ $v('peep_cpap') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('peep_cpap') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Flow (L/menit)</label>
                                <input type="number" step="0.1" name="flow_cpap" value="{{ $v('flow_cpap') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('flow_cpap') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">FiO₂ (%)</label>
                                <input type="text" name="fio2_cpap" value="{{ $v('fio2_cpap') }}" placeholder="21" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('fio2_cpap') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Durasi</label>
                                <input type="text" name="durasi_cpap" value="{{ $v('durasi_cpap') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('durasi_cpap') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-medium mb-1">Catatan Resusitasi</label>
                            <textarea name="catatan_resusitasi" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('catatan_resusitasi') }}</textarea>
                            @error('catatan_resusitasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                var blok = document.getElementById('blok-resusitasi');
                var blokCpap = document.getElementById('blok-cpap');
                document.querySelectorAll('.res-toggle').forEach(function (r) {
                    r.addEventListener('change', function () {
                        blok.style.display = this.value === 'Ya' ? '' : 'none';
                    });
                });
                function syncCpap() {
                    var cb = document.querySelector('input[name="tindakan_resusitasi_3"]');
                    blokCpap.style.display = (cb && cb.checked) ? '' : 'none';
                }
                document.querySelectorAll('.tindakan-res').forEach(function (cb) {
                    cb.addEventListener('change', syncCpap);
                });
                syncCpap();
            })();
        </script>
    </x-emr-split-layout>
@endsection
