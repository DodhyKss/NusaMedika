@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Riwayat Keluarga';
        $subtitleForm = 'Anamnesis keluarga: penyakit yang ada di keluarga, hubungan anggota keluarga, dan catatan tambahan.';
        $routeName = null;
        $routeUrl = url('emr/form/riwayat_keluarga');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');
        $terpilih = array_map('strval', json_decode((string) old('penyakit_keluarga', $emr_data['penyakit_keluarga'] ?? ''), true) ?: []);

        $headers = [
            'hubungan_keluarga' => 'Hubungan Keluarga',
            'catatan_keluarga' => 'Catatan',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Keluarga"
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
                slug="riwayat_keluarga"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= PENYAKIT KELUARGA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Penyakit yang Ada di Keluarga</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach (\App\Helpers\SelectOption::get('penyakit_keluarga') as $opt)
                            <?php $id = 'pk_'.md5($opt['value']); ?>
                            <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" id="{{ $id }}" name="penyakit_keluarga[]"
                                       value="{{ $opt['value'] }}"
                                       @checked(in_array((string) $opt['value'], $terpilih, true))
                                       class="rounded text-cyan-600 focus:ring-cyan-500">
                                <span class="text-slate-700">{{ $opt['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('penyakit_keluarga')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-slate-400">
                        Centang minimal satu. Bila memilih Lainnya, rincikan pada kolom Catatan Keluarga.
                    </p>
                </div>
            </div>

            {{-- ================= HUBUNGAN KELUARGA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Anggota Keluarga Rujukan</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label for="hubungan_keluarga" class="block font-bold text-slate-800 mb-1.5">
                        Hubungan / Nama Anggota Keluarga <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="hubungan_keluarga" name="hubungan_keluarga" maxlength="100"
                           value="{{ $v('hubungan_keluarga') }}"
                           list="daftar-hubungan-keluarga"
                           placeholder="Contoh: Ayah (Bapak Sutrisno, 62 tahun)"
                           class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    <datalist id="daftar-hubungan-keluarga">
                        @foreach (\App\Helpers\SelectOption::get('hubungan_keluarga') as $opt)
                            <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                        @endforeach
                    </datalist>
                    @error('hubungan_keluarga')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- ================= CATATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Catatan Keluarga</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label for="catatan_keluarga" class="block font-bold text-slate-800 mb-1.5">Catatan</label>
                    <textarea id="catatan_keluarga" name="catatan_keluarga" rows="4" maxlength="1000"
                              placeholder="Catatan tambahan tentang riwayat keluarga"
                              class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('catatan_keluarga') }}</textarea>
                    @error('catatan_keluarga')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

@endsection
