@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Penandaan Lokasi Operasi';
        $subtitleForm = 'Formulir penandaan lokasi operasi (site marking WHO) oleh dokter bedah sebelum masuk ruang operasi.';
        $routeName = null;
        $routeUrl = url('emr/form/penandaan_lokasi_operasi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_penandaan' => 'Tanggal',
            'jam_penandaan' => 'Jam',
            'nama_tindakan' => 'Nama Tindakan',
        ];

        $opsiSisi = ['Kanan', 'Kiri', 'Bilateral', 'Tanpa Sisi'];
        $opsiPenanda = ['Spidol Penanda Kulit', 'Plester Penanda', 'Sayatan Kulit'];
        $opsiKonfirmasi = ['Ya', 'Tidak'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Penandaan Lokasi Operasi"
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
                slug="penandaan_lokasi_operasi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= WAKTU PENANDAAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Waktu Penandaan</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Penandaan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_penandaan" value="{{ $v('tanggal_penandaan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_penandaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Penandaan <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_penandaan" value="{{ $v('jam_penandaan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_penandaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= LOKASI OPERASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Lokasi Operasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Nama Tindakan <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_tindakan" value="{{ $v('nama_tindakan') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Nama tindakan operasi...">
                            @error('nama_tindakan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lokasi Anatomi <span class="text-red-500">*</span></label>
                            <input type="text" name="lokasi_anatomi" value="{{ $v('lokasi_anatomi') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Lokasi anatomi yang ditandai...">
                            @error('lokasi_anatomi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-2">Sisi Operasi <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            @foreach ($opsiSisi as $o)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="sisi_operasi" value="{{ $o }}" {{ $v('sisi_operasi') === $o ? 'checked' : '' }} required>
                                    <span>{{ $o }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('sisi_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PENANDA & LAMPIRAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-purple-50 border-b border-slate-200">
                    <span class="text-purple-700 font-medium text-[15px]">Penanda yang Digunakan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-2">Jenis Penandaan <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            @foreach ($opsiPenanda as $o)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="jenis_penandaan" value="{{ $o }}" {{ $v('jenis_penandaan') === $o ? 'checked' : '' }} required>
                                    <span>{{ $o }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('jenis_penandaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Foto Penandaan (opsional)</label>
                        <input type="file" id="lampiran-file" accept="image/*"
                            {{ $isView ? 'disabled' : '' }}
                            class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-sm file:border-0 file:bg-cyan-50 file:px-3 file:py-2 file:text-xs file:font-medium file:text-cyan-700 hover:file:bg-cyan-100">
                        <input type="hidden" name="lampiran_penandaan" id="lampiran_penandaan" value="{{ $v('lampiran_penandaan') }}">
                        <div id="lampiran-preview" class="mt-3" style="display:none">
                            <img id="lampiran-img" src="" alt="Foto penandaan" class="max-h-64 rounded-sm border border-slate-200">
                            <button type="button" id="lampiran-hapus" class="mt-2 block text-xs text-red-600 underline" {{ $isView ? 'disabled' : '' }}>Hapus foto</button>
                        </div>
                        @error('lampiran_penandaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PETUGAS & KONFIRMASI PASIEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-indigo-50 border-b border-slate-200">
                    <span class="text-indigo-700 font-medium text-[15px]">Petugas Penanda &amp; Konfirmasi Pasien</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="max-w-md">
                        <label class="block text-xs font-medium mb-1">Petugas Penanda <span class="text-red-500">*</span></label>
                        <x-select_dokter
                            name="petugas_penanda"
                            id="petugas_penanda"
                            label=""
                            placeholder="-- Pilih Dokter --"
                            :selected="$v('petugas_penanda')"
                            :required="true" />
                        @error('petugas_penanda') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-2">Pasien Menyetujui Penandaan? <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            @foreach ($opsiKonfirmasi as $o)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="konfirmasi_pasien" id="konfirmasi-{{ $o }}" value="{{ $o }}" {{ $v('konfirmasi_pasien') === $o ? 'checked' : '' }} required>
                                    <span>{{ $o }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('konfirmasi_pasien') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="blok-alasan" style="display:none">
                        <label class="block text-xs font-medium mb-1">Alasan Pasien Tidak Menyetujui <span class="text-red-500">*</span></label>
                        <textarea name="alasan_penandaan" id="alasan_penandaan" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('alasan_penandaan') }}</textarea>
                        @error('alasan_penandaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                // Tampilkan alasan hanya saat konfirmasi pasien = Tidak
                // (required_if di server: konfirmasi_pasien,Tidak).
                function syncAlasan() {
                    var dipilih = document.querySelector('input[name="konfirmasi_pasien"]:checked');
                    var perlu = dipilih && dipilih.value === 'Tidak';
                    var blok = document.getElementById('blok-alasan');
                    if (blok) blok.style.display = perlu ? '' : 'none';
                }
                document.querySelectorAll('input[name="konfirmasi_pasien"]').forEach(function (el) {
                    el.addEventListener('change', syncAlasan);
                });
                syncAlasan();

                // Lampiran foto: input file tanpa name → FileReader → hidden input.
                var fileInput = document.getElementById('lampiran-file');
                var hidden = document.getElementById('lampiran_penandaan');
                var preview = document.getElementById('lampiran-preview');
                var img = document.getElementById('lampiran-img');
                var tombolHapus = document.getElementById('lampiran-hapus');

                function syncPreview() {
                    var nilai = hidden ? hidden.value : '';
                    if (nilai && nilai.indexOf('data:image/') === 0) {
                        img.src = nilai;
                        preview.style.display = '';
                    } else {
                        img.removeAttribute('src');
                        preview.style.display = 'none';
                    }
                }

                if (fileInput) {
                    fileInput.addEventListener('change', function (evt) {
                        var berkas = evt.target.files && evt.target.files[0];
                        if (!berkas) { return; }
                        var reader = new FileReader();
                        reader.onload = function (e) {
                            hidden.value = e.target.result;
                            syncPreview();
                        };
                        reader.readAsDataURL(berkas);
                    });
                }
                if (tombolHapus) {
                    tombolHapus.addEventListener('click', function () {
                        hidden.value = '';
                        if (fileInput) fileInput.value = '';
                        syncPreview();
                    });
                }
                syncPreview();
            })();
        </script>
    </x-emr-split-layout>
@endsection
