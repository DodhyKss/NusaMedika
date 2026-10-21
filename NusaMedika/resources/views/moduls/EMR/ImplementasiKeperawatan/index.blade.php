@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Form Implementasi Keperawatan';
        $subtitleForm = 'Catat implementasi asuhan keperawatan yang diberikan kepada pasien beserta waktu dan responnya.';
        $routeName = null;
        $routeUrl = url('emr/form/implementasi_keperawatan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Implementasi"
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
                slug="implementasi_keperawatan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="['nama_implementasi' => 'Implementasi', 'waktu_implementasi' => 'Jam', 'respon_implementasi' => 'Respon']"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Implementasi Keperawatan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    {{-- Master Implementasi --}}
                    <div>
                        <label for="implementasi_id" class="block font-bold text-slate-800 mb-1.5">
                            Implementasi <span class="text-red-500">*</span>
                        </label>
                        <select id="implementasi_id" name="implementasi_id"
                                class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            <option value="">-- Pilih Implementasi --</option>
                            @foreach ($implementasiList as $impl)
                                <option value="{{ $impl->implementasi_id }}"
                                        data-nama="{{ $impl->nama_implementasi }}"
                                        @selected((int) old('implementasi_id', $emr_data['implementasi_id'] ?? 0) === (int) $impl->implementasi_id)>
                                    {{ $impl->nama_implementasi }} ({{ $impl->kode_implementasi }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-slate-400">Daftar diambil dari Master Implementasi.</p>
                        @error('implementasi_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal & jam: wajib diisi manual --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_implementasi" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Implementasi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_implementasi" name="tanggal_implementasi"
                                   value="{{ old('tanggal_implementasi', $emr_data['tanggal_implementasi'] ?? '') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_implementasi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_implementasi" class="block font-bold text-slate-800 mb-1.5">
                                Jam Implementasi <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_implementasi" name="waktu_implementasi" step="60"
                                   value="{{ old('waktu_implementasi', substr((string) ($emr_data['waktu_implementasi'] ?? ''), 0, 5)) }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_implementasi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <label for="keterangan_implementasi" class="block font-bold text-slate-800 mb-1.5">Keterangan</label>
                        <textarea id="keterangan_implementasi" name="keterangan_implementasi" rows="3"
                                  placeholder="Uraikan tindakan yang diberikan, dosis, alat yang dipakai, dan selainnya"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('keterangan_implementasi', $emr_data['keterangan_implementasi'] ?? '') }}</textarea>
                        @error('keterangan_implementasi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Respon --}}
                    <div>
                        <label for="respon_implementasi" class="block font-bold text-slate-800 mb-1.5">Respon Pasien</label>
                        <textarea id="respon_implementasi" name="respon_implementasi" rows="3"
                                  placeholder="Tuliskan respon pasien terhadap implementasi tersebut"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('respon_implementasi', $emr_data['respon_implementasi'] ?? '') }}</textarea>
                        <p class="mt-1.5 text-xs text-slate-400">Kolom bebas, diisi sesuai respon yang diobservasi.</p>
                        @error('respon_implementasi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nama implementasi: readonly, diisi server dari master --}}
                    <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                        <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-1">Nama Implementasi (dari master)</div>
                        <div id="preview_nama_implementasi" class="text-sm font-semibold text-slate-700">
                            {{ old('nama_implementasi', $emr_data['nama_implementasi'] ?? '-') }}
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

    </x-emr-split-layout>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select2 sudah di-init oleh app.js; cukup sinkronkan preview nama.
            var select = document.getElementById('implementasi_id');
            var preview = document.getElementById('preview_nama_implementasi');
            if (!select || !preview) return;

            function syncPreview() {
                var opt = select.options[select.selectedIndex];
                preview.textContent = (opt && opt.dataset.nama) ? opt.dataset.nama : '-';
            }

            select.addEventListener('change', syncPreview);
            syncPreview();
        });
    </script>

@endsection()