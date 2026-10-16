@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Assesmen Transfusi';
        $subtitleForm = 'Persiapan sebelum komponen darah masuk: labu darah, keadaan umum, TTV & verifikasi ganda dua perawat.';
        $routeName = null;
        $routeUrl = url('emr/form/assesmen_transfusi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_persiapan' => 'Tgl Persiapan',
            'no_labu_darah' => 'No Labu',
            'jenis_labu_darah' => 'Jenis',
        ];

        // Opsi verifikasi (label KONSEP: SESUAI / TIDAK SESUAI).
        $opsiVerifikasi = ['SESUAI', 'TIDAK SESUAI'];
        $butirDc = [
            'identitas' => 'Identitas Pasien',
            'jumlah' => 'Jumlah Darah',
            'jenis' => 'Jenis Darah',
            'golongan' => 'Golongan Darah',
        ];

        // Opsi jenis labu — rule server `in:` hanya 5 nilai (tanpa Lainnya),
        // jadi opsi blade juga harus difilter (pelajaran form 91/99).
        $opsiJenisLabu = array_values(array_filter(
            array_column(\App\Helpers\SelectOption::get('jenis_komponen_darah'), 'value'),
            fn ($nilai) => $nilai !== 'Lainnya'
        ));

        // Perbandingan jumlah labu dengan asesmen pemberian darah (form 108).
        $jumlahForm108 = $bandingDarah['jumlah_darah'] ?? null;
        $jenisForm108 = $bandingDarah['jenis_darah'] ?? null;
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Assesmen Transfusi"
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
                slug="assesmen_transfusi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= LABU DARAH ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Persiapan Labu Darah</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-w-4xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Persiapan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_persiapan" value="{{ $v('tanggal_persiapan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_persiapan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Waktu Persiapan <span class="text-red-500">*</span></label>
                            <input type="time" name="waktu_persiapan" value="{{ $v('waktu_persiapan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('waktu_persiapan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">No Labu Darah <span class="text-red-500">*</span></label>
                            <input type="text" name="no_labu_darah" value="{{ $v('no_labu_darah') }}" maxlength="50" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('no_labu_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jumlah Labu Darah <span class="text-red-500">*</span></label>
                            <input type="number" id="jumlah-labu" name="jumlah_labu_darah" value="{{ $v('jumlah_labu_darah') }}" min="1" max="10000" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jumlah_labu_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jenis Labu Darah <span class="text-red-500">*</span></label>
                            <select name="jenis_labu_darah" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Jenis --</option>
                                @foreach ($opsiJenisLabu as $jenis)
                                    <option value="{{ $jenis }}" {{ $v('jenis_labu_darah') === $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                                @endforeach
                            </select>
                            @error('jenis_labu_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Keadaan Umum <span class="text-red-500">*</span></label>
                            <select name="keadaan_umum" class="w-full border rounded-sm px-3 py-2 text-sm">
                                {!! \App\Helpers\SelectOption::render('keadaan_umum_transfusi', $v('keadaan_umum'), '-- Pilih --') !!}
                            </select>
                            @error('keadaan_umum') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Keluhan <span class="text-red-500">*</span></label>
                        <textarea name="keluhan" rows="2" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keluhan') }}</textarea>
                        @error('keluhan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="peringatan-banding" style="display:none" class="border border-amber-300 bg-amber-50 text-amber-800 text-xs rounded-sm p-3">
                        <strong>Perhatian:</strong> jumlah labu pada form ini
                        (<span id="banding-jumlah"></span>) berbeda dengan asesmen
                        pemberian darah (form 108: <span id="banding-jumlah-108"></span>,
                        jenis <span id="banding-jenis-108"></span>). Anda tetap boleh menyimpan —
                        periksa kembali ketepatan input.
                    </div>
                </div>
            </div>

            {{-- ================= VERIFIKASI GANDA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-emerald-700 font-medium text-[15px]">Verifikasi Ganda (5 Benar) — Dua Perawat</span>
                    <span id="badge-tidak-sesuai" style="display:none"
                        class="text-xs font-semibold text-red-700 bg-red-100 border border-red-300 rounded-full px-3 py-1">
                        Ada butir TIDAK SESUAI — verifikasi ulang sebelum darah dipasang
                    </span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-left">
                                    <th class="px-3 py-2 border">Butir yang Diverifikasi</th>
                                    <th class="px-3 py-2 border w-48">Perawat 1 <span class="text-red-500">*</span></th>
                                    <th class="px-3 py-2 border w-48">Perawat 2 <span class="text-red-500">*</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($butirDc as $key => $label)
                                    <tr>
                                        <td class="px-3 py-2 border">{{ $label }}</td>
                                        <td class="px-3 py-2 border">
                                            <select name="dc1_{{ $key }}" class="dc-select w-full border rounded-sm px-2 py-1.5">
                                                <option value="">-- Pilih --</option>
                                                @foreach ($opsiVerifikasi as $opsi)
                                                    <option value="{{ $opsi }}" {{ $v('dc1_'.$key) === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                                                @endforeach
                                            </select>
                                            @error('dc1_'.$key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                        <td class="px-3 py-2 border">
                                            <select name="dc2_{{ $key }}" class="dc-select w-full border rounded-sm px-2 py-1.5">
                                                <option value="">-- Pilih --</option>
                                                @foreach ($opsiVerifikasi as $opsi)
                                                    <option value="{{ $opsi }}" {{ $v('dc2_'.$key) === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                                                @endforeach
                                            </select>
                                            @error('dc2_'.$key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <x-select_pegawai
                            :selected="$v('perawat_1_id')"
                            name="perawat_1_id"
                            id="perawat_1_id"
                            label="Perawat Verifikator 1"
                            placeholder="-- Pilih Perawat --"
                            :profesiId="2"
                            :required="true" />
                        <x-select_pegawai
                            :selected="$v('perawat_2_id')"
                            name="perawat_2_id"
                            id="perawat_2_id"
                            label="Perawat Verifikator 2"
                            placeholder="-- Pilih Perawat (berbeda dari Perawat 1) --"
                            :profesiId="2"
                            :required="true" />
                    </div>
                </div>
            </div>

            {{-- ================= TANDA VITAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Tanda Vital Sebelum Transfusi</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-2 md:grid-cols-5 gap-4 max-w-4xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Sistolik (mmHg) <span class="text-red-500">*</span></label>
                        <input type="number" name="sistolik" value="{{ $v('sistolik') }}" min="50" max="300" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('sistolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Diastolik (mmHg) <span class="text-red-500">*</span></label>
                        <input type="number" name="diastolik" value="{{ $v('diastolik') }}" min="20" max="200" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('diastolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Nadi (/mnt) <span class="text-red-500">*</span></label>
                        <input type="number" name="nadi" value="{{ $v('nadi') }}" min="20" max="250" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('nadi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">RR (/mnt) <span class="text-red-500">*</span></label>
                        <input type="number" name="pernapasan" value="{{ $v('pernapasan') }}" min="5" max="80" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('pernapasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Suhu (°C) <span class="text-red-500">*</span></label>
                        <input type="number" name="suhu" value="{{ $v('suhu') }}" min="30" max="45" step="0.1" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('suhu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                var badge = document.getElementById('badge-tidak-sesuai');
                var dcSelects = document.querySelectorAll('.dc-select');
                var jumlah = document.getElementById('jumlah-labu');
                var blokBanding = document.getElementById('peringatan-banding');
                var jumlah108 = @json($jumlahForm108);
                var jenis108 = @json($jenisForm108);

                function syncBadge() {
                    var adaTidak = false;
                    dcSelects.forEach(function (sel) {
                        if (sel.value === 'TIDAK SESUAI') { adaTidak = true; }
                    });
                    badge.style.display = adaTidak ? '' : 'none';
                }

                function syncBanding() {
                    var nilai = jumlah && jumlah.value !== '' ? parseInt(jumlah.value, 10) : null;
                    var beda = nilai !== null && jumlah108 !== null && jumlah108 !== '' &&
                        nilai !== parseInt(jumlah108, 10);
                    document.getElementById('banding-jumlah').textContent = nilai === null ? '-' : nilai;
                    document.getElementById('banding-jumlah-108').textContent = jumlah108 === null ? '-' : jumlah108;
                    document.getElementById('banding-jenis-108').textContent = jenis108 || '-';
                    blokBanding.style.display = beda ? '' : 'none';
                }

                dcSelects.forEach(function (sel) {
                    sel.addEventListener('change', syncBadge);
                });
                if (jumlah) { jumlah.addEventListener('input', syncBanding); }
                syncBadge();
                syncBanding();
            })();
        </script>
    </x-emr-split-layout>
@endsection
