@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Visus Mata MCU';
        $subtitleForm = 'Pengukuran visus Snellen (mata kanan & kiri). Nilai AKG menjadi dasar surat keterangan.';
        $routeName = null;
        $routeUrl = url('emr/form/visus_mata_mcu');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'visus_od_avod' => 'Visus OD',
            'visus_os_avos' => 'Visus OS',
            'buta_warna' => 'Buta Warna',
        ];

        // Blok kolom visus per mata: key => [label, wajib?].
        $blokKanan = [
            'visus_od_avod' => ['Jauh (AVOD)', true],
            'visus_od_c' => ['Dekat (C)', true],
            'visus_od_add' => ['Addition (ADD)', true],
            'visus_od_tod' => ['TOD', false],
            'visus_od_tos' => ['TOS', false],
        ];
        $blokKiri = [
            'visus_os_avos' => ['Jauh (AVOS)', true],
            'visus_os_c' => ['Dekat (C)', true],
            'visus_os_add' => ['Addition (ADD)', true],
            'visus_os_pd' => ['PD', false],
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Visus Mata MCU"
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
                slug="visus_mata_mcu"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= VISUS MATA KANAN (OD) ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Mata Kanan (OD)</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        @foreach ($blokKanan as $kunci => [$label, $wajib])
                            <div>
                                <label class="block text-xs font-medium mb-1">
                                    {{ $label }} @if ($wajib) <span class="text-red-500">*</span> @endif
                                </label>
                                <input type="text" name="{{ $kunci }}" value="{{ $v($kunci) }}"
                                    placeholder="6/6 atau kode" maxlength="10"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error($kunci) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= VISUS MATA KIRI (OS) ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Mata Kiri (OS)</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach ($blokKiri as $kunci => [$label, $wajib])
                            <div>
                                <label class="block text-xs font-medium mb-1">
                                    {{ $label }} @if ($wajib) <span class="text-red-500">*</span> @endif
                                </label>
                                <input type="text" name="{{ $kunci }}" value="{{ $v($kunci) }}"
                                    placeholder="6/6 atau kode" maxlength="10"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error($kunci) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-slate-500 mt-3">
                        Format: pecahan Snellen (mis. <code>6/6</code>) atau kode —
                        {{ implode(', ', $visusTeks) }}.
                    </p>
                </div>
            </div>

            {{-- ================= KONFRONTASI & BUTA WARNA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Konfrontasi &amp; Buta Warna</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tes Konfrontasi <span class="text-red-500">*</span></label>
                        <input type="text" name="tes_konfrontasi" value="{{ $v('tes_konfrontasi') }}"
                            placeholder="Normal / Tidak"
                            class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tes_konfrontasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-2">Buta Warna <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="buta_warna" value="Tidak"
                                    {{ $v('buta_warna') === 'Tidak' ? 'checked' : '' }}> Tidak
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="buta_warna" value="Ishihara Test"
                                    {{ $v('buta_warna') === 'Ishihara Test' ? 'checked' : '' }}> Ishihara Test
                            </label>
                        </div>
                        @error('buta_warna') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tolak (wajib bila visus &lt; 6/60)</label>
                        <input type="text" name="tolak" value="{{ $v('tolak') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tolak') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>
@endsection
