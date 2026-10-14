@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Surveilans Luka Insisi';
        $subtitleForm = 'Surveilans infeksi luka insisi (SSI): identitas operasi, klasifikasi luka, pencegahan & tanda infeksi.';
        $routeName = null;
        $routeUrl = url('emr/form/surveilans_luka_insisi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tgl_mrs' => 'Tgl MRS',
            'tgl_operasi' => 'Tgl Operasi',
            'klasifikasi_luka' => 'Klasifikasi',
        ];

        $opsiTrauma = ['Trauma Tertutup', 'Trauma Terbuka', 'Elektif'];
        $opsiKualifikasi = ['Spesialis', 'Subspesialis', 'Dokter Umum'];
        $opsiDrainase = ['Ada', 'Tidak Ada'];
        $opsiKlasifikasi = [
            'I' => 'Grade I — Bersih, inflamasi minimal, tanpa eksudat',
            'II' => 'Grade II — Kemerahan & pembengkakan, tanpa eksudat purulen',
            'III' => 'Grade III — Eksudat purulen, menyebar ke jaringan dalam / abses',
            'IV' => 'Grade IV — Gangren, nekrosis, atau kerusakan jaringan vital',
        ];
        $itemsProfilaksis = [
            1 => 'Antibiotik Profilaksis',
            2 => 'Persiapan Kulit Antiseptik',
            3 => 'Pencukuran Rambut (Clipper)',
            4 => 'Kontrol Suhu (Normotermia)',
            5 => 'Kontrol Glikemi',
        ];
        $itemsTanda = [
            1 => 'Nyeri / Nyeri Tekan',
            2 => 'Demam',
            3 => 'Kemerahan',
            4 => 'Bengkak Terlokalisir',
            5 => 'Drainase Purulen / Pus',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Surveilans Luka Insisi"
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
                slug="surveilans_luka_insisi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= WAKTU & IDENTITAS OPERASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Waktu &amp; Identitas Operasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Masuk RS <span class="text-red-500">*</span></label>
                            <input type="date" name="tgl_mrs" value="{{ $v('tgl_mrs') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tgl_mrs') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Operasi <span class="text-red-500">*</span></label>
                            <input type="date" name="tgl_operasi" value="{{ $v('tgl_operasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tgl_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Operasi <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_operasi" value="{{ $v('jam_operasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lama Operasi <span class="text-red-500">*</span></label>
                            <input type="text" name="lama_operasi" value="{{ $v('lama_operasi') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. 2 jam 30 menit">
                            @error('lama_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jenis Operasi <span class="text-red-500">*</span></label>
                            <select name="jenis_operasi" class="w-full border rounded-sm px-3 py-2 text-sm">
                                {!! \App\Helpers\SelectOption::render('jenis_operasi', $v('jenis_operasi'), '-- Pilih Jenis Operasi --') !!}
                            </select>
                            @error('jenis_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kamar Operasi <span class="text-red-500">*</span></label>
                            <select name="kamar_operasi" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Kamar --</option>
                                @foreach ($kamars as $kamar)
                                    <option value="{{ $kamar }}" {{ $v('kamar_operasi') === $kamar ? 'selected' : '' }}>{{ $kamar }}</option>
                                @endforeach
                            </select>
                            @error('kamar_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Kualifikasi Dokter Bedah <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-x-6 gap-y-2 mt-1">
                            @foreach ($opsiKualifikasi as $idx => $opsi)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="dokter_bedah" value="{{ $opsi }}" {{ $v('dokter_bedah') === $opsi ? 'checked' : '' }}>
                                    <span>{{ $opsi }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('dokter_bedah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Trauma Pada <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-x-6 gap-y-2 mt-1">
                            @foreach ($opsiTrauma as $idx => $opsi)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" id="chk-trauma-{{ $idx }}" name="trauma_pada" value="{{ $opsi }}" {{ $v('trauma_pada') === $opsi ? 'checked' : '' }}>
                                    <span>{{ $opsi }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('trauma_pada') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="blok-keterangan-trauma" style="display:none">
                        <label class="block text-xs font-medium mb-1">Keterangan Trauma <span class="text-red-500">*</span></label>
                        <textarea name="keterangan_trauma" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Keterangan trauma...">{{ $v('keterangan_trauma') }}</textarea>
                        @error('keterangan_trauma') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= SURVEILANS LUKA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Surveilans Luka</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Klasifikasi Luka Insisi <span class="text-red-500">*</span></label>
                            <select id="sel-klasifikasi" name="klasifikasi_luka" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Klasifikasi --</option>
                                @foreach ($opsiKlasifikasi as $value => $label)
                                    <option value="{{ $value }}" {{ $v('klasifikasi_luka') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('klasifikasi_luka') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kondisi Luka Post Operasi <span class="text-red-500">*</span></label>
                            <select name="kondisi_luka_post" class="w-full border rounded-sm px-3 py-2 text-sm">
                                {!! \App\Helpers\SelectOption::render('kondisi_luka_post', $v('kondisi_luka_post'), '-- Pilih Kondisi Luka --') !!}
                            </select>
                            @error('kondisi_luka_post') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Drainase Luka <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-x-6 gap-y-2 mt-1">
                            @foreach ($opsiDrainase as $idx => $opsi)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="drainase_luka" value="{{ $opsi }}" {{ $v('drainase_luka') === $opsi ? 'checked' : '' }}>
                                    <span>{{ $opsi }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('drainase_luka') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Pencegahan Infeksi Luka <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-1">
                            @foreach ($itemsProfilaksis as $i => $label)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="hidden" name="profilaksis_{{ $i }}" value="0">
                                    <input type="checkbox" name="profilaksis_{{ $i }}" value="1" {{ $v('profilaksis_'.$i) === '1' ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('profilaksis_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @error('profilaksis_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @error('profilaksis_3') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @error('profilaksis_4') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @error('profilaksis_5') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Tanda Infeksi Luka <span class="text-red-500">*</span></label>
                        <p id="hint-tanda" class="text-xs text-amber-600 mb-1" style="display:none">Klasifikasi luka Grade II atau lebih — minimal satu tanda wajib dicentang.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-1">
                            @foreach ($itemsTanda as $i => $label)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="hidden" name="tanda_infeksi_{{ $i }}" value="0">
                                    <input type="checkbox" name="tanda_infeksi_{{ $i }}" value="1" {{ $v('tanda_infeksi_'.$i) === '1' ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('tanda_infeksi_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @error('tanda_infeksi_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @error('tanda_infeksi_3') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @error('tanda_infeksi_4') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @error('tanda_infeksi_5') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Keterangan Surveilans</label>
                        <textarea name="keterangan_surveilans" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Keterangan surveilans...">{{ $v('keterangan_surveilans') }}</textarea>
                        @error('keterangan_surveilans') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                var blokKet = document.getElementById('blok-keterangan-trauma');
                var hintTanda = document.getElementById('hint-tanda');
                var selKlas = document.getElementById('sel-klasifikasi');
                var radios = Array.prototype.slice.call(document.querySelectorAll('[name="trauma_pada"]'));

                function syncTrauma() {
                    var aktif = radios.find(function (r) { return r.checked; });
                    var nilai = aktif ? aktif.value : '';
                    var traumaAktif = nilai === 'Trauma Tertutup' || nilai === 'Trauma Terbuka';
                    if (blokKet) { blokKet.style.display = traumaAktif ? '' : 'none'; }
                }

                function syncTanda() {
                    if (!selKlas || !hintTanda) { return; }
                    var derajat = { I: 1, II: 2, III: 3, IV: 4 };
                    var nilai = (derajat[selKlas.value] || 1) >= 2;
                    hintTanda.style.display = nilai ? '' : 'none';
                }

                radios.forEach(function (r) { r.addEventListener('change', syncTrauma); });
                if (selKlas) { selKlas.addEventListener('change', syncTanda); }
                syncTrauma();
                syncTanda();
            })();
        </script>
    </x-emr-split-layout>
@endsection
