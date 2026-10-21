@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Skrining Nyeri Perina';
        $subtitleForm = 'NIPS - Neonatal Infant Pain Scale untuk neonatus, 6 parameter dengan total maksimum 7.';
        $routeName = null;
        $routeUrl = url('emr/form/skrining_nyeri_perina');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $nipsValues = [];
        foreach (array_keys(\App\Helpers\NyeriHelper::nipsItems()) as $parameter) {
            $nipsValues[$parameter] = $v($parameter);
        }
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Skrining Nyeri Perina"
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
                slug="skrining_nyeri_perina"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'total_nips' => 'Total NIPS',
                    'interpretasi_nyeri' => 'Interpretasi',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= OBSERVASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Observasi</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_nips" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_nips" name="tanggal_nips"
                                   value="{{ $v('tanggal_nips') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_nips')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_nips" class="block font-bold text-slate-800 mb-1.5">
                                Waktu Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_nips" name="waktu_nips" step="60"
                                   value="{{ $v('waktu_nips') ? substr((string) $v('waktu_nips'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_nips')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= SKALA NIPS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Penilaian Nyeri Perina (NIPS)</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    @include('moduls.EMR.PartialForm._nips_scale', ['values' => $nipsValues, 'idPrefix' => 'nips38'])
                    @error('nips_ekspresi_wajah')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                    @error('nips_tangisan')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                    @error('nips_pola_napas')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                    @error('nips_gerakan_lengan')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                    @error('nips_gerakan_tungkai')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                    @error('nips_status_jaga')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- ================= HASIL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Hasil Penilaian</span>
                </div>
                <div class="p-5 bg-white">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Total Skor NIPS</div>
                            <div id="hasil_nips" class="text-xl font-bold text-slate-700">
                                {{ old('total_nips', $emr_data['total_nips'] ?? '-') }}
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Interpretasi Skor Nyeri</div>
                            <div class="mt-1">
                                <span id="hasil_interpretasi"
                                      class="inline-block border rounded px-2.5 py-1 text-sm font-semibold {{ in_array((string) $v('interpretasi_nyeri'), ['Tidak Nyeri - Nyeri Ringan', 'Nyeri Ringan - Sedang', 'Nyeri Berat'], true) ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ $v('interpretasi_nyeri') !== '' ? $v('interpretasi_nyeri') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-100 border-b border-slate-200">
                                    <th class="px-3 py-2 font-bold text-slate-600">Total</th>
                                    <th class="px-3 py-2 font-bold text-slate-600">Interpretasi</th>
                                    <th class="px-3 py-2 font-bold text-slate-600">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-slate-100">
                                    <td class="px-3 py-2 text-slate-700">0-2</td>
                                    <td class="px-3 py-2 text-slate-600">Tidak nyeri - nyeri ringan</td>
                                    <td class="px-3 py-2 text-slate-600">Monitoring</td>
                                </tr>
                                <tr class="border-b border-slate-100">
                                    <td class="px-3 py-2 text-slate-700">3-4</td>
                                    <td class="px-3 py-2 text-slate-600">Nyeri ringan - sedang</td>
                                    <td class="px-3 py-2 text-slate-600">Manajemen nyeri non-farmakologi, pengkajian ulang menit ke-30</td>
                                </tr>
                                <tr class="border-b border-slate-100">
                                    <td class="px-3 py-2 text-slate-700">&gt; 4</td>
                                    <td class="px-3 py-2 text-slate-600">Nyeri berat</td>
                                    <td class="px-3 py-2 text-slate-600">Manajemen nyeri non-farmakologi, pengkajian ulang menit ke-30</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="mt-3 text-xs text-slate-400">
                        Total dan interpretasi dihitung ulang di server dari 6 parameter mentah.
                        Skor hanya disimpan bila 6 dari 6 parameter terisi.
                    </p>

                    <div class="mt-5">
                        <label for="catatan" class="block font-bold text-slate-800 mb-1.5">Keterangan</label>
                        <textarea id="catatan" name="catatan" rows="2" maxlength="1000"
                                  placeholder="Contoh: pengkajian ulang dilakukan menit ke-30"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('catatan') }}</textarea>
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
            var PARAMETER = ['nips_ekspresi_wajah', 'nips_tangisan', 'nips_pola_napas',
                'nips_gerakan_lengan', 'nips_gerakan_tungkai', 'nips_status_jaga'];

            var hasilNips = document.getElementById('hasil_nips');
            var hasilInterpretasi = document.getElementById('hasil_interpretasi');

            function interpretasi(total) {
                if (total <= 2) return 'Tidak Nyeri - Nyeri Ringan';
                if (total <= 4) return 'Nyeri Ringan - Sedang';
                return 'Nyeri Berat';
            }

            function syncTotal() {
                var total = 0;
                var terisi = 0;
                PARAMETER.forEach(function (nama) {
                    var el = document.querySelector('input[name="' + nama + '"]:checked');
                    if (el) { total += parseInt(el.value, 10) || 0; terisi++; }
                });

                // Hanya 6/6 terisi yang ditampilkan; kalau belum, belum dinilai.
                var lengkap = terisi === PARAMETER.length;
                hasilNips.textContent = lengkap ? total : '-';
                hasilInterpretasi.textContent = lengkap ? interpretasi(total) : '-';
            }

            PARAMETER.forEach(function (nama) {
                Array.prototype.forEach.call(document.querySelectorAll('input[name="' + nama + '"]'), function (el) {
                    el.addEventListener('change', syncTotal);
                });
            });

            syncTotal();
        });
    </script>

@endsection