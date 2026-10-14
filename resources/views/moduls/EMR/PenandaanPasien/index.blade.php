@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Penandaan Pasien';
        $subtitleForm = 'Formulir penandaan gelang risiko pasien: alergi, hepatitis, HIV, TB & geriatri.';
        $routeName = null;
        $routeUrl = url('emr/form/penandaan_pasien');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_penandaan' => 'Tanggal',
            'jam_penandaan' => 'Jam',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Penandaan Pasien"
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
                slug="penandaan_pasien"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= WAKTU PENANDAAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Waktu Penandaan</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Penandaan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_penandaan" value="{{ $v('tanggal_penandaan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_penandaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Penandaan <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_penandaan" value="{{ $v('jam_penandaan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_penandaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PENANDA ALERGI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200 flex items-center justify-between gap-3">
                    <span class="text-amber-700 font-medium text-[15px]">Penanda Alergi</span>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="hidden" name="penanda_alergi" value="0">
                        <input type="checkbox" id="chk-alergi" name="penanda_alergi" value="1" {{ $v('penanda_alergi') === '1' ? 'checked' : '' }}>
                        <span>Aktif</span>
                    </label>
                </div>
                @error('penanda_alergi') <p class="text-xs text-red-600 mt-1 mx-4">{{ $message }}</p> @enderror
                <div id="blok-alergi" class="p-5 bg-white text-sm space-y-3" style="display:none">
                    <p class="text-xs text-slate-500">Daftar alergi pasien — <span class="text-red-500">*</span> minimal satu wajib diisi.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @for ($i = 1; $i <= 5; $i++)
                            <div>
                                <label class="block text-xs font-medium mb-1">Alergi {{ $i }}</label>
                                <input type="text" name="alergi_{{ $i }}" value="{{ $v('alergi_'.$i) }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Alergi {{ $i }}...">
                            </div>
                        @endfor
                    </div>
                    @error('alergi_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @error('alergi_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @error('alergi_3') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @error('alergi_4') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @error('alergi_5') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= PENANDA HEPATITIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-purple-50 border-b border-slate-200 flex items-center justify-between gap-3">
                    <span class="text-purple-700 font-medium text-[15px]">Penanda Hepatitis</span>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="hidden" name="penanda_hepatitis" value="0">
                        <input type="checkbox" id="chk-hepatitis" name="penanda_hepatitis" value="1" {{ $v('penanda_hepatitis') === '1' ? 'checked' : '' }}>
                        <span>Aktif</span>
                    </label>
                </div>
                @error('penanda_hepatitis') <p class="text-xs text-red-600 mt-1 mx-4">{{ $message }}</p> @enderror
                <div id="blok-hepatitis" class="p-5 bg-white text-sm" style="display:none">
                    <div class="max-w-xs">
                        <label class="block text-xs font-medium mb-1">Jenis Hepatitis <span class="text-red-500">*</span></label>
                        <select name="jenis_hepatitis" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="B" {{ $v('jenis_hepatitis') === 'B' ? 'selected' : '' }}>B</option>
                            <option value="C" {{ $v('jenis_hepatitis') === 'C' ? 'selected' : '' }}>C</option>
                        </select>
                        @error('jenis_hepatitis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PENANDA HIV / TB ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-rose-50 border-b border-slate-200">
                    <span class="text-rose-700 font-medium text-[15px]">Penanda HIV &amp; Tuberkulosis</span>
                </div>
                <div class="p-5 bg-white text-sm flex flex-wrap gap-x-8 gap-y-3">
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="hidden" name="penanda_hiv" value="0">
                        <input type="checkbox" name="penanda_hiv" value="1" {{ $v('penanda_hiv') === '1' ? 'checked' : '' }}>
                        <span>Penanda HIV</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="hidden" name="penanda_tb" value="0">
                        <input type="checkbox" name="penanda_tb" value="1" {{ $v('penanda_tb') === '1' ? 'checked' : '' }}>
                        <span>Penanda TB</span>
                    </label>
                </div>
                @error('penanda_hiv') <p class="text-xs text-red-600 mt-1 mx-4">{{ $message }}</p> @enderror
                @error('penanda_tb') <p class="text-xs text-red-600 mt-1 mx-4">{{ $message }}</p> @enderror
            </div>

            {{-- ================= PENANDA GERIATRI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-teal-50 border-b border-slate-200 flex items-center justify-between gap-3">
                    <span class="text-teal-700 font-medium text-[15px]">Penanda Geriatri</span>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="hidden" name="penanda_geriatri" value="0">
                        <input type="checkbox" id="chk-geriatri" name="penanda_geriatri" value="1" {{ $v('penanda_geriatri') === '1' ? 'checked' : '' }}>
                        <span>Aktif</span>
                    </label>
                </div>
                @error('penanda_geriatri') <p class="text-xs text-red-600 mt-1 mx-4">{{ $message }}</p> @enderror
                <div id="blok-geriatri" class="p-5 bg-white text-sm" style="display:none">
                    <label class="block text-xs font-medium mb-1">Alasan Penandaan Geriatri <span class="text-red-500">*</span></label>
                    <textarea name="alasan_geriatri" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Alasan penandaan geriatri...">{{ $v('alasan_geriatri') }}</textarea>
                    @error('alasan_geriatri') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function pasang(checkId, blokId) {
                    var chk = document.getElementById(checkId);
                    var blok = document.getElementById(blokId);
                    if (!chk || !blok) { return; }
                    function sync() {
                        blok.style.display = chk.checked ? '' : 'none';
                    }
                    chk.addEventListener('change', sync);
                    sync();
                }
                pasang('chk-alergi', 'blok-alergi');
                pasang('chk-hepatitis', 'blok-hepatitis');
                pasang('chk-geriatri', 'blok-geriatri');
            })();
        </script>
    </x-emr-split-layout>
@endsection
