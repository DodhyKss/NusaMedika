@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Output Cairan';
        $subtitleForm = 'Pencatatan cairan keluar beserta eliminasi BAB dan BAK.';
        $routeName = null;
        $routeUrl = url('emr/form/output');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        // Nilai eliminasi disimpan sebagai JSON, jadi perlu di-decode dulu.
        $terpilihBab = old('karakter_bab', json_decode((string) ($emr_data['karakter_bab'] ?? ''), true));
        $terpilihBak = old('karakter_bak', json_decode((string) ($emr_data['karakter_bak'] ?? ''), true));
        $terpilihBab = is_array($terpilihBab) ? $terpilihBab : [];
        $terpilihBak = is_array($terpilihBak) ? $terpilihBak : [];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Output Cairan"
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
                slug="output"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="['total_output' => 'Total Output (cc)']"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= OUTPUT CAIRAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Output Cairan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label for="urine" class="block font-bold text-slate-800 mb-1.5">
                                Urine (cc) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="any" min="0" id="urine" name="urine"
                                   value="{{ $v('urine') }}" placeholder="0"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('urine')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        @foreach ([
                            'drain' => 'Drain (cc)',
                            'ngt' => 'NGT / Gastrik Suction (cc)',
                            'wsd' => 'WSD / Watershed (cc)',
                            'output_lainnya' => 'Output Lainnya (cc)',
                        ] as $name => $label)
                            <div>
                                <label for="{{ $name }}" class="block font-bold text-slate-800 mb-1.5">{{ $label }}</label>
                                <input type="number" step="any" min="0" id="{{ $name }}" name="{{ $name }}"
                                       value="{{ $v($name) }}" placeholder="0"
                                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                @error($name)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="p-4 border border-slate-200 rounded-lg bg-slate-50">
                        <div class="flex items-center gap-4">
                            <div>
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">Output IWL (Insensible Water Loss)</div>
                                <div id="iwl" class="text-lg font-bold text-slate-700">
                                    {{ old('iwl', $emr_data['iwl'] ?? '0') }} cc
                                </div>
                            </div>
                            <p class="text-xs text-slate-500 flex-1">
                                Dihitung otomatis di server dengan formula Hudson: (25 + berat badan) x faktor suhu.
                                Berat badan terakhir: <span class="font-semibold">{{ $beratBadan !== null ? rtrim(rtrim(number_format($beratBadan, 1, ',', '.'), '0'), ',') . ' Kg' : 'belum tercatat' }}</span>,
                                suhu terakhir: <span class="font-semibold">{{ $suhu !== null ? rtrim(rtrim(number_format($suhu, 1, ',', '.'), '0'), ',') . ' C' : 'belum tercatat' }}</span>.
                            </p>
                        </div>
                        @error('iwl')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Total Output Cairan</div>
                            <div id="totalOutput" class="text-xl font-bold text-cyan-700">
                                {{ old('total_output', $emr_data['total_output'] ?? '-') }} cc
                            </div>
                        </div>
                        <p class="text-xs text-slate-400">
                            Dihitung otomatis di server dari seluruh kolom output termasuk IWL.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ================= ELIMINASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Eliminasi</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- BAB --}}
                        <div class="p-4 border border-slate-200 rounded-lg">
                            <div class="flex items-center gap-3 mb-3">
                                <label for="frekuensi_bab" class="block font-bold text-slate-800">Frekuensi BAB (x/24 jam)</label>
                                <input type="number" min="0" max="20" id="frekuensi_bab" name="frekuensi_bab"
                                       value="{{ $v('frekuensi_bab') }}" placeholder="0"
                                       class="w-28 border border-slate-300 rounded px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            </div>
                            @error('frekuensi_bab')
                                <p class="mb-2 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Karakter BAB</div>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach (\App\Helpers\SelectOption::get('karakter_bab') as $opt)
                                    <label class="flex items-center gap-2 text-slate-700 text-sm">
                                        <input type="checkbox" name="karakter_bab[]" value="{{ $opt['value'] }}"
                                               @checked(in_array((string) $opt['value'], array_map('strval', $terpilihBab), true))
                                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                                        {{ $opt['label'] }}
                                    </label>
                                @endforeach
                            </div>
                            @error('karakter_bab')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- BAK --}}
                        <div class="p-4 border border-slate-200 rounded-lg">
                            <div class="flex items-center gap-3 mb-3">
                                <label for="frekuensi_bak" class="block font-bold text-slate-800">Frekuensi BAK (x/24 jam)</label>
                                <input type="number" min="0" max="30" id="frekuensi_bak" name="frekuensi_bak"
                                       value="{{ $v('frekuensi_bak') }}" placeholder="0"
                                       class="w-28 border border-slate-300 rounded px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            </div>
                            @error('frekuensi_bak')
                                <p class="mb-2 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Karakter BAK</div>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach (\App\Helpers\SelectOption::get('karakter_bak') as $opt)
                                    <label class="flex items-center gap-2 text-slate-700 text-sm">
                                        <input type="checkbox" name="karakter_bak[]" value="{{ $opt['value'] }}"
                                               @checked(in_array((string) $opt['value'], array_map('strval', $terpilihBak), true))
                                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500/20">
                                        {{ $opt['label'] }}
                                    </label>
                                @endforeach
                            </div>
                            @error('karakter_bak')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="catatan" class="block font-bold text-slate-800 mb-1.5">Catatan</label>
                        <textarea id="catatan" name="catatan" rows="3"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('catatan') }}</textarea>
                        @error('catatan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Total output hanya tampilan; server menghitung ulang sendiri
        // (termasuk IWL yang bergantung berat badan & suhu terakhir).
        var KUNCI = ['urine', 'drain', 'ngt', 'wsd', 'output_lainnya'];

        function hitungTotal() {
            var total = 0;
            KUNCI.forEach(function (id) {
                var el = document.getElementById(id);
                var v = parseFloat((el && el.value) || '0');
                if (!isNaN(v)) total += v;
            });

            var iwlText = (document.getElementById('iwl') || {}).textContent || '0';
            var iwl = parseFloat(iwlText.replace(/[^0-9,.-]/g, '').replace(',', '.')) || 0;
            total += iwl;

            var out = document.getElementById('totalOutput');
            if (out) out.textContent = (Math.round(total * 100) / 100) + ' cc';
        }

        KUNCI.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('input', hitungTotal);
        });

        hitungTotal();
    });
    </script>

@endsection