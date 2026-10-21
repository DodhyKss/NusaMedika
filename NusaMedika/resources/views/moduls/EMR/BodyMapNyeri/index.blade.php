@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Body Map Nyeri';
        $subtitleForm = 'Penandaan lokasi nyeri pada gambar tubuh. Gambar disimpan langsung di dalam EMR.';
        $routeName = null;
        $routeUrl = url('emr/form/body_map_nyeri');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $v = fn ($key) => old($key, $emr_data[$key] ?? '');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Body Map Nyeri"
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
                slug="body_map_nyeri"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'sisi_lokasi' => 'Sisi Lokasi',
                    'keterangan_lokasi' => 'Keterangan Lokasi',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= OBSERVASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Observasi</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_body_map" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_body_map" name="tanggal_body_map"
                                   value="{{ $v('tanggal_body_map') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_body_map')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_body_map" class="block font-bold text-slate-800 mb-1.5">
                                Waktu Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_body_map" name="waktu_body_map" step="60"
                                   value="{{ $v('waktu_body_map') ? substr((string) $v('waktu_body_map'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_body_map')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PETA TUBUH ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Penandaan Lokasi Nyeri</span>
                </div>
                <div class="p-5 bg-white">
                    @include('moduls.EMR.PartialForm._body_map_nyeri', [
                        'value' => (string) $v('gambar_lokasi_nyeri'),
                        'idPrefix' => 'bodymap37',
                    ])
                </div>
            </div>

            {{-- ================= KETERANGAN LOKASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Keterangan Lokasi</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <span class="block font-bold text-slate-800 mb-2">
                            Sisi Lokasi Nyeri <span class="text-red-500">*</span>
                        </span>
                        <div class="flex flex-wrap gap-4">
                            @foreach (\App\Helpers\SelectOption::get('sisi_lokasi') as $opsi)
                                <label for="sisi-{{ $opsi['value'] }}" class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" id="sisi-{{ $opsi['value'] }}" name="sisi_lokasi"
                                           value="{{ $opsi['value'] }}"
                                           @checked((string) $v('sisi_lokasi') === $opsi['value'])>
                                    <span class="text-slate-700">{{ $opsi['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('sisi_lokasi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="keterangan_lokasi" class="block font-bold text-slate-800 mb-1.5">
                            Keterangan Lokasi Nyeri <span class="text-red-500">*</span>
                        </label>
                        <textarea id="keterangan_lokasi" name="keterangan_lokasi" rows="3" maxlength="500"
                                  placeholder="Contoh: pasien menunjuk lokasi di abdomen kanan bawah"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('keterangan_lokasi') }}</textarea>
                        @error('keterangan_lokasi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="catatan" class="block font-bold text-slate-800 mb-1.5">Keterangan</label>
                        <textarea id="catatan" name="catatan" rows="2" maxlength="1000"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('catatan') }}</textarea>
                        @error('catatan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

@endsection