@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Inisiasi Menyusui Dini';
        $subtitleForm = 'Dokumentasi IMD (skin-to-skin pada 1 jam pertama).';
        $routeName = null;
        $routeUrl = url('emr/form/inisiasi_menyusui_dini');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $headers = ['inisisasi_menyususui_dini' => 'IMD'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Inisiasi Menyusui Dini"
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
                slug="inisiasi_menyusui_dini"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Inisiasi Menyusui Dini</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-2">Apakah IMD dilakukan? <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2"><input type="radio" name="inisisasi_menyususui_dini" value="Ya" class="imd-toggle" {{ $v('inisisasi_menyususui_dini') === 'Ya' ? 'checked' : '' }}> Ya</label>
                            <label class="inline-flex items-center gap-2"><input type="radio" name="inisisasi_menyususui_dini" value="Tidak" class="imd-toggle" {{ $v('inisisasi_menyususui_dini') === 'Tidak' ? 'checked' : '' }}> Tidak</label>
                        </div>
                        @error('inisisasi_menyususui_dini') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div id="blok-ya" style="{{ $v('inisisasi_menyususui_dini') === 'Ya' ? '' : 'display:none' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1">Waktu Mulai (menit setelah lahir) <span class="text-red-500">*</span></label>
                                <input type="text" name="waktu_menyusui_dini" value="{{ $v('waktu_menyusui_dini') }}" placeholder="mis. 30"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('waktu_menyusui_dini') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                <p class="text-xs text-amber-600 mt-1" id="peringatan-imd" style="display:none">IMD &gt; 60 menit — di luar rekomendasi 1 jam pertama.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Durasi IMD</label>
                                <input type="text" name="durasi_menyusui_dini" value="{{ $v('durasi_menyusui_dini') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('durasi_menyusui_dini') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div id="blok-tidak" style="{{ $v('inisisasi_menyususui_dini') === 'Tidak' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Alasan Tidak Dilakukan <span class="text-red-500">*</span></label>
                        <input type="text" name="alasan_tidak_menyususui_dini" value="{{ $v('alasan_tidak_menyususui_dini') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('alasan_tidak_menyususui_dini') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                var blokYa = document.getElementById('blok-ya');
                var blokTidak = document.getElementById('blok-tidak');
                var peringatan = document.getElementById('peringatan-imd');
                var waktu = document.querySelector('input[name="waktu_menyusui_dini"]');
                function cekWaktu() {
                    if (! waktu || ! peringatan) { return; }
                    var m = waktu.value.match(/\d+/);
                    peringatan.style.display = (m && parseInt(m[0], 10) > 60) ? '' : 'none';
                }
                document.querySelectorAll('.imd-toggle').forEach(function (r) {
                    r.addEventListener('change', function () {
                        blokYa.style.display = this.value === 'Ya' ? '' : 'none';
                        blokTidak.style.display = this.value === 'Tidak' ? '' : 'none';
                    });
                });
                if (waktu) { waktu.addEventListener('input', cekWaktu); cekWaktu(); }
            })();
        </script>
    </x-emr-split-layout>
@endsection
