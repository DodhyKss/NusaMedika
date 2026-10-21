@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Kriteria Keluar ICU';
        $subtitleForm = 'Dokumen keputusan keluar ICU — kriteria I–IV & parameter stabil.';
        $routeName = null;
        $routeUrl = url('emr/form/kriteria_keluar_icu');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_keluar_icu' => 'Tanggal',
            'waktu_keluar_icu' => 'Waktu',
            'kesimpulan' => 'Kesimpulan',
        ];

        $butirLifeSupport = [
            'kr1_masker_nrm' => 'Masker NRM',
            'kr1_masker_rm' => 'Masker RM',
            'kr1_jackson_rees' => 'Jackson Rees',
            'kr1_ventilator' => 'Ventilator',
            'kr1_dopamin' => 'Dopamin',
            'kr1_dobutamin' => 'Dobutamin',
            'kr1_vascon' => 'Vasocon',
            'kr1_adrenalin' => 'Adrenalin',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Kriteria Keluar ICU"
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
                slug="kriteria_keluar_icu"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Pengkajian</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Keluar ICU <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_keluar_icu" value="{{ $v('tanggal_keluar_icu') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_keluar_icu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Waktu Keluar ICU <span class="text-red-500">*</span></label>
                            <input type="time" name="waktu_keluar_icu" value="{{ $v('waktu_keluar_icu') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('waktu_keluar_icu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Dokter Konsultan ICU <span class="text-red-500">*</span></label>
                            <select name="dokter_konsultan_icu" class="w-full border rounded-sm px-3 py-2 text-sm select2">
                                <option value="">-- Pilih Dokter --</option>
                                @foreach ($dokterKonsultans as $dokter)
                                    <option value="{{ $dokter->pegawai_id }}" {{ (string) $v('dokter_konsultan_icu') === (string) $dokter->pegawai_id ? 'selected' : '' }}>
                                        {{ $dokter->nama_pegawai }}
                                    </option>
                                @endforeach
                            </select>
                            @error('dokter_konsultan_icu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Diagnosa Keluar ICU <span class="text-red-500">*</span></label>
                        <textarea name="diagnosa_keluar_icu" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('diagnosa_keluar_icu') }}</textarea>
                        @error('diagnosa_keluar_icu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= KRITERIA I ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kriteria I — Tidak Lagi Memerlukan Life Support</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <p class="text-xs text-slate-600">Pilih <strong>YA</strong> bila pasien <strong>masih membutuhkan</strong> alat/obat; <strong>TIDAK</strong> bila <strong>sudah tidak membutuhkan</strong>. Status kriteria I (YA bila semua TIDAK) dihitung otomatis.</p>
                    <table class="w-full text-xs border border-slate-200">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="px-3 py-2 text-left border-b border-slate-200">Alat / Obat</th>
                                <th class="px-3 py-2 text-center border-b border-slate-200 w-48">Masih Membutuhkan?</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($butirLifeSupport as $key => $label)
                                <tr class="border-b border-slate-100 last:border-b-0">
                                    <td class="px-3 py-2">{{ $label }}</td>
                                    <td class="px-3 py-2">
                                        <div class="flex justify-center gap-4">
                                            <label class="inline-flex items-center gap-1"><input type="radio" name="{{ $key }}" value="YA" {{ $v($key) === 'YA' ? 'checked' : '' }} class="butir-kr1"> YA</label>
                                            <label class="inline-flex items-center gap-1"><input type="radio" name="{{ $key }}" value="TIDAK" {{ $v($key) === 'TIDAK' ? 'checked' : '' }} class="butir-kr1"> TIDAK</label>
                                        </div>
                                        @error($key) <p class="text-[11px] text-red-600 mt-1 text-center">{{ $message }}</p> @enderror
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium">Status Kriteria I (turunan)</span>
                        <span id="badge-kr1" class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-slate-200 text-slate-700">—</span>
                    </div>
                </div>
            </div>

            {{-- ================= KRITERIA II ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kriteria II — Terapi Gagal / Prognosis Jelek</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <p class="text-xs text-slate-600 mb-3">Terapi telah dinyatakan gagal, prognosis jangka pendek jelek, dan manfaat kelanjutan terapi intensif kecil (gagal multi organ tidak berespons terhadap terapi agresif).</p>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center gap-2"><input type="radio" name="kr2_status" value="YA" {{ $v('kr2_status') === 'YA' ? 'checked' : '' }}> YA</label>
                        <label class="inline-flex items-center gap-2"><input type="radio" name="kr2_status" value="TIDAK" {{ $v('kr2_status') === 'TIDAK' ? 'checked' : '' }}> TIDAK</label>
                    </div>
                    @error('kr2_status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= KRITERIA III ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kriteria III — Kondisi Stabil Normal (Base Line)</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="flex gap-4">
                        <label class="inline-flex items-center gap-2"><input type="radio" name="kr3_status" value="YA" {{ $v('kr3_status') === 'YA' ? 'checked' : '' }}> YA</label>
                        <label class="inline-flex items-center gap-2"><input type="radio" name="kr3_status" value="TIDAK" {{ $v('kr3_status') === 'TIDAK' ? 'checked' : '' }}> TIDAK</label>
                    </div>
                    @error('kr3_status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tensi (mmHg) <span class="text-red-500">*</span></label>
                            <input type="number" name="kr3_tensi" value="{{ $v('kr3_tensi') }}" min="30" max="300" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                            @error('kr3_tensi') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Nadi (/mnt) <span class="text-red-500">*</span></label>
                            <input type="number" name="kr3_nadi" value="{{ $v('kr3_nadi') }}" min="0" max="250" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                            @error('kr3_nadi') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">RR (/mnt) <span class="text-red-500">*</span></label>
                            <input type="number" name="kr3_rr" value="{{ $v('kr3_rr') }}" min="0" max="120" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                            @error('kr3_rr') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Suhu (°C) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="kr3_suhu" value="{{ $v('kr3_suhu') }}" min="25" max="45" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                            @error('kr3_suhu') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">GCS (3–15) <span class="text-red-500">*</span></label>
                            <input type="number" name="kr3_gcs" value="{{ $v('kr3_gcs') }}" min="3" max="15" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                            @error('kr3_gcs') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">CVP (mmHg) <span class="text-red-500">*</span></label>
                            <input type="number" name="kr3_cvp" value="{{ $v('kr3_cvp') }}" min="0" max="50" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                            @error('kr3_cvp') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= KRITERIA IV ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kriteria IV — Prognosis Terminal</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <p class="text-xs text-slate-600">Manfaat terapi intensif kecil karena penyakit primernya sudah terminal, tidak berespons terhadap terapi ICU, prognosis jangka pendek jelek, dan tidak ada terapi potensial untuk memperbaiki prognosisnya.</p>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center gap-2"><input type="radio" name="kr4_status" value="YA" {{ $v('kr4_status') === 'YA' ? 'checked' : '' }}> YA</label>
                        <label class="inline-flex items-center gap-2"><input type="radio" name="kr4_status" value="TIDAK" {{ $v('kr4_status') === 'TIDAK' ? 'checked' : '' }}> TIDAK</label>
                    </div>
                    @error('kr4_status') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    <div id="blok-kr4-lain" style="{{ $v('kr4_status') === 'YA' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Lain-lain</label>
                        <textarea name="kr4_lain" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('kr4_lain') }}</textarea>
                        @error('kr4_lain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= KESIMPULAN & CATATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kesimpulan & Catatan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium">Kesimpulan (turunan server)</span>
                        <span id="badge-kesimpulan" class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-slate-200 text-slate-700">—</span>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Catatan</label>
                        <p class="text-[11px] text-slate-500 mb-1">Wajib diisi (min. 5 karakter) bila Kriteria II atau IV dinyatakan YA.</p>
                        <textarea name="catatan" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('catatan') }}</textarea>
                        @error('catatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                var badgeKr1 = document.getElementById('badge-kr1');
                var badgeKes = document.getElementById('badge-kesimpulan');

                function nilai(name) {
                    var el = document.querySelector('input[name="' + name + '"]:checked');
                    return el ? el.value : null;
                }
                function hitung() {
                    var butir = ['kr1_masker_nrm','kr1_masker_rm','kr1_jackson_rees','kr1_ventilator','kr1_dopamin','kr1_dobutamin','kr1_vascon','kr1_adrenalin'];
                    var semuaTidak = true;
                    var lengkap = true;
                    butir.forEach(function (n) {
                        var v = nilai(n);
                        if (v === null) { lengkap = false; }
                        else if (v !== 'TIDAK') { semuaTidak = false; }
                    });
                    var kr1 = lengkap ? (semuaTidak ? 'YA' : 'TIDAK') : null;
                    badgeKr1.textContent = kr1 || '—';
                    badgeKr1.className = 'inline-block px-2 py-0.5 rounded text-xs font-semibold ' +
                        (kr1 === 'YA' ? 'bg-green-100 text-green-700' : kr1 === 'TIDAK' ? 'bg-slate-200 text-slate-600' : 'bg-slate-200 text-slate-700');

                    var kr2 = nilai('kr2_status');
                    var kr3 = nilai('kr3_status');
                    var kr4 = nilai('kr4_status');
                    var kes = null;
                    if (kr1 !== null && kr2 !== null && kr3 !== null && kr4 !== null) {
                        if (kr2 === 'YA' || kr4 === 'YA') { kes = 'TIDAK LAYAK'; }
                        else if (kr1 === 'YA' || kr3 === 'YA') { kes = 'LAYAK'; }
                        else { kes = 'TIDAK LAYAK'; }
                    }
                    badgeKes.textContent = kes || '—';
                    badgeKes.className = 'inline-block px-2 py-0.5 rounded text-xs font-semibold ' +
                        (kes === 'LAYAK' ? 'bg-green-100 text-green-700' : kes === 'TIDAK LAYAK' ? 'bg-red-100 text-red-700' : 'bg-slate-200 text-slate-700');

                    document.getElementById('blok-kr4-lain').style.display = kr4 === 'YA' ? '' : 'none';
                }
                document.querySelectorAll('input[type="radio"]').forEach(function (r) { r.addEventListener('change', hitung); });
                hitung();
            })();
        </script>
    </x-emr-split-layout>
@endsection
