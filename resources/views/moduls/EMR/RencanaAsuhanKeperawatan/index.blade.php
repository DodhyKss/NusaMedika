@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Rencana Asuhan Keperawatan';
        $subtitleForm = 'Care Plan SDKI: diagnosa keperawatan, tujuan dan kriteria hasil, intervensi, serta indikasi/objektif pendukung.';
        $routeName = null;
        $routeUrl = url('emr/form/rencana_asuhan_keperawatan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Rencana Asuhan"
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
                slug="rencana_asuhan_keperawatan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'nama_diagnosa_keperawatan' => 'Diagnosa Keperawatan',
                    'tujuan_keperawatan' => 'Tujuan',
                    'satuan_waktu_tujuan' => 'Satuan',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= DIAGNOSA KEPERAWATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Diagnosa Keperawatan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <label for="diagnosa_keperawatan_id" class="block font-bold text-slate-800 mb-1.5">
                            Diagnosa Keperawatan <span class="text-red-500">*</span>
                        </label>
                        <select id="diagnosa_keperawatan_id" name="diagnosa_keperawatan_id"
                                class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            <option value="">-- Pilih Diagnosa --</option>
                            @foreach ($icdList as $icd)
                                <option value="{{ $icd->icd_id }}" @selected((string) $v('diagnosa_keperawatan_id') === (string) $icd->icd_id)>
                                    {{ $icd->kode_diagnosa }} - {{ $icd->nama_diagnosa }}
                                </option>
                            @endforeach
                        </select>
                        @error('diagnosa_keperawatan_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama_diagnosa_keperawatan" class="block font-bold text-slate-800 mb-1.5">
                            Nama Diagnosa (snapshot)
                        </label>
                        <input type="text" id="nama_diagnosa_keperawatan" name="nama_diagnosa_keperawatan"
                               value="{{ $v('nama_diagnosa_keperawatan') }}" readonly
                               class="w-full border border-slate-200 rounded px-3 py-2.5 bg-slate-100 text-slate-500 outline-none cursor-not-allowed">
                        <p class="mt-1 text-xs text-slate-400">Diisi otomatis oleh server dari master diagnosa yang dipilih.</p>
                    </div>
                </div>
            </div>

            {{-- ================= TUJUAN & KRITERIA HASIL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tujuan &amp; Kriteria Hasil</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="tujuan_keperawatan" class="block font-bold text-slate-800 mb-1.5">
                                Durasi Tujuan <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="tujuan_keperawatan" name="tujuan_keperawatan"
                                   min="1" step="1"
                                   value="{{ $v('tujuan_keperawatan') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            <p class="mt-1 text-xs text-slate-400">
                                Setelah dilakukan tindakan keperawatan selama ... (angka durasi).
                            </p>
                            @error('tujuan_keperawatan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="satuan_waktu_tujuan" class="block font-bold text-slate-800 mb-1.5">
                                Satuan Waktu <span class="text-red-500">*</span>
                            </label>
                            <select id="satuan_waktu_tujuan" name="satuan_waktu_tujuan"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('satuan_waktu_tujuan', $v('satuan_waktu_tujuan'), '-- Pilih Satuan --') !!}
                            </select>
                            @error('satuan_waktu_tujuan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="kriteria_hasil" class="block font-bold text-slate-800 mb-1.5">
                            Kriteria Hasil Keperawatan
                        </label>
                        <textarea id="kriteria_hasil" name="kriteria_hasil" rows="3"
                                  placeholder="Contoh: Ansas 0; Ronki -/+ 2; Anasarca 0"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('kriteria_hasil') }}</textarea>
                        @error('kriteria_hasil')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= INTERVENSI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Intervensi Keperawatan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <label for="intervensi_keperawatan" class="block font-bold text-slate-800 mb-1.5">
                            Intervensi <span class="text-red-500">*</span>
                        </label>
                        <textarea id="intervensi_keperawatan" name="intervensi_keperawatan" rows="4"
                                  placeholder="Tuliskan daftar intervensi yang akan dilaksanakan"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('intervensi_keperawatan') }}</textarea>
                        @error('intervensi_keperawatan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="indikasi_keperawatan" class="block font-bold text-slate-800 mb-1.5">Indikasi</label>
                            <textarea id="indikasi_keperawatan" name="indikasi_keperawatan" rows="4"
                                      placeholder="Indikasi / alasan diagnose keperawatan"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('indikasi_keperawatan') }}</textarea>
                            @error('indikasi_keperawatan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="objektif_keperawatan" class="block font-bold text-slate-800 mb-1.5">Objektif</label>
                            <textarea id="objektif_keperawatan" name="objektif_keperawatan" rows="4"
                                      placeholder="Temuan objektif yang mendasarai diagnosa"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('objektif_keperawatan') }}</textarea>
                            @error('objektif_keperawatan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="catatan_rencana" class="block font-bold text-slate-800 mb-1.5">Catatan Rencana</label>
                        <textarea id="catatan_rencana" name="catatan_rencana" rows="3"
                                  placeholder="Catatan tambahan rencana asuhan"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('catatan_rencana') }}</textarea>
                        @error('catatan_rencana')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

@endsection
