@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Asesmen Pra Bedah';
        $subtitleForm = 'Asesmen pra-operasi — riwayat, rencana tindakan, edukasi & prognosis.';
        $routeName = null;
        $routeUrl = url('emr/form/asesmen_pra_bedah');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_asesmen' => 'Tanggal',
            'jam_asesmen' => 'Jam',
            'jenis_operasi' => 'Jenis Operasi',
        ];

        // Label item edukasi masih provisional (KONSEP §3 — ganti bersama
        // tim anestesi bila sudah ada label final).
        $itemEdukasi = [
            'kebutuhan_edukasi_persiapan_mentas' => 'Persiapan Mentas',
            'kebutuhan_edukasi_persiapan_lensa' => 'Persiapan Lensa',
            'kebutuhan_edukasi_puasa' => 'Puasa',
            'kebutuhan_edukasi_pemberian_obat' => 'Pemberian Obat',
            'kebutuhan_edukasi_lainnya' => 'Lainnya',
        ];

        // KONSEP §3 validasi: jenis_operasi hanya Efektif / Darurat
        // (SelectOption key "jenis_operasi" memuat 3 opsi utk form lain).
        $opsiJenis = array_values(array_filter(
            \App\Helpers\SelectOption::get('jenis_operasi'),
            fn ($o) => in_array($o['value'], ['Efektif', 'Darurat'], true)
        ));
        $opsiPrognosis = \App\Helpers\SelectOption::get('prognosis_bedah');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Pra Bedah"
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
                slug="asesmen_pra_bedah"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Asesmen</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Asesmen <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_asesmen" value="{{ $v('tanggal_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Asesmen <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_asesmen" value="{{ $v('jam_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <x-select_dokter
                            name="dokter_penilai_id"
                            id="dokter_penilai_id"
                            label="Dokter Penilai"
                            placeholder="-- Pilih Dokter --"
                            :selected="$v('dokter_penilai_id')"
                            :required="true" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Keluhan <span class="text-red-500">*</span></label>
                        <textarea name="keluhan" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keluhan') }}</textarea>
                        @error('keluhan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Diagnosa Pra Operasi <span class="text-red-500">*</span></label>
                            <textarea name="diagnosa_pra_operasi" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('diagnosa_pra_operasi') }}</textarea>
                            @error('diagnosa_pra_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Temuan Fisik Pra Bedah <span class="text-red-500">*</span></label>
                            <textarea name="temuan_fisik_pra_bedah" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('temuan_fisik_pra_bedah') }}</textarea>
                            @error('temuan_fisik_pra_bedah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Riwayat Alergi <span class="text-red-500">*</span></label>
                        <textarea name="riwayat_alergi" rows="2" placeholder="Tulis &quot;Tidak ada&quot; bila tidak ada alergi." class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('riwayat_alergi') }}</textarea>
                        @error('riwayat_alergi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= RENCANA OPERASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Rencana Tindakan Pra Bedah</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Rencana Operasi <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_rencana_operasi" value="{{ $v('tanggal_rencana_operasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <p class="text-[11px] text-slate-500 mt-1">Minimal hari ini.</p>
                            @error('tanggal_rencana_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Rencana Operasi <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_rencana_operasi" value="{{ $v('jam_rencana_operasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_rencana_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jenis Operasi <span class="text-red-500">*</span></label>
                            <select name="jenis_operasi" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Jenis --</option>
                                @foreach ($opsiJenis as $opsi)
                                    <option value="{{ $opsi['value'] }}" {{ $v('jenis_operasi') === $opsi['value'] ? 'selected' : '' }}>
                                        {{ $opsi['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Rencana Tindakan Operasi <span class="text-red-500">*</span></label>
                        <textarea name="rencana_tindakan_operasi" rows="3" placeholder="Rencana tindakan sesuai diagnosis, termasuk rencana penanggulangan & pencegahan komplikasi." class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('rencana_tindakan_operasi') }}</textarea>
                        @error('rencana_tindakan_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= KEBUTUHAN EDUKASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kebutuhan Edukasi Pra Bedah <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <p class="text-xs text-slate-600">Pilih minimal satu kebutuhan edukasi.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach ($itemEdukasi as $key => $label)
                            <label class="inline-flex items-center gap-2 border border-slate-200 rounded-sm px-3 py-2">
                                <input type="hidden" name="{{ $key }}" value="0">
                                <input type="checkbox" name="{{ $key }}" value="1" {{ $v($key) === '1' ? 'checked' : '' }} class="edukasi-lainnya-trigger" data-key="{{ $key }}">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('kebutuhan_edukasi_persiapan_mentas') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <div id="blok-edukasi-lain" style="{{ $v('kebutuhan_edukasi_lainnya') === '1' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Kebutuhan Edukasi Lainnya <span class="text-red-500">*</span></label>
                        <input type="text" name="kebutuhan_edukasi_lain" value="{{ $v('kebutuhan_edukasi_lain') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('kebutuhan_edukasi_lain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PENUNJANG ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Hasil Penunjang Pra Bedah</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Hasil Laboratorium</label>
                            <textarea name="hasil_laboratorium" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('hasil_laboratorium') }}</textarea>
                            @error('hasil_laboratorium') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Hasil Radiologi</label>
                            <textarea name="hasil_radiologi" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('hasil_radiologi') }}</textarea>
                            @error('hasil_radiologi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Hasil Penunjang Lain</label>
                            <textarea name="hasil_penunjang_lain" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('hasil_penunjang_lain') }}</textarea>
                            @error('hasil_penunjang_lain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= KOMPLIKASI & BACK UP PLAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kemungkinan Komplikasi & Rencana Penanganan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Kemungkinan Komplikasi <span class="text-red-500">*</span></label>
                            <textarea name="kemungkinan_komplikasi" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('kemungkinan_komplikasi') }}</textarea>
                            @error('kemungkinan_komplikasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Rencana Penatalaksanaan Komplikasi <span class="text-red-500">*</span></label>
                            <textarea name="rencana_komplikasi" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('rencana_komplikasi') }}</textarea>
                            @error('rencana_komplikasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Back Up Plan <span class="text-red-500">*</span></label>
                        <p class="text-[11px] text-slate-500 mb-1">Wajib diisi bila ada kemungkinan / rencana komplikasi.</p>
                        <textarea name="back_up_plan" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('back_up_plan') }}</textarea>
                        @error('back_up_plan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PROGNOSIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Prognosis <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <div class="flex flex-wrap gap-5">
                        @foreach ($opsiPrognosis as $opsi)
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="prognosis_dubia_ad_bonam" value="{{ $opsi['value'] }}" {{ $v('prognosis_dubia_ad_bonam') === $opsi['value'] ? 'checked' : '' }}>
                                <span>{{ $opsi['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('prognosis_dubia_ad_bonam') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <div>
                        <label class="block text-xs font-medium mb-1">Catatan Prognosis</label>
                        <textarea name="catatan_prognosis" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('catatan_prognosis') }}</textarea>
                        @error('catatan_prognosis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                // Blok "Kebutuhan Edukasi Lainnya" muncul bila checkbox Lainnya dicentang.
                var blok = document.getElementById('blok-edukasi-lain');
                var cekLainnya = document.querySelector('input.edukasi-lainnya-trigger[data-key="kebutuhan_edukasi_lainnya"]');
                if (blok && cekLainnya) {
                    var sinkron = function () {
                        blok.style.display = cekLainnya.checked ? '' : 'none';
                    };
                    cekLainnya.addEventListener('change', sinkron);
                    sinkron();
                }
            })();
        </script>
    </x-emr-split-layout>
@endsection
