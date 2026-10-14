@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Obstetri MCU';
        $subtitleForm = 'Anamnesis obstetri. Field obstetri hanya berlaku untuk pasien perempuan — pilih "L" untuk menyembunyikannya.';
        $routeName = null;
        $routeUrl = url('emr/form/obstetri_mcu');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');
        $perempuan = $v('jenis_kelamin_pasien') !== 'L';

        $headers = [
            'jenis_kelamin_pasien' => 'L/P',
            'kehamilan_g' => 'G/P/A',
            'kb_metode' => 'KB',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Obstetri MCU"
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
                slug="obstetri_mcu"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= JENIS KELAMIN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Obstetri</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label class="block text-xs font-medium mb-2">Jenis Kelamin Pasien <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="jenis_kelamin_pasien" value="L" class="jk-toggle"
                                {{ ! $perempuan ? 'checked' : '' }}> L
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="jenis_kelamin_pasien" value="P" class="jk-toggle"
                                {{ $perempuan ? 'checked' : '' }}> P
                        </label>
                    </div>
                    @error('jenis_kelamin_pasien') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div id="panel-obstetri" style="{{ $perempuan ? '' : 'display:none' }}">

                {{-- ================= HAID & MENARCHE ================= --}}
                <div class="border border-slate-200 rounded-sm shadow-sm space-y-4">
                    <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                        <span class="text-cyan-600 font-medium text-[15px]">Haid &amp; Menarche</span>
                    </div>
                    <div class="p-5 bg-white text-sm">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1">Menarche (umur) <span class="text-red-500">*</span></label>
                                <input type="text" name="menarche" value="{{ $v('menarche') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('menarche') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Haid Terakhir <span class="text-red-500">*</span></label>
                                <input type="date" name="haid_terakhir" value="{{ $v('haid_terakhir') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('haid_terakhir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Siklus Haid <span class="text-red-500">*</span></label>
                                <input type="text" name="siklus_haid" value="{{ $v('siklus_haid') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('siklus_haid') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-2">Nyeri Haid <span class="text-red-500">*</span></label>
                                <div class="flex gap-3">
                                    <label class="inline-flex items-center gap-1"><input type="radio" name="nyeri_haid" value="Ya" {{ $v('nyeri_haid') === 'Ya' ? 'checked' : '' }}> Ya</label>
                                    <label class="inline-flex items-center gap-1"><input type="radio" name="nyeri_haid" value="Tidak" {{ $v('nyeri_haid') === 'Tidak' ? 'checked' : '' }}> Tidak</label>
                                </div>
                                @error('nyeri_haid') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= KB & RIWAYAT KEHAMILAN ================= --}}
                <div class="border border-slate-200 rounded-sm shadow-sm">
                    <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                        <span class="text-cyan-600 font-medium text-[15px]">Kontrasepsi &amp; Riwayat Kehamilan</span>
                    </div>
                    <div class="p-5 bg-white text-sm space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1">Metode KB <span class="text-red-500">*</span></label>
                                <select name="kb_metode" class="w-full border rounded-sm px-3 py-2 text-sm">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($kbMetode as $m)
                                        <option value="{{ $m }}" {{ $v('kb_metode') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                    @endforeach
                                </select>
                                @error('kb_metode') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-2">KB Aktif <span class="text-red-500">*</span></label>
                                <div class="flex gap-3">
                                    <label class="inline-flex items-center gap-1"><input type="radio" name="kb_aktif" value="Ya" {{ $v('kb_aktif') === 'Ya' ? 'checked' : '' }}> Ya</label>
                                    <label class="inline-flex items-center gap-1"><input type="radio" name="kb_aktif" value="Tidak" {{ $v('kb_aktif') === 'Tidak' ? 'checked' : '' }}> Tidak</label>
                                </div>
                                @error('kb_aktif') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1">G (kehamilan) <span class="text-red-500">*</span></label>
                                <input type="number" min="0" name="kehamilan_g" value="{{ $v('kehamilan_g') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('kehamilan_g') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">P (persalinan) <span class="text-red-500">*</span></label>
                                <input type="number" min="0" name="kehamilan_p" value="{{ $v('kehamilan_p') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('kehamilan_p') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">A (abortus) <span class="text-red-500">*</span></label>
                                <input type="number" min="0" name="kehamilan_a" value="{{ $v('kehamilan_a') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('kehamilan_a') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Jumlah Anak Lahir <span class="text-red-500">*</span></label>
                                <input type="number" min="0" name="jml_anak_lahir" value="{{ $v('jml_anak_lahir') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('jml_anak_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Jumlah Anak Hidup <span class="text-red-500">*</span></label>
                                <input type="number" min="0" name="jml_anak_hidup" value="{{ $v('jml_anak_hidup') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('jml_anak_hidup') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1">Keguguran Terakhir</label>
                                <input type="date" name="keguguran_terakhir" value="{{ $v('keguguran_terakhir') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('keguguran_terakhir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Keterangan Keguguran</label>
                                <input type="text" name="keguguran_keterangan" value="{{ $v('keguguran_keterangan') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('keguguran_keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Hamil Terakhir</label>
                                <input type="date" name="hamil_terakhir" value="{{ $v('hamil_terakhir') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('hamil_terakhir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lama Kehamilan Terakhir</label>
                            <input type="text" name="hamil_terakhir_lama" value="{{ $v('hamil_terakhir_lama') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('hamil_terakhir_lama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- ================= PENCEGAHAN & KELUHAN LAIN ================= --}}
                <div class="border border-slate-200 rounded-sm shadow-sm">
                    <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                        <span class="text-cyan-600 font-medium text-[15px]">Pemeriksaan Preventif &amp; Keluhan Lain</span>
                    </div>
                    <div class="p-5 bg-white text-sm space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1">Waktu Pap Smear</label>
                                <input type="date" name="waktu_paps" value="{{ $v('waktu_paps') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('waktu_paps') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">SC</label>
                                <input type="text" name="sc" value="{{ $v('sc') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('sc') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Coitus</label>
                                <input type="text" name="coitus" value="{{ $v('coitus') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('coitus') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1">Coitus Terakhir</label>
                                <input type="date" name="coitus_terakhir" value="{{ $v('coitus_terakhir') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('coitus_terakhir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Discharge</label>
                                <input type="text" name="discharge" value="{{ $v('discharge') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('discharge') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Gatal</label>
                                <input type="text" name="gatal" value="{{ $v('gatal') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('gatal') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1">Hari</label>
                                <input type="text" name="hari" value="{{ $v('hari') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('hari') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Hormon</label>
                                <input type="text" name="hormon" value="{{ $v('hormon') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('hormon') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Normal</label>
                                <input type="text" name="normal" value="{{ $v('normal') }}"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('normal') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </fieldset>

        <script>
            (function () {
                // Sembunyikan seluruh panel obstetri saat jenis kelamin = L.
                var panel = document.getElementById('panel-obstetri');
                var inputs = panel.querySelectorAll('input, select, textarea');
                document.querySelectorAll('.jk-toggle').forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        var p = this.value === 'P';
                        panel.style.display = p ? '' : 'none';
                        inputs.forEach(function (el) {
                            if (p) {
                                if (el.dataset.name) { el.setAttribute('name', el.dataset.name); delete el.dataset.name; }
                            } else if (el.name) {
                                el.dataset.name = el.name;
                                el.removeAttribute('name');
                            }
                        });
                    });
                });
            })();
        </script>
    </x-emr-split-layout>
@endsection
