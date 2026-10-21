@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Bukti Pelayanan Kerohanian';
        $subtitleForm = 'Dokumen bukti petugas kerohanian telah memberikan pelayanan — kapan dan di mana.';
        $routeName = null;
        $routeUrl = url('emr/form/bukti_kerohanian');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_rohani' => 'Tanggal',
            'hari_rohani' => 'Hari',
            'tempat_rohani' => 'Tempat',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Bukti Kerohanian"
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
                slug="bukti_kerohanian"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            @include('moduls.EMR.PartialForm.kerohanian_identitas_pasien')

            {{-- ================= ANGGOTA KEROHANIAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-violet-50 border-b border-slate-200">
                    <span class="text-violet-700 font-medium text-[15px]">Saya Anggota Kerohanian</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium mb-1">Nama <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_anggota_rohani" value="{{ $v('nama_anggota_rohani') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('nama_anggota_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Umur <span class="text-red-500">*</span></label>
                        <input type="text" name="umur_anggota_rohani" value="{{ $v('umur_anggota_rohani') }}" maxlength="30" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. 35 th">
                        @error('umur_anggota_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="jk_anggota_rohani" class="w-full border rounded-sm px-3 py-2 text-sm">
                            {!! \App\Helpers\SelectOption::render('jenis_kelamin', $v('jk_anggota_rohani'), '-- Pilih Jenis Kelamin --') !!}
                        </select>
                        @error('jk_anggota_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Agama <span class="text-red-500">*</span></label>
                        <select name="agama_anggota_rohani" class="w-full border rounded-sm px-3 py-2 text-sm">
                            {!! \App\Helpers\SelectOption::render('agama', $v('agama_anggota_rohani'), '-- Pilih Agama --') !!}
                        </select>
                        @error('agama_anggota_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PADA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Pada</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Hari <span class="text-red-500">*</span></label>
                        <select id="hari_rohani" name="hari_rohani" class="w-full border rounded-sm px-3 py-2 text-sm">
                            {!! \App\Helpers\SelectOption::render('hari_kerohanian', $v('hari_rohani'), '-- Pilih Hari --') !!}
                        </select>
                        @error('hari_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" id="tanggal_rohani" name="tanggal_rohani" value="{{ $v('tanggal_rohani') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jam <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_rohani" value="{{ $v('jam_rohani') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('jam_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tempat <span class="text-red-500">*</span></label>
                        <input type="text" name="tempat_rohani" value="{{ $v('tempat_rohani') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. Ruang Mawar">
                        @error('tempat_rohani') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <p class="text-xs text-slate-500 italic">Dengan Ini Telah Memberikan Pelayanan Kerohanian kepada pasien di atas.</p>

        </fieldset>

        <script>
            (function () {
                // Selaraskan pilihan Hari dengan Tanggal — server juga menurunkan
                // `hari_rohani` dari `tanggal_rohani` saat disimpan.
                var HARI = ['MINGGU', 'SENIN', 'SELASA', 'RABU', 'KAMIS', 'JUMAT', 'SABTU'];
                var tgl = document.getElementById('tanggal_rohani');
                var hari = document.getElementById('hari_rohani');
                if (tgl && hari) {
                    tgl.addEventListener('change', function () {
                        if (!this.value) { return; }
                        var d = new Date(this.value + 'T00:00:00');
                        if (!isNaN(d.getTime())) { hari.value = HARI[d.getDay()]; }
                    });
                }
            })();
        </script>
    </x-emr-split-layout>
@endsection
