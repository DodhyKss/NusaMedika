@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Surat Keterangan Sehat';
        $subtitleForm = 'Surat keterangan sehat untuk keperluan administrasi pasien.';
        $routeName = null;
        $routeUrl = url('emr/form/surat_keterangan_sehat');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'no_surat' => 'No. Surat',
            'pemeriksaan_fisik' => 'Hasil Pemeriksaan',
        ];

        $sel = function ($name, $list) use ($v) {
            $html = '<select name="'.$name.'" class="w-full border rounded-sm px-2 py-1.5 text-sm"><option value="">-- Pilih --</option>';
            foreach ($list as $opt) {
                $attr = $v($name) === $opt ? ' selected' : '';
                $html .= '<option value="'.$opt.'"'.$attr.'>'.$opt.'</option>';
            }
            return $html.'</select>';
        };
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Surat Keterangan Sehat"
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
                slug="surat_keterangan_sehat"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            @include('moduls.EMR.PartialForm.pasien_identitas_rujukan')

            {{-- ================= NOMOR SURAT ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Nomor Surat</span>
                </div>
                <div class="p-5 bg-white text-sm max-w-xl">
                    <label class="block text-xs font-medium mb-1">Nomor Surat Keterangan Sehat</label>
                    <input type="text" name="no_surat" value="{{ $v('no_surat') }}" readonly
                        placeholder="Dibentuk otomatis saat disimpan"
                        class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
                    <p class="text-xs text-slate-500 mt-1">Format: urut / KDPM / SKS / bulan romawi / tahun — dihitung oleh sistem.</p>
                </div>
            </div>

            {{-- ================= HASIL PEMERIKSAAN FISIK ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Hasil Pemeriksaan Fisik</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Pemeriksaan Fisik <span class="text-red-500">*</span></label>
                        {!! $sel('pemeriksaan_fisik', $opsiFisik) !!}
                        @error('pemeriksaan_fisik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Pemeriksaan Mata <span class="text-red-500">*</span></label>
                        {!! $sel('mata', $opsiMata) !!}
                        @error('mata') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium mb-1">Keterangan <span class="text-red-500">*</span> <span class="font-normal text-slate-400">(wajib bila tidak SEHAT)</span></label>
                        <textarea id="keterangan" name="keterangan" rows="2" maxlength="1000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keterangan') }}</textarea>
                        @error('keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= TANDA VITAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Tanda Vital</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-4 gap-4 max-w-4xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Berat Badan (kg) <span class="text-red-500">*</span></label>
                        <input type="number" name="bb" step="0.1" min="1" max="400" value="{{ $v('bb') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('bb') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tinggi Badan (cm) <span class="text-red-500">*</span></label>
                        <input type="number" name="tb" step="0.1" min="30" max="250" value="{{ $v('tb') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tb') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Sistolik (mmHg) <span class="text-red-500">*</span></label>
                        <input type="number" name="sistolik" min="50" max="300" value="{{ $v('sistolik') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('sistolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Diastolik (mmHg) <span class="text-red-500">*</span></label>
                        <input type="number" name="diastolik" min="20" max="200" value="{{ $v('diastolik') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('diastolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <p class="md:col-span-4 text-xs text-slate-500">Nilai awal diambil dari tanda vital terakhir pada kunjungan ini (dapat diubah).</p>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function cek126Sks() {
                    var fisik = document.querySelector('select[name="pemeriksaan_fisik"]');
                    var box = document.getElementById('keterangan');
                    if (!fisik || !box) { return; }
                    // Keterangan wajib bila bukan SEHAT — tampil/sembunyi via style.display.
                    box.style.display = fisik.value === 'SEHAT' ? 'none' : '';
                }

                var fisikSelect = document.querySelector('select[name="pemeriksaan_fisik"]');
                if (fisikSelect) { fisikSelect.addEventListener('change', cek126Sks); }
                cek126Sks();
            })();
        </script>
    </x-emr-split-layout>
@endsection
