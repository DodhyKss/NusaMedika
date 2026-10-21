@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Uji Fungsi & Asesment';
        $subtitleForm = 'Lembar asesmen rehabilitasi medik / fisioterapi dengan bentuk SOAP dasar.';
        $routeName = null;
        $routeUrl = url('emr/form/uji_fungsi_asesment');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_pelayanan' => 'Tanggal Pelayanan',
            'diagnosis_fungsi' => 'Diagnosa Fungsi',
            'frekuensi_kunjungan' => 'Frekuensi Kunjungan',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Uji Fungsi"
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
                slug="uji_fungsi_asesment"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= WAKTU PELAYANAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Waktu Pelayanan</span>
                </div>
                <div class="p-5 bg-white grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <label for="tanggal_pelayanan" class="block font-bold text-slate-800 mb-1.5">
                            Tanggal Pelayanan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tanggal_pelayanan" name="tanggal_pelayanan"
                               value="{{ $v('tanggal_pelayanan') }}"
                               class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        @error('tanggal_pelayanan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="waktu_pelayanan" class="block font-bold text-slate-800 mb-1.5">
                            Waktu Pelayanan <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="waktu_pelayanan" name="waktu_pelayanan" step="60"
                               value="{{ $v('waktu_pelayanan') ? substr((string) $v('waktu_pelayanan'), 0, 5) : '' }}"
                               class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        @error('waktu_pelayanan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= SOAP ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Subjective, Objective, Assessment, Plan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <label for="subjective" class="block font-bold text-slate-800 mb-1.5">
                            Subjective <span class="text-red-500">*</span>
                        </label>
                        <textarea id="subjective" name="subjective" rows="5" maxlength="2000"
                                  placeholder="Keluhan dan riwayat yang disampaikan pasien"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('subjective') }}</textarea>
                        <p class="mt-1 text-xs text-slate-400">
                            Diisi otomatis dari Pengkajian Awal Keperawatan terakhir bila tersedia; boleh diubah.
                        </p>
                        @error('subjective')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="objective" class="block font-bold text-slate-800 mb-1.5">
                            Objective <span class="text-red-500">*</span>
                        </label>
                        <textarea id="objective" name="objective" rows="5" maxlength="2000"
                                  placeholder="Hasil pemeriksaan fisik dan temuan objektif"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('objective') }}</textarea>
                        <p class="mt-1 text-xs text-slate-400">
                            Diisi otomatis dari tanda vital Pengkajian Awal terakhir bila tersedia; boleh diubah.
                        </p>
                        @error('objective')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="diagnosis_fungsi" class="block font-bold text-slate-800 mb-1.5">
                                Diagnosa Fungsi <span class="text-red-500">*</span>
                            </label>
                            <textarea id="diagnosis_fungsi" name="diagnosis_fungsi" rows="4" maxlength="1000"
                                      placeholder="Contoh: Gangguan berjalan dan keseimbangan"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('diagnosis_fungsi') }}</textarea>
                            @error('diagnosis_fungsi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="goal" class="block font-bold text-slate-800 mb-1.5">
                                Goal <span class="text-red-500">*</span>
                            </label>
                            <textarea id="goal" name="goal" rows="4" maxlength="1000"
                                      placeholder="Tujuan jangka pendek dan jangka panjang"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('goal') }}</textarea>
                            @error('goal')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="diagnosis_medis" class="block font-bold text-slate-800 mb-1.5">
                            Diagnosa Medis <span class="text-red-500">*</span>
                        </label>
                        <select id="diagnosis_medis" name="diagnosis_medis"
                                class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            <option value="">-- Pilih Diagnosa Medis --</option>
                            @foreach ($icdList as $icd)
                                <option value="{{ $icd->icd_id }}" @selected((string) $v('diagnosis_medis') === (string) $icd->icd_id)>
                                    {{ $icd->kode_diagnosa }} - {{ $icd->nama_diagnosa }}
                                </option>
                            @endforeach
                        </select>
                        @error('diagnosis_medis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="instruksi" class="block font-bold text-slate-800 mb-1.5">
                            Instruksi / Tata Laksana <span class="text-red-500">*</span>
                        </label>
                        <textarea id="instruksi" name="instruksi" rows="5" maxlength="2000"
                                  placeholder="Tata laksana dan tindakan program rehabilitasi"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('instruksi') }}</textarea>
                        @error('instruksi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= EVALUASI & EDUKASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Evaluasi, Edukasi &amp; Anjuran</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <label for="evaluasi" class="block font-bold text-slate-800 mb-1.5">
                            Evaluasi <span class="text-red-500">*</span>
                        </label>
                        <textarea id="evaluasi" name="evaluasi" rows="4" maxlength="2000"
                                  placeholder="Evaluasi perkembangan pasien"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('evaluasi') }}</textarea>
                        @error('evaluasi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="edukasi_anjuran" class="block font-bold text-slate-800 mb-1.5">
                            Edukasi &amp; Anjuran <span class="text-red-500">*</span>
                        </label>
                        <textarea id="edukasi_anjuran" name="edukasi_anjuran" rows="4" maxlength="1000"
                                  placeholder="Edukasi yang diberikan dan anjuran untuk keluarga"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('edukasi_anjuran') }}</textarea>
                        @error('edukasi_anjuran')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="frekuensi_kunjungan" class="block font-bold text-slate-800 mb-1.5">
                            Frekuensi Kunjungan <span class="text-red-500">*</span>
                        </label>
                        <select id="frekuensi_kunjungan" name="frekuensi_kunjungan"
                                class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            {!! \App\Helpers\SelectOption::render('frekuensi_kunjungan_rehab', $v('frekuensi_kunjungan'), '-- Pilih Frekuensi --') !!}
                        </select>
                        @error('frekuensi_kunjungan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

@endsection
