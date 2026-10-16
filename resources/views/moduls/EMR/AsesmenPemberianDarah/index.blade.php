@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Asesmen Pasien dengan Darah';
        $subtitleForm = 'Asesmen pra-transfusi: golongan darah & Rh, jenis dan jumlah komponen darah, riwayat transfusi.';
        $routeName = null;
        $routeUrl = url('emr/form/asesmen_pemberian_darah');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_permintaan' => 'Tgl Permintaan',
            'jenis_darah' => 'Jenis Darah',
            'jumlah_darah' => 'Jumlah',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Pemberian Darah"
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
                slug="asesmen_pemberian_darah"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= GOLONGAN DARAH ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Golongan Darah &amp; Dasar Penentuan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Golongan Darah <span class="text-red-500">*</span></label>
                        <select name="golongan_darah" class="w-full border rounded-sm px-3 py-2 text-sm">
                            {!! \App\Helpers\SelectOption::render('golongan_darah', $v('golongan_darah'), '-- Pilih Golongan --') !!}
                        </select>
                        @error('golongan_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Rhesus <span class="text-red-500">*</span></label>
                        <select name="rhesus" class="w-full border rounded-sm px-3 py-2 text-sm">
                            {!! \App\Helpers\SelectOption::render('rhesus', $v('rhesus'), '-- Pilih Rhesus --') !!}
                        </select>
                        @error('rhesus') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Dasar Penentuan <span class="text-red-500">*</span></label>
                        <select name="dasar_penentuan" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <option value="">-- Pilih Dasar --</option>
                            <option value="Sesuai anamnesis pasien" {{ $v('dasar_penentuan') === 'Sesuai anamnesis pasien' ? 'selected' : '' }}>Sesuai anamnesis pasien</option>
                            <option value="Sesuai hasil lab" {{ $v('dasar_penentuan') === 'Sesuai hasil lab' ? 'selected' : '' }}>Sesuai hasil lab</option>
                        </select>
                        @error('dasar_penentuan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium mb-1">Nomor Hasil Lab (opsional)</label>
                        <input type="text" name="nomor_hasil_lab" value="{{ $v('nomor_hasil_lab') }}" maxlength="100" class="w-full border rounded-sm px-3 py-2 text-sm md:max-w-md" placeholder="mis. LAB-2026-0001">
                        @error('nomor_hasil_lab') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= KOMPONEN DARAH ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Komponen Darah yang Diminta</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Jenis Darah <span class="text-red-500">*</span></label>
                            <select name="jenis_darah" class="w-full border rounded-sm px-3 py-2 text-sm">
                                {!! \App\Helpers\SelectOption::render('jenis_komponen_darah', $v('jenis_darah'), '-- Pilih Jenis --') !!}
                            </select>
                            @error('jenis_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jumlah Darah (mL/kantong) <span class="text-red-500">*</span></label>
                            <input type="number" name="jumlah_darah" value="{{ $v('jumlah_darah') }}" min="1" max="10000" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jumlah_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Permintaan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_permintaan" value="{{ $v('tanggal_permintaan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_permintaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Alasan Transfusi <span class="text-red-500">*</span></label>
                        <textarea name="alasan_transfusi" rows="2" maxlength="1000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('alasan_transfusi') }}</textarea>
                        @error('alasan_transfusi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <x-select_dokter
                        name="dokter_pemohon_id"
                        id="dokter_pemohon_id"
                        label="Dokter Pemohon"
                        placeholder="-- Pilih Dokter --"
                        :selected="$v('dokter_pemohon_id')"
                        :required="true" />
                </div>
            </div>

            {{-- ================= RIWAYAT TRANSFUSI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Riwayat Transfusi &amp; Reaksi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Pernah Transfusi <span class="text-red-500">*</span></label>
                        <div class="flex gap-6">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="pernah_transfusi" id="pernah-ya" value="Ya" {{ $v('pernah_transfusi') === 'Ya' ? 'checked' : '' }}>
                                <span>Ya</span>
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="pernah_transfusi" id="pernah-tidak" value="Tidak" {{ $v('pernah_transfusi') === 'Tidak' ? 'checked' : '' }}>
                                <span>Tidak</span>
                            </label>
                        </div>
                        @error('pernah_transfusi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="blok-reaksi" style="display:none">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                            <div>
                                <label class="block text-xs font-medium mb-1">Reaksi Alergi Transfusi <span class="text-red-500">*</span> (bila pernah)</label>
                                <select name="reaksi_alergi" id="sel-reaksi" class="w-full border rounded-sm px-3 py-2 text-sm">
                                    <option value="">-- Pilih --</option>
                                    <option value="Ya" {{ $v('reaksi_alergi') === 'Ya' ? 'selected' : '' }}>Ya</option>
                                    <option value="Tidak" {{ $v('reaksi_alergi') === 'Tidak' ? 'selected' : '' }}>Tidak</option>
                                </select>
                                @error('reaksi_alergi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div id="blok-pantangan" style="display:none">
                        <label class="block text-xs font-medium mb-1">Pantangan Transfusi <span class="text-red-500">*</span> (bila ada reaksi)</label>
                        <textarea name="pantangan_transfusi" rows="2" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('pantangan_transfusi') }}</textarea>
                        @error('pantangan_transfusi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                var blokReaksi = document.getElementById('blok-reaksi');
                var blokPantangan = document.getElementById('blok-pantangan');
                var selReaksi = document.getElementById('sel-reaksi');

                function radioPernah() {
                    var ya = document.getElementById('pernah-ya');
                    return ya && ya.checked ? 'Ya' : 'Tidak';
                }

                function sync() {
                    var pernah = radioPernah();
                    blokReaksi.style.display = pernah === 'Ya' ? '' : 'none';
                    var reaksi = selReaksi ? selReaksi.value : '';
                    blokPantangan.style.display = reaksi === 'Ya' ? '' : 'none';
                }

                document.getElementById('pernah-ya').addEventListener('change', sync);
                document.getElementById('pernah-tidak').addEventListener('change', sync);
                if (selReaksi) { selReaksi.addEventListener('change', sync); }
                sync();
            })();
        </script>
    </x-emr-split-layout>
@endsection
