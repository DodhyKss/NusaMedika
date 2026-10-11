@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Catatan Keperawatan Rawat Jalan';
        $subtitleForm = 'Catatan SOAP ringkas per kunjungan rawat jalan: keluhan, kesadaran, tanda vital, dan catatan keperawatan.';
        $routeName = null;
        $routeUrl = url('emr/form/catatan_keperawatan_rajal');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_catatan' => 'Tanggal Catatan',
            'kesadaran' => 'Kesadaran',
            'catatan_keperawatan' => 'Catatan',
        ];

        $vitalFields = [
            'td_sistolik' => ['label' => 'TD Sistolik', 'min' => 40, 'max' => 300, 'step' => 1],
            'td_diastolik' => ['label' => 'TD Diastolik', 'min' => 20, 'max' => 200, 'step' => 1],
            'nadi' => ['label' => 'Nadi (/menit)', 'min' => 20, 'max' => 250, 'step' => 1],
            'pernapasan' => ['label' => 'Pernapasan (/menit)', 'min' => 4, 'max' => 80, 'step' => 1],
            'suhu' => ['label' => 'Suhu (C)', 'min' => 30, 'max' => 45, 'step' => 'any'],
            'saturasi' => ['label' => 'Saturasi (%)', 'min' => 50, 'max' => 100, 'step' => 1],
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Catatan Rajal"
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
                slug="catatan_keperawatan_rajal"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= WAKTU KUNJUNGAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Waktu Catatan</span>
                </div>
                <div class="p-5 bg-white grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <label for="tanggal_catatan" class="block font-bold text-slate-800 mb-1.5">
                            Tanggal Catatan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tanggal_catatan" name="tanggal_catatan"
                               value="{{ $v('tanggal_catatan') }}"
                               class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        @error('tanggal_catatan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="waktu_catatan" class="block font-bold text-slate-800 mb-1.5">
                            Waktu Catatan <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="waktu_catatan" name="waktu_catatan" step="60"
                               value="{{ $v('waktu_catatan') ? substr((string) $v('waktu_catatan'), 0, 5) : '' }}"
                               class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        @error('waktu_catatan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= KELUHAN & KESADARAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Keluhan &amp; Kesadaran</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <label for="keluhan" class="block font-bold text-slate-800 mb-1.5">
                            Keluhan Utama <span class="text-red-500">*</span>
                        </label>
                        <textarea id="keluhan" name="keluhan" rows="3" maxlength="1000"
                                  placeholder="Keluhan utama pasien pada kunjungan ini"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('keluhan') }}</textarea>
                        @error('keluhan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Kesadaran <span class="text-red-500">*</span></div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach (\App\Helpers\SelectOption::get('kesadaran') as $opt)
                                <?php $id = 'ksr_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" id="{{ $id }}" name="kesadaran"
                                           value="{{ $opt['value'] }}"
                                           @checked((string) $v('kesadaran') === (string) $opt['value'])
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('kesadaran')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= TANDA VITAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanda Vital</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <p class="mb-3 text-xs text-slate-400">Parameter yang tidak diukur boleh dikosongkan.</p>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @foreach ($vitalFields as $nama => $meta)
                            <div>
                                <label for="{{ $nama }}" class="block font-bold text-slate-800 mb-1.5">{{ $meta['label'] }}</label>
                                <input type="number" id="{{ $nama }}" name="{{ $nama }}"
                                       min="{{ $meta['min'] }}" max="{{ $meta['max'] }}" step="{{ $meta['step'] }}"
                                       value="{{ $v($nama) }}"
                                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                @error($nama)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= CATATAN KEPERAWATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Catatan Keperawatan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <label for="catatan_keperawatan" class="block font-bold text-slate-800 mb-1.5">
                            Catatan Keperawatan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="catatan_keperawatan" name="catatan_keperawatan" rows="5" maxlength="2000"
                                  placeholder="Catatan keperawatan pada kunjungan ini"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('catatan_keperawatan') }}</textarea>
                        @error('catatan_keperawatan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="alergi" class="block font-bold text-slate-800 mb-1.5">Alergi</label>
                        <textarea id="alergi" name="alergi" rows="2" maxlength="500"
                                  placeholder="Alergi yang diketahui (bila tidak ada tulis Tidak Ada)"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('alergi') }}</textarea>
                        @error('alergi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

@endsection
