@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'SPRI & Program Rujukan Balik';
        $subtitleForm = 'Surat Pernyataan Rawat Inap / Program Rujukan Balik.';
        $routeName = null;
        $routeUrl = url('emr/form/spri_rujukan_balik');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'jenis_formulir' => 'Jenis Formulir',
            'faskes_tujuan' => 'Faskes Tujuan',
            'tanggal_kontrol' => 'Tgl. Kontrol',
        ];

        $sel = function ($name, $list) use ($v) {
            $html = '<select name="'.$name.'" class="w-full border rounded-sm px-2 py-1.5 text-sm"><option value="">-- Pilih --</option>';
            foreach ($list as $opt) {
                $attr = $v($name) === $opt ? ' selected' : '';
                $html .= '<option value="'.$opt.'"'.$attr.'>'.$opt.'</option>';
            }
            return $html.'</select>';
        };
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat SPRI / Rujukan Balik"
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
                slug="spri_rujukan_balik"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            @include('moduls.EMR.PartialForm.pasien_identitas_rujukan')

            {{-- ================= JENIS FORMULIR & NOMOR SEP ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Jenis Formulir & Nomor SEP</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Formulir <span class="text-red-500">*</span></label>
                        {!! $sel('jenis_formulir', $opsiFormulir) !!}
                        @error('jenis_formulir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-no-sep">
                        <label class="block text-xs font-medium mb-1">Nomor SEP <span class="text-red-500">*</span></label>
                        <input type="text" name="no_sep" value="{{ $v('no_sep') }}" maxlength="50" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('no_sep') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Nomor Rujukan</label>
                        <input type="text" name="no_rujukan" value="{{ $v('no_rujukan') }}" maxlength="50" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('no_rujukan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= STATUS KLINIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Status Klinis</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Diagnosis <span class="text-red-500">*</span></label>
                        <textarea name="diagnosis" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Di-prefill dari diagnosa terakhir (boleh diedit)">{{ $v('diagnosis') }}</textarea>
                        @error('diagnosis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Anamnesis <span class="text-red-500">*</span></label>
                        <textarea name="anamnesis" rows="3" maxlength="4000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Di-prefill dari SOAP terakhir (boleh diedit)">{{ $v('anamnesis') }}</textarea>
                        @error('anamnesis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Pemeriksaan Fisik <span class="text-red-500">*</span></label>
                        <textarea name="pemeriksaan_fisik" rows="3" maxlength="4000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Di-prefill dari SOAP terakhir (boleh diedit)">{{ $v('pemeriksaan_fisik') }}</textarea>
                        @error('pemeriksaan_fisik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PERSIAPAN, RENCANA & TERAPI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Persiapan, Rencana Pemeriksaan & Terapi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Persiapan Tindakan <span class="text-red-500">*</span></label>
                        <textarea name="persiapan_tindakan" rows="2" maxlength="1000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('persiapan_tindakan') }}</textarea>
                        @error('persiapan_tindakan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Rencana Pemeriksaan Penunjang <span class="text-red-500">*</span></label>
                        <textarea name="rencana_pemeriksaan_penunjang" rows="2" maxlength="1000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('rencana_pemeriksaan_penunjang') }}</textarea>
                        @error('rencana_pemeriksaan_penunjang') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Terapi & Obat yang Masih Diberikan <span class="text-red-500">*</span></label>
                        <textarea name="terapi_obat" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('terapi_obat') }}</textarea>
                        @error('terapi_obat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= RUJUKAN BALIK ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-indigo-50 border-b border-slate-200">
                    <span class="text-indigo-700 font-medium text-[15px]">Rujukan Balik</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Alasan Rujukan Balik <span class="text-red-500">*</span></label>
                        <textarea name="alasan" rows="2" maxlength="1000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('alasan') }}</textarea>
                        @error('alasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Anjuran & Instruksi <span class="text-red-500">*</span></label>
                        <textarea name="anjuran" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('anjuran') }}</textarea>
                        @error('anjuran') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div id="blok-tanggal-kontrol">
                            <label class="block text-xs font-medium mb-1">Tanggal Kontrol Rujukan Balik <span class="text-red-500">*</span> <span class="font-normal text-slate-400">(PRB)</span></label>
                            <input type="date" name="tanggal_kontrol" value="{{ $v('tanggal_kontrol') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_kontrol') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Faskes Tujuan Rujukan Balik <span class="text-red-500">*</span></label>
                            <input type="text" name="faskes_tujuan" value="{{ $v('faskes_tujuan') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('faskes_tujuan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <x-select_dokter name="dokter_pengarah_id" id="dokter_pengarah_id" label="Dokter Pengarah" :selected="$v('dokter_pengarah_id')" :required="true" />
                        </div>
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function cek128Spri() {
                    var jenis = document.querySelector('select[name="jenis_formulir"]');
                    var jenisVal = jenis ? jenis.value : '';

                    // Nomor SEP hanya untuk SPRI.
                    var blokSep = document.getElementById('blok-no-sep');
                    if (blokSep) {
                        blokSep.style.display = jenisVal === 'SPRI' ? '' : 'none';
                    }

                    // Tanggal kontrol hanya untuk Program Rujukan Balik.
                    var blokKontrol = document.getElementById('blok-tanggal-kontrol');
                    if (blokKontrol) {
                        blokKontrol.style.display = jenisVal === 'PROGRAM RUJUKAN BALIK' ? '' : 'none';
                    }
                }

                var jenisSelect = document.querySelector('select[name="jenis_formulir"]');
                if (jenisSelect) { jenisSelect.addEventListener('change', cek128Spri); }
                cek128Spri();
            })();
        </script>
    </x-emr-split-layout>
@endsection
