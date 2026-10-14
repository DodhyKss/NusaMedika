@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Triage Hemodialisa';
        $subtitleForm = 'Triase awal pasien sebelum sesi hemodialisa.';
        $routeName = null;
        $routeUrl = url('emr/form/triage_hemodialisa');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');
        $headers = [
            'keluhan' => 'Keluhan',
            'care_plan' => 'Care Plan',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Triage Hemodialisa"
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
                slug="triage_hemodialisa"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Data Triase</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Keluhan Utama <span class="text-red-500">*</span></label>
                        <textarea name="keluhan" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keluhan') }}</textarea>
                        @error('keluhan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Berat Badan Kering (kg) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="bb_kering" value="{{ $v('bb_kering') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('bb_kering') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Berat Badan Saat Ini (kg) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="berat_badan" value="{{ $v('berat_badan') }}" class="w-full border rounded-sm px-3 py-2 text-sm" oninput="cekSelisihBb()">
                            @error('berat_badan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    @if (($selisih['kategori'] ?? '') !== '' && $selisih['kategori'] !== 'Ideal' && $selisih['kategori'] !== 'Data Tidak Lengkap')
                        <div class="rounded-sm border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                            Peringatan: {{ $selisih['kategori'] }} {{ $selisih['selisih_kg'] }} kg — periksa pompa &amp; akses vaskuler / status hidrasi.
                        </div>
                    @endif
                    <div id="peringatan-bb" style="display:none" class="rounded-sm border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-800"></div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Care Plan <span class="text-red-500">*</span></label>
                        <select name="care_plan" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <option value="">— Pilih —</option>
                            {!! \App\Helpers\SelectOption::render('care_plan_hd', $v('care_plan')) !!}
                        </select>
                        @error('care_plan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="informed_consent" value="">
                            <input type="checkbox" id="informed_consent" name="informed_consent" value="lengkap"
                                {{ $v('informed_consent') === 'lengkap' ? 'checked' : '' }} class="rounded border-slate-300">
                            <label for="informed_consent" class="text-sm">Informed Consent Lengkap</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="administrasi" value="">
                            <input type="checkbox" id="administrasi" name="administrasi" value="lengkap"
                                {{ $v('administrasi') === 'lengkap' ? 'checked' : '' }} class="rounded border-slate-300">
                            <label for="administrasi" class="text-sm">Administrasi Lengkap</label>
                        </div>
                    </div>
                    @error('informed_consent') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @error('administrasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </fieldset>
    </x-emr-split-layout>

    <script>
        function cekSelisihBb() {
            var bb = parseFloat(document.querySelector('[name="berat_badan"]').value);
            var kering = parseFloat(document.querySelector('[name="bb_kering"]').value);
            var box = document.getElementById('peringatan-bb');
            if (isNaN(bb) || isNaN(kering)) { box.style.display = 'none'; return; }
            var selisih = bb - kering;
            if (selisih > 3 || selisih < 0) {
                box.textContent = 'Peringatan: ' + (selisih > 3 ? 'Gain BB' : 'Susut BB') + ' ' + selisih.toFixed(1) + ' kg — periksa pompa & akses vaskuler / status hidrasi.';
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
            }
        }
    </script>
@endsection
