@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Rekam Medis Rujukan';
        $subtitleForm = 'Surat pengantar rujukan keluar rumah sakit.';
        $routeName = null;
        $routeUrl = url('emr/form/rekam_medis_rujukan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tingkat_urgensi' => 'Tingkat Urgensi',
            'kebutuhan_kamar' => 'Kebutuhan Kamar',
        ];

        $jawaban = ['Ya', 'Tidak'];
        $sel = function ($name, $list) use ($v) {
            $html = '<select name="'.$name.'" class="w-full border rounded-sm px-2 py-1.5 text-sm"><option value="">-- Pilih --</option>';
            foreach ($list as $opt) {
                $attr = $v($name) === $opt ? ' selected' : '';
                $html .= '<option value="'.$opt.'"'.$attr.'>'.$opt.'</option>';
            }
            return $html.'</select>';
        };
        $selAssoc = function ($name, $list) use ($v) {
            $html = '<select name="'.$name.'" class="w-full border rounded-sm px-2 py-1.5 text-sm"><option value="">-- Pilih --</option>';
            foreach ($list as $opt) {
                $val = $opt['value'];
                $attr = $v($name) === $val ? ' selected' : '';
                $html .= '<option value="'.$val.'"'.$attr.'>'.($opt['label'] ?? $val).'</option>';
            }
            return $html.'</select>';
        };
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Rekam Medis Rujukan"
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
                slug="rekam_medis_rujukan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            @include('moduls.EMR.PartialForm.pasien_identitas_rujukan')

            {{-- ================= SURAT PENGANTAR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Surat Pengantar</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Pengantar <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pengantar" value="{{ $v('tanggal_pengantar') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_pengantar') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Rencana Rawat Inap</label>
                        <input type="date" name="tgl_rencana_ranap" value="{{ $v('tgl_rencana_ranap') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tgl_rencana_ranap') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tingkat Urgensi Rujukan <span class="text-red-500">*</span></label>
                        {!! $selAssoc('tingkat_urgensi', $opsiUrgensi) !!}
                        @error('tingkat_urgensi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Indikasi Rawat Inap <span class="text-red-500">*</span></label>
                        {!! $selAssoc('indikasi_rawat_inap', $opsiIndikasi) !!}
                        @error('indikasi_rawat_inap') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Dirujuk Ke</label>
                        <input type="text" name="dirujuk_ke" value="{{ $v('dirujuk_ke') }}" maxlength="100" placeholder="Faskes / unit tujuan rujukan" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('dirujuk_ke') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-select_dokter name="dokter_rawat" id="dokter_rawat" label="Dokter Rawat Pengirim" :selected="$v('dokter_rawat')" :required="true" />
                    </div>
                </div>
            </div>

            {{-- ================= DIAGNOSIS, RENCANA & TERAPI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Diagnosis, Rencana Pemeriksaan & Terapi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Diagnosis Medis <span class="text-red-500">*</span></label>
                        <textarea name="diagnosis" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Di-prefill dari diagnosa terakhir (boleh diedit)">{{ $v('diagnosis') }}</textarea>
                        @error('diagnosis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Rencana Pemeriksaan Penunjang <span class="text-red-500">*</span></label>
                        <textarea name="rencana_pemeriksaan_penunjang" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('rencana_pemeriksaan_penunjang') }}</textarea>
                        @error('rencana_pemeriksaan_penunjang') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Terapi Perawatan <span class="text-red-500">*</span></label>
                        <textarea name="terapi_keperawatan" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Farmakologis / Non Farmakologis + isi terapi">{{ $v('terapi_keperawatan') }}</textarea>
                        @error('terapi_keperawatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Alasan / Keterangan</label>
                        <textarea name="alasan" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('alasan') }}</textarea>
                        @error('alasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= KEBUTUHAN TINGKAT PELAYANAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Kebutuhan Tingkat Pelayanan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Kebutuhan Pelayanan <span class="text-red-500">*</span></label>
                        {!! $selAssoc('kebutuhan_pelayanan', $opsiPelayanan) !!}
                        @error('kebutuhan_pelayanan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Kebutuhan Kamar <span class="text-red-500">*</span></label>
                        {!! $selAssoc('kebutuhan_kamar', $opsiKamar) !!}
                        @error('kebutuhan_kamar') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= DATA PENDAMPING PASIEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-indigo-50 border-b border-slate-200">
                    <span class="text-indigo-700 font-medium text-[15px]">Data Pendamping Pasien</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Screening COVID <span class="text-red-500">*</span></label>
                        {!! $sel('screening_covid', $jawaban) !!}
                        @error('screening_covid') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">EWS <span class="text-red-500">*</span></label>
                        {!! $sel('ews', $opsiEws) !!}
                        @error('ews') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Oksigen <span class="text-red-500">*</span></label>
                        {!! $sel('oksigen', $jawaban) !!}
                        @error('oksigen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Ventilator <span class="text-red-500">*</span></label>
                        {!! $sel('ventilator', $jawaban) !!}
                        @error('ventilator') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Monitor <span class="text-red-500">*</span></label>
                        {!! $sel('monitor', $jawaban) !!}
                        @error('monitor') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Syringe Pump <span class="text-red-500">*</span></label>
                        {!! $sel('syringe_pump', $jawaban) !!}
                        @error('syringe_pump') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium mb-1">Kriteria COVID</label>
                        <textarea id="kriteria_covid" name="kriteria_covid" rows="2" maxlength="1000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Wajib diisi bila screening COVID = Ya">{{ $v('kriteria_covid') }}</textarea>
                        @error('kriteria_covid') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium mb-1">No. Handphone <span class="text-red-500">*</span></label>
                        <input type="text" name="handphone" value="{{ $v('handphone') }}" maxlength="13" class="w-full md:max-w-xs border rounded-sm px-3 py-2 text-sm">
                        @error('handphone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function cek58Rujukan() {
                    var sc = document.querySelector('select[name="screening_covid"]');
                    var box = document.getElementById('kriteria_covid');
                    if (!sc || !box) { return; }
                    // Tampil/sembunyi pakai style.display (bukan class hidden —
                    // Tailwind v4 menempatkan .hidden sebelum utility display).
                    box.style.display = sc.value === 'Ya' ? '' : 'none';
                }

                var scSelect = document.querySelector('select[name="screening_covid"]');
                if (scSelect) { scSelect.addEventListener('change', cek58Rujukan); }
                cek58Rujukan();
            })();
        </script>
    </x-emr-split-layout>
@endsection
