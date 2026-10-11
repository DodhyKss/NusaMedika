@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Issues Keperawatan';
        $subtitleForm = 'Daftar masalah keperawatan beserta faktor berhubungan dengan. Maksimal 10 baris.';
        $routeName = null;
        $routeUrl = url('emr/form/issues_keperawatan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $maksBaris = 10;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');
        $headers = ['masalah_keperawatan_1' => 'Masalah Utama', 'faktor_berhubungan_1' => 'Faktor'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Issues"
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
                slug="issues_keperawatan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= DAFTAR MASALAH ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Masalah Keperawatan</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">

                    <p class="text-xs text-slate-500">
                        Isi baris yang dipakai saja. Bila kolom masalah dikosongkan, kolom faktor pada baris
                        yang sama otomatis dibuang saat disimpan.
                    </p>

                    @for ($i = 1; $i <= $maksBaris; $i++)
                        <?php
                            $namaMasalah = 'masalah_keperawatan_'.$i;
                            $namaFaktor = 'faktor_berhubungan_'.$i;
                        ?>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-3 border border-slate-200 rounded-lg bg-slate-50/60">
                            <div class="md:col-span-1 flex items-center justify-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-cyan-600 text-white text-xs font-bold">
                                    {{ $i }}
                                </span>
                            </div>

                            <div class="md:col-span-6">
                                <label for="{{ $namaMasalah }}" class="block font-bold text-slate-800 mb-1.5">
                                    Masalah Keperawatan @if ($i === 1)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                <input type="text" id="{{ $namaMasalah }}" name="{{ $namaMasalah }}"
                                       maxlength="255"
                                       value="{{ $v($namaMasalah) }}"
                                       placeholder="Contoh: Risiko Ganggu Integritas Kulit"
                                       class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                                @error($namaMasalah)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-5">
                                <label for="{{ $namaFaktor }}" class="block font-bold text-slate-800 mb-1.5">
                                    Berhubungan Dengan
                                </label>
                                <input type="text" id="{{ $namaFaktor }}" name="{{ $namaFaktor }}"
                                       maxlength="255"
                                       value="{{ $v($namaFaktor) }}"
                                       placeholder="Contoh: Immobilisasi"
                                       class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                                @error($namaFaktor)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

@endsection
