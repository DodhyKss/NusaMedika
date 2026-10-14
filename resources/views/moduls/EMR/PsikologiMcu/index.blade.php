@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Psikologi MCU';
        $subtitleForm = 'Asesmen psikologis oleh psikolog klinis. Interpretasi dihitung otomatis dari skala GAD-2 & PHQ-2.';
        $routeName = null;
        $routeUrl = url('emr/form/psikologi_mcu');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');
        $ada = $v('gangguan_kehidupan_ada') === '1';

        $headers = ['interpretasi' => 'Interpretasi', 'saran' => 'Saran'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Psikologi MCU"
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
                slug="psikologi_mcu"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= GANGGUAN KEHIDUPAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Status Psikologis</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-2">Gangguan Kehidupan <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="gangguan_kehidupan_ada" value="0" class="gk-toggle"
                                    {{ ! $ada ? 'checked' : '' }}> Tidak Ada
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="gangguan_kehidupan_ada" value="1" class="gk-toggle"
                                    {{ $ada ? 'checked' : '' }}> Ada
                            </label>
                        </div>
                        @error('gangguan_kehidupan_ada') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="gk-uraian" style="{{ $ada ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Uraian Gangguan Kehidupan</label>
                        <textarea name="gangguan_kehidupan" rows="3"
                            class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('gangguan_kehidupan') }}</textarea>
                        @error('gangguan_kehidupan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= SKALA EMOSI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Skala Cemas &amp; Depresi (GAD-2 / PHQ-2)</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Skala Cemas (GAD-2, 0–4) <span class="text-red-500">*</span></label>
                            <input type="number" min="0" max="4" name="skala_cemas" value="{{ $v('skala_cemas') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @error('skala_cemas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Skala Depresi (PHQ-2, 0–4) <span class="text-red-500">*</span></label>
                            <input type="number" min="0" max="4" name="skala_depresi" value="{{ $v('skala_depresi') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @error('skala_depresi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">Interpretasi dihitung otomatis: total 0–2 Normal, 3–4 Keluhan ringan, ≥ 5 Rujukan ke psikolog/klinisi.</p>
                </div>
            </div>

            {{-- ================= SARAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Saran</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <textarea name="saran" rows="3"
                        class="w-full border rounded-sm px-3 py-2 text-sm" required>{{ $v('saran') }}</textarea>
                    @error('saran') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                // Toggle uraian mengikuti radio "Ada / Tidak Ada".
                // Lepas/pasang atribut name agar field non-aktif tidak ikut terkirim.
                var uraian = document.getElementById('gk-uraian');
                var input = uraian.querySelector('textarea');
                document.querySelectorAll('.gk-toggle').forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        var ada = this.value === '1';
                        uraian.style.display = ada ? '' : 'none';
                        if (ada) { input.setAttribute('name', 'gangguan_kehidupan'); }
                        else { input.removeAttribute('name'); }
                    });
                });
                if (!{{ $ada ? 'true' : 'false' }}) { input.removeAttribute('name'); }
            })();
        </script>
    </x-emr-split-layout>
@endsection
