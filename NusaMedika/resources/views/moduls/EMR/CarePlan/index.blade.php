@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Care Plan';
        $subtitleForm = 'Rencana asuhan tertulis: tempat pertemuan, intervensi, dan target terukur yang dapat dievaluasi ulang.';
        $routeName = null;
        $routeUrl = url('emr/form/care_plan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $v = fn($key) => old($key, $emr_data[$key] ?? '');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Care Plan"
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
                slug="care_plan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'tanggal_care_plan' => 'Tanggal Care Plan',
                    'tempat_pertemuan' => 'Tempat Pertemuan',
                    'perkiraan_lama_rawat' => 'Perkiraan Lama Rawat',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS & WAKTU ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas &amp; Waktu Pertemuan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_care_plan" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Care Plan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_care_plan" name="tanggal_care_plan"
                                   value="{{ $v('tanggal_care_plan') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_care_plan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="tempat_pertemuan" class="block font-bold text-slate-800 mb-1.5">
                                Tempat Pertemuan <span class="text-red-500">*</span>
                            </label>
                            <select id="tempat_pertemuan" name="tempat_pertemuan"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('tempat_pertemuan', $v('tempat_pertemuan'), '-- Pilih Tempat Pertemuan --') !!}
                            </select>
                            @error('tempat_pertemuan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= INTERVENSI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Intervensi</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <label for="intervensi_non_farmakologis" class="block font-bold text-slate-800 mb-1.5">
                            Intervensi Non Farmakologis <span class="text-red-500">*</span>
                        </label>
                        <textarea id="intervensi_non_farmakologis" name="intervensi_non_farmakologis" rows="4"
                                  placeholder="Contoh: diet rendah garam, latihan napas, mobilisasi bertahap, edukasi pasien dan keluarga"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('intervensi_non_farmakologis') }}</textarea>
                        @error('intervensi_non_farmakologis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="intervensi_farmakologis" class="block font-bold text-slate-800 mb-1.5">
                            Intervensi Farmakologis <span class="text-red-500">*</span>
                        </label>
                        <textarea id="intervensi_farmakologis" name="intervensi_farmakologis" rows="4"
                                  placeholder="Contoh: antibiotik, dosis, rute pemberian, dan durasi terapi"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('intervensi_farmakologis') }}</textarea>
                        @error('intervensi_farmakologis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= TARGET TERUKUR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Target Terukur</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="perkiraan_lama_rawat" class="block font-bold text-slate-800 mb-1.5">
                                Perkiraan Lama Rawat (Hari) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="perkiraan_lama_rawat" name="perkiraan_lama_rawat"
                                   min="0" max="100" step="1"
                                   value="{{ $v('perkiraan_lama_rawat') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('perkiraan_lama_rawat')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <div class="mt-3 px-3 py-2 bg-slate-50 border border-slate-200 rounded">
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">Perkiraan Tanggal Pulang</div>
                                <div id="perkiraan_pulang" class="text-sm font-semibold text-slate-700">-</div>
                            </div>
                        </div>
                        <div>
                            <label for="kriteria_pemulangan" class="block font-bold text-slate-800 mb-1.5">
                                Kriteria Pemulangan Pasien <span class="text-red-500">*</span>
                            </label>
                            <textarea id="kriteria_pemulangan" name="kriteria_pemulangan" rows="4"
                                      placeholder="Contoh: bebas demam selama 24 jam, mampu minum, skor nyeri kurang dari 4"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('kriteria_pemulangan') }}</textarea>
                            @error('kriteria_pemulangan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label for="target_perawatan" class="block font-bold text-slate-800 mb-1.5">
                            Target Perawatan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="target_perawatan" name="target_perawatan" rows="3"
                                  placeholder="Target yang terukur dan bisa dievaluasi ulang, contoh: tekanan darah < 140/90 mmHg pada hari ke-3"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('target_perawatan') }}</textarea>
                        @error('target_perawatan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= CATATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Catatan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <label for="catatan" class="block font-bold text-slate-800 mb-1.5">Catatan Tambahan</label>
                        <textarea id="catatan" name="catatan" rows="2"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('catatan') }}</textarea>
                        @error('catatan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Perkiraan tanggal pulang hanya tampilan, tidak disimpan sebagai
            // variabel EMR, hanya Field "perkiraan_lama_rawat" yang tersimpan.
            var tanggal = document.getElementByById('tanggal_care_plan');
            var lama = document.getElementByById('perkiraan_lama_rawat');
            var out = document.getElementById('perkiraan_pulang');

            function syncPerkiraan() {
                if (!out) return;
                var tgl = tanggal ? tanggal.value : '';
                var hari = parseInt(lama ? lama.value : '', 10);
                if (!tgl || isNaN(hari)) {
                    out.textContent = '-';
                    return;
                }
                var hasil = new Date(tgl + 'T00:00:00');
                hasil.setDate(hasil.getDate() + hari);
                out.textContent = hasil.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
            }

            [tanggal, lama].forEach(function (el) {
                if (el) el.addEventListener('input', syncPerkiraan);
                if (el) el.addEventListener('change', syncPerkiraan);
            });
            syncPerkiraan();
        });
    </script>

@endsection