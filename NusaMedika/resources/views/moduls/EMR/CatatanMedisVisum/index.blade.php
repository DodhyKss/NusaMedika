@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Catatan Medis Visum';
        $subtitleForm = 'Visum et repertum atas permintaan penegak hukum. Khusus IGD, hanya dokter.';
        $routeName = null;
        $routeUrl = url('emr/form/catatan_medis_visum');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $v = fn($key) => old($key, $emr_data[$key] ?? '');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Visum"
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
                slug="catatan_medis_visum"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'nomor_visum' => 'Nomor Visum',
                    'jenis_visum' => 'Jenis Visum',
                    'permintaan_visum' => 'Permintaan Visum',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS VISUM ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Visum</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="tanggal_visum" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Pemeriksaan
                            </label>
                            <input type="date" id="tanggal_visum" name="tanggal_visum" readonly
                                   value="{{ $v('tanggal_visum') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-100 text-slate-500 outline-none cursor-not-allowed">
                            <p class="mt-1 text-xs text-slate-400">Diisi otomatis server.</p>
                        </div>
                        <div>
                            <label for="waktu_visum" class="block font-bold text-slate-800 mb-1.5">
                                Jam Pemeriksaan
                            </label>
                            <input type="time" id="waktu_visum" name="waktu_visum" readonly
                                   value="{{ $v('waktu_visum') ? substr((string) $v('waktu_visum'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-100 text-slate-500 outline-none cursor-not-allowed">
                            <p class="mt-1 text-xs text-slate-400">Diisi otomatis server.</p>
                        </div>
                        <div>
                            <label for="jenis_visum" class="block font-bold text-slate-800 mb-1.5">
                                Jenis Visum <span class="text-red-500">*</span>
                            </label>
                            <select id="jenis_visum" name="jenis_visum"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('jenis_visum', $v('jenis_visum'), '-- Pilih Jenis Visum --') !!}
                            </select>
                            @error('jenis_visum')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nomor_visum" class="block font-bold text-slate-800 mb-1.5">
                                Nomor Visum Et Repertum
                            </label>
                            <input type="text" id="nomor_visum" name="nomor_visum" readonly
                                   value="{{ $v('nomor_visum') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-100 text-slate-500 outline-none cursor-not-allowed">
                            <p class="mt-1 text-xs text-slate-400">Dibbangkitkan server, tidak dapat diubah.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-select_pegawai
                            :selected="$v('dokter_visum')"
                            name="dokter_visum"
                            id="dokter_visum"
                            label="Dokter Pemeriksa"
                            placeholder="-- Pilih Dokter --"
                            :profesiId="1"
                            :required="true" />

                        <div>
                            <label for="permintaan_visum" class="block font-bold text-slate-800 mb-1.5">
                                Permintaan Visum <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="permintaan_visum" name="permintaan_visum"
                                   value="{{ $v('permintaan_visum') }}"
                                   placeholder="Tulis nama instansi pemohon secara lengkap"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                            @error('permintaan_visum')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-400">Tulis nama instansi pemohon secara lengkap.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= BENDA BUKTI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identifikasi Benda Bukti</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <label for="benda_bukti_identifisir" class="block font-bold text-slate-800 mb-1.5">
                            Benda Bukti Telah Diidentifisir Dengan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="benda_bukti_identifisir" name="benda_bukti_identifisir" rows="4"
                                  placeholder="Uraikan benda bukti yang diidentifisir, misalnya ciri-ciri fisik dan cara pengenalannya"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('benda_bukti_identifisir') }}</textarea>
                        @error('benda_bukti_identifisir')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= HASIL & KESIMPULAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Hasil Pemeriksaan &amp; Kesimpulan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <label for="hasil_visum" class="block font-bold text-slate-800 mb-1.5">
                            Hasil Pemeriksaan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="hasil_visum" name="hasil_visum" rows="6"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('hasil_visum') }}</textarea>
                        @error('hasil_visum')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="kesimpulan_visum" class="block font-bold text-slate-800 mb-1.5">
                            Kesimpulan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="kesimpulan_visum" name="kesimpulan_visum" rows="5"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('kesimpulan_visum') }}</textarea>
                        @error('kesimpulan_visum')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="kelainan_sebab" class="block font-bold text-slate-800 mb-1.5">
                                Kelainan Tersebut Disebabkan Oleh Karena <span class="text-red-500">*</span>
                            </label>
                            <textarea id="kelainan_sebab" name="kelainan_sebab" rows="4"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('kelainan_sebab') }}</textarea>
                            @error('kelainan_sebab')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="kelainan_akibat" class="block font-bold text-slate-800 mb-1.5">
                                Kelainan Tersebut Mengakibatkan <span class="text-red-500">*</span>
                            </label>
                            <textarea id="kelainan_akibat" name="kelainan_akibat" rows="4"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('kelainan_akibat') }}</textarea>
                            @error('kelainan_akibat')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <p class="text-xs text-slate-400">
                        Visum merupakan dokumen bermeterai. Nomor yang sudah terbit tidak dapat diubah dan
                        visum hanya dapat dihapus dalam 7 hari pertama sejak dibuat.
                    </p>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

@endsection