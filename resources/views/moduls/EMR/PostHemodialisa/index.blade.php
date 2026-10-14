@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Post Hemodialisa';
        $subtitleForm = 'Evaluasi setelah sesi hemodialisa — Kt/V, UFG & Convective Volume.';
        $routeName = null;
        $routeUrl = url('emr/form/post_hemodialisa');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');
        $headers = [
            'tanggal_pertemuan' => 'Tanggal',
            'kt_v' => 'Kt/V',
        ];
        $labFields = [
            'hb' => 'Hb (g/dL)', 'ureum' => 'Ureum (mg/dL)', 'creatinine' => 'Creatinine (mg/dL)',
            'kalium' => 'Kalium (mEq/L)', 'sgot' => 'SGOT (U/L)', 'sgpt' => 'SGPT (U/L)',
        ];
        $terapiFields = [
            'transfusi' => 'Transfusi (ml/paket)', 'd40' => 'D 40% (ampul)', 'ca_glukonas' => 'Ca. Gluconas (ampul)',
            'kcl' => 'KCL (vial)', 'renxamin' => 'Renxamin (ml)', 'erythropoietin' => 'Erythropoietin (IU)',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Post Hemodialisa"
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
                slug="post_hemodialisa"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas &amp; Data Fokus</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Pengkajian <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pertemuan" value="{{ $v('tanggal_pertemuan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_pertemuan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Waktu Pengkajian <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_pertemuan" value="{{ $v('waktu_pertemuan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('waktu_pertemuan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium mb-1">Data Focus <span class="text-red-500">*</span></label>
                        <textarea name="data_focus" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('data_focus') }}</textarea>
                        @error('data_focus') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium mb-1">KU Pasien <span class="text-red-500">*</span></label>
                        <textarea name="ku_pasien" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('ku_pasien') }}</textarea>
                        @error('ku_pasien') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanda-tanda Vital &amp; Berat Badan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach (['td_sistolik' => 'TD Sistolik', 'td_diastolik' => 'TD Diastolik', 'nadi' => 'Nadi', 'pernapasan' => 'Pernapasan', 'suhu' => 'Suhu (°C)', 'saturasi' => 'SpO2 (%)', 'berat_badan' => 'BB Saat Ini (kg)', 'bb_sebelumnya' => 'BB Sebelumnya (kg)', 'bb_kering' => 'BB Kering (kg)'] as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="{{ $key }}" value="{{ $v($key) }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-medium mb-1">EWS (turunan)</label>
                        <input type="text" value="{{ $emr_data['ews'] ?? '-' }}" class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50" readonly>
                    </div>
                </div>
            </div>

            {{-- Parameter post-sesi --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Parameter Post-Sesi</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach (['tbv' => 'TBV (ml)', 'ufg' => 'UFG (L)', 'kt_v' => 'Kt/V', 'convective_volume' => 'Convective Volume (L)'] as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="{{ $key }}" value="{{ $v($key) }}" class="w-full border rounded-sm px-3 py-2 text-sm" oninput="cekKtv()">
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
                @if (($ktvInfo['adequat'] ?? null) === false)
                    <div class="px-5 pb-4">
                        <x-confirm-alert />
                        <div class="rounded-sm border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                            Kt/V = {{ $ktvInfo['nilai'] }} — <strong>{{ $ktvInfo['label'] }}</strong> (target SNKF ≥ 1,2).
                        </div>
                    </div>
                @else
                    <div class="px-5 pb-4">
                        <div id="peringatan-ktv" style="display:none" class="rounded-sm border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-800"></div>
                    </div>
                @endif
            </div>

            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Laboratorium</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ($labFields as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="{{ $key }}" value="{{ $v($key) }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-medium mb-1">HBsAg <span class="text-red-500">*</span></label>
                        <select name="hbsag" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <option value="">— Pilih —</option>
                            {!! \App\Helpers\SelectOption::render('hbsag', $v('hbsag')) !!}
                        </select>
                        @error('hbsag') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2 md:col-start-4">
                        <label class="block text-xs font-medium mb-1">Hasil Pemeriksaan Lain</label>
                        <textarea name="hasil_pemeriksaan_lain" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('hasil_pemeriksaan_lain') }}</textarea>
                        @error('hasil_pemeriksaan_lain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Terapi Tambahan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($terapiFields as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }}</label>
                            <input type="number" step="0.01" name="{{ $key }}" value="{{ $v($key) }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Asuhan Keperawatan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @foreach (['masalah_keperawatan' => 'Masalah Keperawatan', 'tindakan_keperawatan' => 'Tindakan Keperawatan', 'terapi_obat' => 'Terapi Obat', 'evaluasi' => 'Evaluasi Keperawatan'] as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <textarea name="{{ $key }}" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($key) }}</textarea>
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-medium mb-1">Keterangan</label>
                        <textarea name="keterangan" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keterangan') }}</textarea>
                        @error('keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </fieldset>
    </x-emr-split-layout>

    <script>
        function cekKtv() {
            var el = document.querySelector('[name="kt_v"]');
            var box = document.getElementById('peringatan-ktv');
            if (!el || !box) return;
            var val = parseFloat(el.value);
            if (!isNaN(val) && val < 1.2) {
                box.textContent = 'Kt/V = ' + val + ' — Belum Adekuat (target SNKF ≥ 1,2).';
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
            }
        }
    </script>
@endsection
