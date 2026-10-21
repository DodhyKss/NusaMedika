@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Telaah Resep';
        $subtitleForm = 'Pemeriksaan 7 aspek resep oleh farmasis sebelum obat diserahkan. Bila ada butir tidak tercentang, catatan telaah wajib diisi.';
        $routeName = null;
        $routeUrl = url('emr/form/telaah_resep');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $kriteria = \App\Helpers\FarmasiHelper::KRITERIA_TELAAH_RESEP;
        $tercentang = 0;
        foreach (array_keys($kriteria) as $n) {
            if (\App\Helpers\FarmasiHelper::tercentang($emr_data['telaah_'.$n] ?? null)) { $tercentang++; }
        }

        $headers = [
            'tanggal_telaah' => 'Tanggal',
            'nomor_resep_id' => 'No. Resep',
            'telaah_catatan' => 'Catatan',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Telaah Resep"
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
                slug="telaah_resep"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Telaah</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Nomor Resep <span class="text-red-500">*</span></label>
                        <input type="number" name="nomor_resep_id" value="{{ $v('nomor_resep_id') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('nomor_resep_id') border-red-500 @enderror" required>
                        @error('nomor_resep_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Telaah <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_telaah" value="{{ $v('tanggal_telaah') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('tanggal_telaah') border-red-500 @enderror" required>
                        @error('tanggal_telaah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Waktu Telaah <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_telaah" value="{{ $v('waktu_telaah') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('waktu_telaah') border-red-500 @enderror" required>
                        @error('waktu_telaah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Farmasis Pemeriksa <span class="text-red-500">*</span></label>
                        <select name="petugas_id" class="w-full border rounded-sm px-3 py-2 text-sm @error('petugas_id') border-red-500 @enderror" required>
                            <option value="">-- Pilih --</option>
                            @foreach (\App\Models\Pegawai::aktif()->where('profesi_id', 4)->get() as $p)
                                <option value="{{ $p->pegawai_id }}" {{ (string) $emr_data['petugas_id'] === (string) $p->pegawai_id ? 'selected' : '' }}>
                                    {{ $p->nama_pegawai }}
                                </option>
                            @endforeach
                        </select>
                        @error('petugas_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= 7 ASPEK RESEP ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-cyan-600 font-medium text-[15px]">Aspek Penilaian Resep</span>
                    <span class="text-xs text-slate-600">Tercentang: <span id="tercentang-tampil">{{ $tercentang }}</span> / {{ count($kriteria) }}</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="panel-telaah">
                        @foreach ($kriteria as $nomor => $label)
                            <label class="flex items-start gap-2 border rounded-sm px-3 py-2">
                                {{-- Hidden 'off' + checkbox 'on': pola legacy supaya nilai
                                     selalu terkirim dan tersimpan, walau tidak dicentang. --}}
                                <input type="hidden" name="telaah_{{ $nomor }}" value="off">
                                <input type="checkbox" name="telaah_{{ $nomor }}" value="on" class="form-checkbox mt-0.5"
                                    {{ \App\Helpers\FarmasiHelper::tercentang($emr_data['telaah_'.$nomor] ?? null) ? 'checked' : '' }}>
                                <span class="text-sm"><span class="text-slate-400 mr-1">{{ $nomor }}.</span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= CATATAN TEMUAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Catatan Temuan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">
                            Catatan / Keterangan Telaah
                            <span class="text-red-500">*</span>
                            <span class="text-slate-500 font-normal">(wajib bila ada butir tidak tercentang)</span>
                        </label>
                        <textarea name="telaah_catatan" rows="3"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('telaah_catatan') border-red-500 @enderror">{{ $v('telaah_catatan') }}</textarea>
                        @error('telaah_catatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Prioritas</label>
                            <select name="prioritas" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih --</option>
                                @foreach (['BIASA', 'CITO'] as $o)
                                    <option value="{{ $o }}" {{ $v('prioritas') === $o ? 'selected' : '' }}>{{ $o }}</option>
                                @endforeach
                            </select>
                            @error('prioritas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Keterangan</label>
                            <textarea name="keterangan" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keterangan') }}</textarea>
                            @error('keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>

    @push('scripts')
        <script>
            function hitungTercentang() {
                const tampil = document.getElementById('tercentang-tampil');
                if (!tampil) return;
                let n = 0;
                Array.prototype.forEach.call(
                    document.querySelectorAll('#panel-telaah input[type="checkbox"]'),
                    function (el) { if (el.checked) n++; }
                );
                tampil.textContent = n;
            }
            document.getElementById('panel-telaah').addEventListener('change', hitungTercentang);
            hitungTercentang();
        </script>
    @endpush
@endsection