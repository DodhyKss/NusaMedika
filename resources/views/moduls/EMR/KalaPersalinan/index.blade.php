@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $apgar = $apgar ?? [];
        $titleForm = 'Kala I-IV Persalinan';
        $subtitleForm = 'Observasi per kala persalinan. Satu kala per pengisian; APGAR menit 1 & 5 ditampilkan dari form Penilaian APGAR.';
        $routeName = null;
        $routeUrl = url('emr/form/kala_persalinan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'kala' => 'Kala',
            'tanggal_kala' => 'Tanggal',
            'jam_kala' => 'Jam',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Kala Persalinan"
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
                slug="kala_persalinan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS KALA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Kala</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Kala <span class="text-red-500">*</span></label>
                            <select name="kala" id="pilih-kala" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Kala --</option>
                                @for ($k = 1; $k <= 4; $k++)
                                    <option value="{{ $k }}" {{ (string) $v('kala') === (string) $k ? 'selected' : '' }}>Kala {{ ['I', 'II', 'III', 'IV'][$k - 1] }}</option>
                                @endfor
                            </select>
                            @error('kala') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_kala" value="{{ $v('tanggal_kala') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_kala') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_kala" value="{{ $v('jam_kala') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_kala') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= KALA I: PEMERIKSAAN DALAM / HIS / DJJ ================= --}}
            <div id="panel-kala-1" class="border border-slate-200 rounded-sm shadow-sm" style="{{ (string) $v('kala') === '1' ? '' : 'display:none' }}">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kala I — Pemeriksaan Dalam, HIS & DJJ</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Pemeriksaan Dalam (VT) <span class="text-red-500">*</span></label>
                        <textarea name="pemeriksaan_dalam" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('pemeriksaan_dalam') }}</textarea>
                        @error('pemeriksaan_dalam') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">HIS</label>
                            <textarea name="his" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('his') }}</textarea>
                            @error('his') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">DJJ (denyut jantung janin)</label>
                            <input type="text" name="djj" value="{{ $v('djj') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('djj') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= MASALAH & PENATALAKSANAAN (4 slot) ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Masalah & Penatalaksanaan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @for ($k = 1; $k <= 4; $k++)
                        <div class="border border-slate-100 rounded-sm p-4 space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
                                <div>
                                    <label class="block text-xs font-medium mb-2">Masalah {{ $k }} <span class="text-red-500">*</span></label>
                                    <div class="flex gap-4">
                                        <label class="inline-flex items-center gap-2"><input type="radio" name="masalah_kala_{{ $k }}" value="Tidak" class="opsi-masalah-{{ $k }}" {{ $v('masalah_kala_'.$k) === 'Tidak' ? 'checked' : '' }}> Tidak</label>
                                        <label class="inline-flex items-center gap-2"><input type="radio" name="masalah_kala_{{ $k }}" value="Ya" class="opsi-masalah-{{ $k }}" {{ $v('masalah_kala_'.$k) === 'Ya' ? 'checked' : '' }}> Ya</label>
                                    </div>
                                    @error('masalah_kala_'.$k) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div id="blok-masalah-{{ $k }}" style="{{ $v('masalah_kala_'.$k) === 'Ya' ? '' : 'display:none' }}">
                                    <label class="block text-xs font-medium mb-1">Detail Masalah {{ $k }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="masalah_kala_detail_{{ $k }}" value="{{ $v('masalah_kala_detail_'.$k) }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                    @error('masalah_kala_detail_'.$k) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div id="blok-penatalaksanaan-{{ $k }}" style="{{ $v('masalah_kala_'.$k) === 'Ya' ? '' : 'display:none' }}">
                                <label class="block text-xs font-medium mb-1">Penatalaksanaan {{ $k }}</label>
                                <input type="text" name="penatalaksanaan_kala_{{ $k }}" value="{{ $v('penatalaksanaan_kala_'.$k) }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('penatalaksanaan_kala_'.$k) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- ================= APGAR READ-ONLY (form 66) ================= --}}
            <div id="panel-apgar" class="border border-slate-200 rounded-sm shadow-sm" style="{{ (string) $v('kala') === '2' ? '' : 'display:none' }}">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">APGAR Score (dari Form Penilaian APGAR)</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <table class="w-full text-sm border border-slate-200">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="border border-slate-200 px-3 py-2 text-left">Menit 1</th>
                                <th class="border border-slate-200 px-3 py-2 text-left">Menit 5</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-slate-200 px-3 py-2">{{ $apgar['apgar_total_1'] ?? '-' }}</td>
                                <td class="border border-slate-200 px-3 py-2">{{ $apgar['apgar_total_5'] ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-xs text-slate-500 mt-2">Nilai diambil dari form Penilaian Bayi dengan APGAR Score terbaru pada kunjungan ini.</p>
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                var selectKala = document.getElementById('pilih-kala');
                function sinkron() {
                    var kala = selectKala ? selectKala.value : '';
                    var p1 = document.getElementById('panel-kala-1');
                    var apgar = document.getElementById('panel-apgar');
                    if (p1) p1.style.display = (kala === '1') ? '' : 'none';
                    if (apgar) apgar.style.display = (kala === '2') ? '' : 'none';
                }
                if (selectKala) selectKala.addEventListener('change', sinkron);
                sinkron();

                @for ($k = 1; $k <= 4; $k++)
                    document.querySelectorAll('.opsi-masalah-{{ $k }}').forEach(function (r) {
                        r.addEventListener('change', function () {
                            var tampil = this.value === 'Ya';
                            document.getElementById('blok-masalah-{{ $k }}').style.display = tampil ? '' : 'none';
                            document.getElementById('blok-penatalaksanaan-{{ $k }}').style.display = tampil ? '' : 'none';
                        });
                    });
                @endfor
            })();
        </script>
    </x-emr-split-layout>
@endsection
