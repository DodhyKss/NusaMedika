@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Catatan Keperawatan / TTV MCU';
        $subtitleForm = 'Form pembuka episode Medical Check Up. BMI dihitung otomatis dari berat & tinggi badan.';
        $routeName = null;
        $routeUrl = url('emr/form/ttv_mcu');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'keluhan_saat_ini' => 'Keluhan',
            'sistolik' => 'TD',
            'bmi' => 'BMI',
        ];

        // Toggle panel detail gaya hidup: radio 1 = Ya → tampilkan input.
        $gayaHidup = ['merokok' => 'Merokok', 'alkohol' => 'Alkohol', 'olahraga' => 'Olahraga'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat TTV MCU"
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
                slug="ttv_mcu"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS & ANAMNESIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Anamnesis</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_ttv" value="{{ $v('tanggal_ttv') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @error('tanggal_ttv') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_ttv" value="{{ $v('jam_ttv') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @error('jam_ttv') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jenis Pekerjaan <span class="text-red-500">*</span></label>
                            <select name="jenis_pekerjaan" class="w-full border rounded-sm px-3 py-2 text-sm" required>
                                <option value="">-- Pilih pekerjaan --</option>
                                @foreach ($pekerjaan as $p)
                                    <option value="{{ $p['value'] }}" {{ $v('jenis_pekerjaan') === $p['value'] ? 'selected' : '' }}>
                                        {{ $p['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_pekerjaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Keluhan Saat Ini <span class="text-red-500">*</span></label>
                        <textarea name="keluhan_saat_ini" rows="2"
                            class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keluhan_saat_ini') }}</textarea>
                        @error('keluhan_saat_ini') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Riwayat Penyakit <span class="text-red-500">*</span></label>
                        <textarea name="riwayat_penyakit" rows="2"
                            class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('riwayat_penyakit') }}</textarea>
                        @error('riwayat_penyakit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Riwayat Alergi <span class="text-red-500">*</span></label>
                        <textarea name="riwayat_alergi" rows="2" placeholder="Tulis &quot;Tidak ada&quot; bila tidak ada"
                            class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('riwayat_alergi') }}</textarea>
                        @error('riwayat_alergi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= RIWAYAT KELUARGA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Keluarga</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Ayah <span class="text-red-500">*</span></label>
                            <input type="text" name="riwayat_keluarga_ayah" value="{{ $v('riwayat_keluarga_ayah') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('riwayat_keluarga_ayah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Ibu <span class="text-red-500">*</span></label>
                            <input type="text" name="riwayat_keluarga_ibu" value="{{ $v('riwayat_keluarga_ibu') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('riwayat_keluarga_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lainnya</label>
                            <input type="text" name="riwayat_keluarga_lain" value="{{ $v('riwayat_keluarga_lain') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('riwayat_keluarga_lain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= GAYA HIDUP ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Kebiasaan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @foreach ($gayaHidup as $kunci => $label)
                        @php $ya = $v('riwayat_'.$kunci) === '1'; @endphp
                        <div>
                            <label class="block text-xs font-medium mb-2">{{ $label }} <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="riwayat_{{ $kunci }}" value="0" class="gl-toggle" data-target="gl-{{ $kunci }}"
                                        {{ ! $ya ? 'checked' : '' }}> Tidak
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" name="riwayat_{{ $kunci }}" value="1" class="gl-toggle" data-target="gl-{{ $kunci }}"
                                        {{ $ya ? 'checked' : '' }}> Ya
                                </label>
                            </div>
                            @error('riwayat_'.$kunci) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            <div id="gl-{{ $kunci }}" class="mt-2" style="{{ $ya ? '' : 'display:none' }}">
                                <input type="text" name="riwayat_{{ $kunci }}_detail" value="{{ $v('riwayat_'.$kunci.'_detail') }}"
                                    placeholder="Keterangan {{ $label }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('riwayat_'.$kunci.'_detail') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= TANDA VITAL & ANTROPOMETRI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanda Vital &amp; Antropometri</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Sistolik (mmHg) <span class="text-red-500">*</span></label>
                            <input type="number" name="sistolik" value="{{ $v('sistolik') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('sistolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Diastolik (mmHg) <span class="text-red-500">*</span></label>
                            <input type="number" name="diastolik" value="{{ $v('diastolik') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('diastolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Nadi (x/mnt) <span class="text-red-500">*</span></label>
                            <input type="number" name="nadi" value="{{ $v('nadi') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('nadi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Pernapasan (x/mnt) <span class="text-red-500">*</span></label>
                            <input type="number" name="pernapasan" value="{{ $v('pernapasan') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('pernapasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Suhu (°C) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="suhu" value="{{ $v('suhu') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('suhu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Berat Badan (kg) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="berat_badan" value="{{ $v('berat_badan') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('berat_badan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tinggi Badan (cm) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="tinggi_badan" value="{{ $v('tinggi_badan') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tinggi_badan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lingkar Perut (cm) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="lingkar_perut" value="{{ $v('lingkar_perut') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('lingkar_perut') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">BMI dihitung otomatis saat disimpan — tidak perlu diisi manual.</p>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                // Toggle detail gaya hidup mengikuti radio Tidak/Ya.
                // Lepas atribut name saat "Tidak" supaya field tidak ikut terkirim.
                document.querySelectorAll('.gl-toggle').forEach(function (radio) {
                    radio.addEventListener('change', function () {
                        var panel = document.getElementById(this.dataset.target);
                        var input = panel.querySelector('input[type="text"]');
                        var ya = this.value === '1';
                        panel.style.display = ya ? '' : 'none';
                        if (ya) { input.setAttribute('name', input.dataset.name || input.name || ('riwayat_' + panel.id.replace('gl-', '') + '_detail')); }
                        else { input.removeAttribute('name'); }
                    });
                });
                // Sinkronkan atribut name saat load (nilai Ya tersimpan).
                document.querySelectorAll('div[id^="gl-"]').forEach(function (panel) {
                    var input = panel.querySelector('input[type="text"]');
                    var kunci = panel.id.replace('gl-', '');
                    input.dataset.name = 'riwayat_' + kunci + '_detail';
                    if (panel.style.display === 'none') { input.removeAttribute('name'); }
                });
            })();
        </script>
    </x-emr-split-layout>
@endsection
