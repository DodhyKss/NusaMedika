@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Permintaan Pelayanan Kerohanian';
        $subtitleForm = 'Pengajuan tenaga klinik unit pelayanan agar petugas kerohanian datang ke pasien.';
        $routeName = null;
        $routeUrl = url('emr/form/permintaan_kerohanian');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'nama_rohani' => 'Pemohon',
            'jk_rohani' => 'JK',
            'agama_rohani' => 'Agama',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Permintaan Kerohanian"
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
                slug="permintaan_kerohanian"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            @include('moduls.EMR.PartialForm.kerohanian_identitas_pasien')

            {{-- ================= PEMOHON ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Saya Yang Bertanggung Jawab Dibawah Ini</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium mb-1">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_rohani" value="{{ $v('nama_rohani') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('nama_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Umur <span class="text-red-500">*</span></label>
                        <input type="text" name="umur_rohani" value="{{ $v('umur_rohani') }}" maxlength="30" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. 35 th">
                        @error('umur_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="jk_rohani" class="w-full border rounded-sm px-3 py-2 text-sm">
                            {!! \App\Helpers\SelectOption::render('jenis_kelamin', $v('jk_rohani'), '-- Pilih Jenis Kelamin --') !!}
                        </select>
                        @error('jk_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Agama <span class="text-red-500">*</span></label>
                        <select name="agama_rohani" class="w-full border rounded-sm px-3 py-2 text-sm">
                            {!! \App\Helpers\SelectOption::render('agama', $v('agama_rohani'), '-- Pilih Agama --') !!}
                        </select>
                        @error('agama_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <p class="text-xs text-slate-500 italic">Demikian Permohonan Ini Kami Ajukan — data identitas pasien di atas diambil dari master dan tidak diubah lewat formulir ini.</p>

        </fieldset>
    </x-emr-split-layout>
@endsection
