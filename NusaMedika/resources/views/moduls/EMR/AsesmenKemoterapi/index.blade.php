@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Asesmen Kemoterapi';
        $subtitleForm = 'Asesmen awal sebelum siklus kemoterapi: riwayat, protokol, penunjang, alat akses & rencana keperawatan.';
        $routeName = null;
        $routeUrl = url('emr/form/asesmen_kemoterapi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_asesmen' => 'Tgl Asesmen',
            'siklus' => 'Siklus',
            'protokol' => 'Protokol',
        ];

        $opsiRiwayat = [
            'riwayat_operasi' => 'Riwayat Operasi',
            'riwayat_kemoterapi' => 'Riwayat Kemoterapi',
            'riwayat_radioterapi' => 'Riwayat Radioterapi',
        ];
        $opsiPenunjang = [
            'penunjang_1' => 'PA (Patologi Anatomi)',
            'penunjang_2' => 'IHK',
            'penunjang_3' => 'Laboratorium',
            'penunjang_4' => 'Radiologi',
        ];
        $aspekMasalah = [
            'nyeri' => 'Nyeri',
            'resiko' => 'Risiko',
            'cemas' => 'Cemas',
            'nutrisi' => 'Nutrisi',
            'infeksi' => 'Infeksi',
        ];
        $protokolOpsi = array_column(\App\Helpers\SelectOption::get('protokol_kemoterapi'), 'value');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Kemoterapi"
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
                slug="asesmen_kemoterapi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS & SIKLUS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas &amp; Siklus Kemoterapi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Asesmen <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_asesmen" value="{{ $v('tanggal_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Waktu Asesmen <span class="text-red-500">*</span></label>
                            <input type="time" name="waktu_asesmen" value="{{ $v('waktu_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('waktu_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Siklus <span class="text-red-500">*</span></label>
                            <input type="text" name="siklus" value="{{ $v('siklus') }}" maxlength="50" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. Siklus 3">
                            @error('siklus') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Protokol Kemoterapi <span class="text-red-500">*</span></label>
                            <input type="text" name="protokol" value="{{ $v('protokol') }}" maxlength="100" list="daftar-protokol" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Pilih atau ketik protokol...">
                            <datalist id="daftar-protokol">
                                @foreach ($protokolOpsi as $protokol)
                                    <option value="{{ $protokol }}"></option>
                                @endforeach
                            </datalist>
                            @error('protokol') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <x-select_dokter
                            name="dpjp_utama"
                            id="dpjp_utama"
                            label="DPJP Utama"
                            placeholder="-- Pilih Dokter --"
                            :selected="$v('dpjp_utama')"
                            :required="true" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Diagnosis <span class="text-red-500">*</span></label>
                        <textarea name="diagnosis" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('diagnosis') }}</textarea>
                        @error('diagnosis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Riwayat <span class="text-red-500">*</span></label>
                        <p class="text-xs text-slate-500 mb-1">Centang bila ada riwayat berikut.</p>
                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            @foreach ($opsiRiwayat as $key => $label)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="hidden" name="{{ $key }}" value="0">
                                    <input type="checkbox" name="{{ $key }}" value="1" {{ $v($key) === '1' ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @foreach (array_keys($opsiRiwayat) as $key)
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= RENCANA PENUNJANG & PERSIAPAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Rencana Penunjang &amp; Persiapan Pemberian Obat</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Hasil Penunjang Terjadwal <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            @foreach ($opsiPenunjang as $key => $label)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="hidden" name="{{ $key }}" value="0">
                                    <input type="checkbox" name="{{ $key }}" value="1" {{ $v($key) === '1' ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @foreach (array_keys($opsiPenunjang) as $key)
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Pemberian Obat Kemoterapi <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_pemberian_obat" value="{{ $v('tanggal_pemberian_obat') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_pemberian_obat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Pemasangan Alat Akses <span class="text-red-500">*</span></label>
                            <select name="pasang_alat" class="w-full border rounded-sm px-3 py-2 text-sm">
                                {!! \App\Helpers\SelectOption::render('jenis_alat_akses_kemo', $v('pasang_alat'), '-- Pilih Alat Akses --') !!}
                            </select>
                            @error('pasang_alat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Obat Kemoterapi <span class="text-red-500">*</span></label>
                        <textarea name="jenis_obat" rows="2" maxlength="1000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('jenis_obat') }}</textarea>
                        @error('jenis_obat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Reaksi Kemoterapi <span class="text-red-500">*</span></label>
                            <select id="sel-reaksi" name="reaksi_kemoterapi" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Reaksi --</option>
                                <option value="Tidak ada" {{ $v('reaksi_kemoterapi') === 'Tidak ada' ? 'selected' : '' }}>Tidak ada</option>
                                <option value="Ada" {{ $v('reaksi_kemoterapi') === 'Ada' ? 'selected' : '' }}>Ada</option>
                            </select>
                            @error('reaksi_kemoterapi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Skala Nyeri (0–10)</label>
                            <input type="number" name="skala_nyeri" min="0" max="10" value="{{ $v('skala_nyeri') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('skala_nyeri') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div id="blok-ket-reaksi" style="display:none">
                        <label class="block text-xs font-medium mb-1">Keterangan Reaksi <span class="text-red-500">*</span></label>
                        <textarea name="ket_reaksi" rows="2" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('ket_reaksi') }}</textarea>
                        @error('ket_reaksi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Kondisi Rahim</label>
                        <p class="text-xs text-slate-500 mb-1">Diisi khusus pasien perempuan usia subur bila relevan (kebijakan RS).</p>
                        <textarea name="kondisi_rahim" rows="2" maxlength="1000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('kondisi_rahim') }}</textarea>
                        @error('kondisi_rahim') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Perawat Pendamping <span class="text-red-500">*</span></label>
                            <input type="text" name="perawat_kemoterapi" value="{{ $v('perawat_kemoterapi') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('perawat_kemoterapi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Keluarga Pendamping <span class="text-red-500">*</span></label>
                            <input type="text" name="keluarga_kemoterapi" value="{{ $v('keluarga_kemoterapi') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('keluarga_kemoterapi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= RENCANA KEPERAWATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Rencana Keperawatan Kemoterapi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-5">
                    <p class="text-xs text-slate-500">Lima masalah keperawatan — pilih status lalu isi intervensi bila <strong>Ya</strong>.</p>

                    @foreach ($aspekMasalah as $key => $label)
                        <div class="border border-slate-100 rounded-sm p-4 space-y-2">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div>
                                    <label class="block text-xs font-medium mb-1">Masalah {{ $label }} <span class="text-red-500">*</span></label>
                                    <select name="{{ $key }}_kemoterapi" data-isi="{{ $key }}" class="w-full border rounded-sm px-3 py-2 text-sm sel-aspek">
                                        <option value="">-- Pilih --</option>
                                        <option value="Ya" {{ $v($key.'_kemoterapi') === 'Ya' ? 'selected' : '' }}>Ya</option>
                                        <option value="Tidak" {{ $v($key.'_kemoterapi') === 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                    @error($key.'_kemoterapi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="blok-isi" data-isi="{{ $key }}" style="display:none">
                                <label class="block text-xs font-medium mb-1">Intervensi {{ $label }} <span class="text-red-500">*</span> bila Ya</label>
                                <textarea name="isi_{{ $key }}_kemoterapi" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('isi_'.$key.'_kemoterapi') }}</textarea>
                                @error('isi_'.$key.'_kemoterapi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                var selReaksi = document.getElementById('sel-reaksi');
                var blokKet = document.getElementById('blok-ket-reaksi');

                function syncReaksi() {
                    if (!selReaksi || !blokKet) { return; }
                    blokKet.style.display = selReaksi.value === 'Ada' ? '' : 'none';
                }

                function syncAspek() {
                    Array.prototype.slice.call(document.querySelectorAll('.sel-aspek')).forEach(function (sel) {
                        var key = sel.getAttribute('data-isi');
                        var blok = document.querySelector('.blok-isi[data-isi="' + key + '"]');
                        if (blok) { blok.style.display = sel.value === 'Ya' ? '' : 'none'; }
                    });
                }

                if (selReaksi) { selReaksi.addEventListener('change', syncReaksi); }
                document.querySelectorAll('.sel-aspek').forEach(function (sel) {
                    sel.addEventListener('change', syncAspek);
                });
                syncReaksi();
                syncAspek();
            })();
        </script>
    </x-emr-split-layout>
@endsection
