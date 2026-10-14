@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Check List Kesiapan Anestesi';
        $subtitleForm = 'Daftar periksa kesiapan induksi anestesi: alat, oksigen, monitor, farmasi, persetujuan & dokumentasi.';
        $routeName = null;
        $routeUrl = url('emr/form/checklist_kesiapan_anestesi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_checklist' => 'Tanggal',
            'jam_checklist' => 'Jam',
            'consent_dokumentasi_ok' => 'Persetujuan',
        ];

        // [variabel => label butir] — penilaian Ok / Tidak Ok (KONSEP §8).
        $butir = [
            'mesin_anestesi_ok' => 'Mesin & Alat Anestesi',
            'oksigen_ventilasi_ok' => 'Oksigen & Ventilasi',
            'monitor_akses_ok' => 'Monitor & Akses Vaskular',
            'farmasi_persiapan_ok' => 'Farmasi & Persiapan',
            'consent_dokumentasi_ok' => 'Persetujuan & Dokumentasi',
        ];
        $opsiOk = ['Ok', 'Tidak Ok'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Check List Kesiapan Anestesi"
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
                slug="checklist_kesiapan_anestesi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS CHECKLIST ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Check List</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Checklist <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_checklist" value="{{ $v('tanggal_checklist') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_checklist') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Checklist <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_checklist" value="{{ $v('jam_checklist') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_checklist') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= BUTIR KESIAPAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Butir Kesiapan Induksi Anestesi <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    @foreach ($butir as $key => $label)
                        <div class="flex flex-wrap items-center justify-between gap-3 border border-slate-200 rounded-sm px-4 py-3">
                            <span class="text-sm font-medium text-slate-700">{{ $label }} <span class="text-red-500">*</span></span>
                            <div class="flex gap-4">
                                @foreach ($opsiOk as $o)
                                    <label class="inline-flex items-center gap-1.5">
                                        <input type="radio" name="{{ $key }}" value="{{ $o }}" {{ $v($key) === $o ? 'checked' : '' }}>
                                        <span class="{{ $o === 'Ok' ? 'text-emerald-700' : 'text-red-600' }}">{{ $o }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error($key) <p class="w-full text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                    <div class="border border-amber-300 bg-amber-50 rounded-sm px-4 py-3 text-xs text-amber-800" id="peringatan-consent" style="display:none">
                        Persetujuan &amp; Dokumentasi berstatus <b>Tidak Ok</b> — check list ini <b>tidak akan bisa disimpan</b> sebelum diperbaiki.
                    </div>
                </div>
            </div>

            {{-- ================= KETERANGAN & CATATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Keterangan &amp; Catatan Kesiapan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div id="blok-mesin-keterangan">
                        <label class="block text-xs font-medium mb-1">Keterangan Mesin &amp; Alat <span class="text-red-500" id="bintang-mesin">*</span></label>
                        <input type="text" name="mesin_anestesi_keterangan" value="{{ $v('mesin_anestesi_keterangan') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Sebutkan ketidaksesuaian alat...">
                        @error('mesin_anestesi_keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">
                            Catatan Kesiapan <span class="text-red-500">*</span>
                            <span class="text-[11px] font-normal text-slate-500">(wajib min. 5 karakter bila ada butir Tidak Ok)</span>
                        </label>
                        <textarea name="catatan_kesiapan" rows="3" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Catatan kesiapan sebelum induksi...">{{ $v('catatan_kesiapan') }}</textarea>
                        @error('catatan_kesiapan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function sel(name) {
                    var r = document.querySelector('input[name="' + name + '"]:checked');
                    return r ? r.value : '';
                }
                function tampilkan(el, tampil, display) {
                    if (!el) return;
                    el.style.display = tampil ? (display || '') : 'none';
                }

                function syncMesin() {
                    var tidakOk = sel('mesin_anestesi_ok') === 'Tidak Ok';
                    tampilkan(document.getElementById('blok-mesin-keterangan'), tidakOk);
                    tampilkan(document.getElementById('bintang-mesin'), tidakOk);
                }
                document.querySelectorAll('input[name="mesin_anestesi_ok"]').forEach(function (r) {
                    r.addEventListener('change', syncMesin);
                });
                syncMesin();

                function syncConsent() {
                    tampilkan(document.getElementById('peringatan-consent'), sel('consent_dokumentasi_ok') === 'Tidak Ok');
                }
                document.querySelectorAll('input[name="consent_dokumentasi_ok"]').forEach(function (r) {
                    r.addEventListener('change', syncConsent);
                });
                syncConsent();
            })();
        </script>
    </x-emr-split-layout>
@endsection
