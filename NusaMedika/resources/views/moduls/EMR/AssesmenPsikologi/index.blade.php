@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Asesmen Psikologi Pasien & Keluarga';
        $subtitleForm = 'Status psikologis pasien & keluarga, reaksi pasien atas penyakitnya, dan reaksi keluarga.';
        $routeName = null;
        $routeUrl = url('emr/form/assesmen_psikologi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        // Pasangan hidden 0 + checkbox 1 (KONSEP §3): key tetap terkirim saat
        // tidak dicentang sehingga baris emr_detail ikut ter-update saat edit.
        $cb = function ($name) use ($v) {
            $checked = $v($name) === '1' ? 'checked' : '';
            return '<input type="hidden" name="'.$name.'" value="0">'
                .'<input type="checkbox" name="'.$name.'" value="1" '.$checked.'>';
        };

        $headers = [
            'tanggal_asesmen' => 'Tgl Observasi',
            'hubungan_keluarga' => 'Kontak Keluarga',
            'rencana_rawat' => 'Rencana Rawat',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Psikologi"
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
                slug="assesmen_psikologi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= STATUS PSIKOLOGIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Status Psikologis Pasien dan Keluarga</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Observasi <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_asesmen" value="{{ $v('tanggal_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Waktu Observasi <span class="text-red-500">*</span></label>
                            <input type="time" name="waktu_asesmen" value="{{ $v('waktu_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('waktu_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Rencana Perawatan Selanjutnya <span class="text-red-500">*</span></label>
                            <select name="rencana_rawat" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Rencana --</option>
                                <option value="Tetap Dirawat di RS" {{ $v('rencana_rawat') === 'Tetap Dirawat di RS' ? 'selected' : '' }}>Tetap Dirawat di RS</option>
                                <option value="Dirawat di Rumah" {{ $v('rencana_rawat') === 'Dirawat di Rumah' ? 'selected' : '' }}>Dirawat di Rumah</option>
                            </select>
                            @error('rencana_rawat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Apakah ada orang yang bisa dihubungi saat ini? <span class="text-red-500">*</span></label>
                            <div class="flex gap-6 mt-1">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="hubungan_keluarga" value="Tidak" {{ $v('hubungan_keluarga') === 'Tidak' ? 'checked' : '' }}>
                                    <span>Tidak</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="hubungan_keluarga" value="Ya" {{ $v('hubungan_keluarga') === 'Ya' ? 'checked' : '' }}>
                                    <span>Ya</span>
                                </label>
                            </div>
                            @error('hubungan_keluarga') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Apakah lingkungan rumah sudah disiapkan? <span class="text-red-500">*</span></label>
                            <div class="flex gap-6 mt-1">
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="lingkungan" value="Tidak" {{ $v('lingkungan') === 'Tidak' ? 'checked' : '' }}>
                                    <span>Tidak</span>
                                </label>
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="lingkungan" value="Ya" {{ $v('lingkungan') === 'Ya' ? 'checked' : '' }}>
                                    <span>Ya</span>
                                </label>
                            </div>
                            @error('lingkungan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div id="blok-kontak">
                        <p class="text-xs font-semibold text-slate-500 mb-2">Orang yang Dapat Dihubungi</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-4xl">
                            <div>
                                <label class="block text-xs font-medium mb-1">Nama Keluarga <span class="text-red-500">*</span></label>
                                <input type="text" id="nama_keluarga" name="nama_keluarga" value="{{ $v('nama_keluarga') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('nama_keluarga') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Status/Hubungan <span class="text-red-500">*</span></label>
                                <input type="text" id="status_keluarga" name="status_keluarga" value="{{ $v('status_keluarga') }}" maxlength="100" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. Suami, Ibu, Anak">
                                @error('status_keluarga') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">No. HP/Telp. <span class="text-red-500">*</span></label>
                                <input type="text" id="hp_keluarga" name="hp_keluarga" value="{{ $v('hp_keluarga') }}" maxlength="13" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('hp_keluarga') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-xs font-medium mb-1">Alamat Keluarga</label>
                                <textarea id="alamat_keluarga" name="alamat_keluarga" rows="2" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('alamat_keluarga') }}</textarea>
                                @error('alamat_keluarga') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div id="blok-perawat">
                        <label class="block text-xs font-medium mb-1">Nama Perawat di Rumah <span class="text-red-500">*</span> (bila lingkungan disiapkan)</label>
                        <input type="text" id="perawat_rumah" name="perawat_rumah" value="{{ $v('perawat_rumah') }}" maxlength="150" class="w-full border rounded-sm px-3 py-2 text-sm md:max-w-md">
                        @error('perawat_rumah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="blok-fasilitasi">
                        <label class="block text-xs font-medium mb-1">Perlu Fasilitasi Rumah Sakit? <span class="text-red-500">*</span> (bila lingkungan belum disiapkan)</label>
                        <select id="fasilitasi_rs" name="fasilitasi_rs" class="w-full border rounded-sm px-3 py-2 text-sm md:max-w-xs">
                            <option value="">-- Pilih --</option>
                            <option value="Tidak" {{ $v('fasilitasi_rs') === 'Tidak' ? 'selected' : '' }}>Tidak</option>
                            <option value="Ya" {{ $v('fasilitasi_rs') === 'Ya' ? 'selected' : '' }}>Ya</option>
                        </select>
                        @error('fasilitasi_rs') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= REAKSI PASIEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Reaksi Pasien atas Penyakitnya</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 mb-2">Asesmen Informasi</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_info_1') !!}<span>Menyangkal</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_info_2') !!}<span>Sedih / Menangis</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_info_3') !!}<span>Marah</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_info_4') !!}<span>Rasa Bersalah</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_info_5') !!}<span>Nafas Melalui Mulut</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_info_6') !!}<span>Takut</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_info_7') !!}<span>Ketidakberdayaan</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_info_8') !!}<span>Tidak Dapat Dinilai</span></label>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 mb-2">Masalah Kebutuhan Pribadi</p>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_masalah_1') !!}<span>Ansietas terhadap kematian</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_masalah_2') !!}<span>Distres Spiritual</span></label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= REAKSI KELUARGA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Reaksi Keluarga atas Penyakit Pasien</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 mb-2">Asesmen Informasi Keluarga</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_keluarga_1') !!}<span>Problem kebiasaan pola komunikasi</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_keluarga_2') !!}<span>Rasa Bersalah</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_keluarga_3') !!}<span>Penurunan Konsentrasi</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_keluarga_4') !!}<span>Letih / Lelah</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_keluarga_5') !!}<span>Ketidakmampuan memenuhi peran yang diharapkan</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_keluarga_6') !!}<span>Gangguan Tidur</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_keluarga_7') !!}<span>Keluarga kurang berkomunikasi dengan pasien</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_keluarga_8') !!}<span>Sedih / Menangis</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_keluarga_9') !!}<span>Keluarga kurang berpartisipasi dalam keputusan perawatan</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_keluarga_10') !!}<span>Marah</span></label>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 mb-2">Masalah Keperawatan Keluarga</p>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_mk_keluarga_1') !!}<span>Koping individu tidak efektif</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_mk_keluarga_2') !!}<span>Perubahan proses keluarga</span></label>
                            <label class="inline-flex items-center gap-2 text-sm">{!! $cb('psi_mk_keluarga_3') !!}<span>Distres Spiritual</span></label>
                        </div>
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function radioVal(name) {
                    var el = document.querySelector('input[name="' + name + '"]:checked');
                    return el ? el.value : '';
                }

                function toggle(blokId, selectors, tampil) {
                    var blok = document.getElementById(blokId);
                    if (blok) { blok.style.display = tampil ? '' : 'none'; }
                    selectors.forEach(function (sel) {
                        document.querySelectorAll(sel).forEach(function (input) { input.disabled = !tampil; });
                    });
                }

                function sync() {
                    var hub = radioVal('hubungan_keluarga');
                    toggle('blok-kontak', ['#nama_keluarga', '#status_keluarga', '#alamat_keluarga', '#hp_keluarga'], hub === 'Ya');
                    var ling = radioVal('lingkungan');
                    toggle('blok-perawat', ['#perawat_rumah'], ling === 'Ya');
                    toggle('blok-fasilitasi', ['#fasilitasi_rs'], ling === 'Tidak');
                }

                document.querySelectorAll('input[name="hubungan_keluarga"], input[name="lingkungan"]')
                    .forEach(function (radio) { radio.addEventListener('change', sync); });
                sync();
            })();
        </script>
    </x-emr-split-layout>
@endsection
