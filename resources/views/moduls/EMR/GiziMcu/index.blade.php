@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Gizi MCU';
        $subtitleForm = 'Penilaian status gizi oleh dokter SpGizi. BMI dihitung otomatis dari berat & tinggi badan.';
        $routeName = null;
        $routeUrl = url('emr/form/gizi_mcu');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'bmi' => 'BMI',
            'status_gizi' => 'Status Gizi',
            'diagnosa' => 'Diagnosa',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Gizi MCU"
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
                slug="gizi_mcu"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= ANTHROPOMETRI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Anthropometri</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tinggi Badan (cm) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="tinggi_badan" value="{{ $v('tinggi_badan') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @error('tinggi_badan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Berat Badan (kg) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="berat_badan" value="{{ $v('berat_badan') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @error('berat_badan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lingkar Lengan Atas (cm) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="lingkar_lengan_atas" value="{{ $v('lingkar_lengan_atas') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @error('lingkar_lengan_atas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">BMI dihitung otomatis saat disimpan — tidak perlu diisi manual.</p>
                </div>
            </div>

            {{-- ================= STATUS GIZI & POLA DIET ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Status Gizi &amp; Pola Diet</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Status Gizi <span class="text-red-500">*</span></label>
                        <select name="status_gizi" class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            <option value="">-- Pilih status gizi --</option>
                            @foreach ($statusGiziOpsi as $opsi)
                                <option value="{{ $opsi }}" {{ $v('status_gizi') === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                            @endforeach
                        </select>
                        @error('status_gizi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Pola Diet <span class="text-red-500">*</span></label>
                        <textarea name="pola_diet" rows="2"
                            class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('pola_diet') }}</textarea>
                        @error('pola_diet') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Risiko Gizi <span class="text-red-500">*</span></label>
                        <textarea name="risiko_gizi" rows="2"
                            class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('risiko_gizi') }}</textarea>
                        @error('risiko_gizi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= DIAGNOSA & REKOMENDASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Diagnosa &amp; Rekomendasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Diagnosa <span class="text-red-500">*</span></label>
                        <textarea name="diagnosa" rows="3"
                            class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('diagnosa') }}</textarea>
                        @error('diagnosa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Rekomendasi <span class="text-red-500">*</span></label>
                        <textarea name="rekomendasi" rows="3"
                            class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('rekomendasi') }}</textarea>
                        @error('rekomendasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>
@endsection
