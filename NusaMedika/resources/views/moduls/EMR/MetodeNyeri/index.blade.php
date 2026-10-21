@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Metode Nyeri';
        $subtitleForm = 'Pemilih metode penskalaan VAS/NRS atau FLACC beserta skor dan interpretasinya.';
        $routeName = null;
        $routeUrl = url('emr/form/metode_nyeri');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $v = fn ($key) => old($key, $emr_data[$key] ?? '');
        $metode = old('metode_skala_nyeri', $emr_data['metode_skala_nyeri'] ?? 'VAS');

        $flaccValues = [];
        foreach (array_keys(\App\Helpers\NyeriHelper::flaccItems()) as $butir) {
            $flaccValues[$butir] = $v($butir);
        }

        $warnaInterpretasi = [
            'Tidak Nyeri' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Nyeri Ringan' => 'bg-amber-50 text-amber-700 border-amber-200',
            'Nyeri Sedang' => 'bg-orange-50 text-orange-700 border-orange-200',
            'Nyeri Berat' => 'bg-red-50 text-red-700 border-red-200',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Metode Nyeri"
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
                slug="metode_nyeri"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="[
                    'metode_skala_nyeri' => 'Metode',
                    'skor_nyeri' => 'Skor VAS',
                    'skor_flacc' => 'Skor FLACC',
                    'interpretasi_nyeri' => 'Interpretasi',
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= METODE SKALA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Metode Penilaian Skala Nyeri</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div>
                        <span class="block font-bold text-slate-800 mb-2">
                            Metode Skala Nyeri <span class="text-red-500">*</span>
                        </span>
                        <div class="flex flex-wrap gap-4">
                            @foreach (\App\Helpers\SelectOption::get('metode_skala_nyeri') as $opsi)
                                <label for="metode36-{{ $opsi['value'] }}" class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" id="metode36-{{ $opsi['value'] }}" name="metode_skala_nyeri"
                                           value="{{ $opsi['value'] }}" @checked($metode === $opsi['value'])>
                                    <span class="text-slate-700">{{ $opsi['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('metode_skala_nyeri')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="panel-vas">
                        @include('moduls.EMR.PartialForm._vas_scale', ['value' => $v('skor_nyeri'), 'prefix' => 'vas36', 'required' => true])
                    </div>

                    <div id="panel-flacc">
                        @include('moduls.EMR.PartialForm._flacc_scale', ['values' => $flaccValues, 'idPrefix' => 'flacc36'])
                    </div>
                </div>
            </div>

            {{-- ================= HASIL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Hasil Penilaian</span>
                </div>
                <div class="p-5 bg-white">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Skor Nyeri (VAS)</div>
                            <div id="hasil_vas" class="text-xl font-bold text-slate-700">
                                {{ old('skor_nyeri', $emr_data['skor_nyeri'] ?? '-') }}
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Total Skor FLACC</div>
                            <div id="hasil_flacc" class="text-xl font-bold text-slate-700">
                                {{ old('skor_flacc', $emr_data['skor_flacc'] ?? '-') }}
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Interpretasi Skor Nyeri</div>
                            <div class="mt-1">
                                <span id="hasil_interpretasi"
                                      class="inline-block border rounded px-2.5 py-1 text-sm font-semibold {{ $warnaInterpretasi[(string) $v('interpretasi_nyeri')] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ $v('interpretasi_nyeri') !== '' ? $v('interpretasi_nyeri') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-slate-400">
                        Skor FLACC dan interpretasi dihitung ulang di server dari jawaban mentah,
                        bukan dari nilai yang dikirim browser.
                    </p>

                    <div class="mt-5">
                        <label for="keterangan" class="block font-bold text-slate-800 mb-1.5">Keterangan</label>
                        <textarea id="keterangan" name="keterangan" rows="3" maxlength="1000"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('keterangan') }}</textarea>
                        @error('keterangan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tampil/sembunyi harus lewat style.display DAN class "hidden" — utility
            // display lain di Tailwind bisa mengalahkan .hidden (lihat AGENTS.md).
            function tampilkan(el, tampil, display) {
                if (!el) return;
                if (tampil) {
                    el.classList.remove('hidden');
                    el.style.display = display;
                } else {
                    el.classList.add('hidden');
                    el.style.display = 'none';
                }
            }

            var KATEGORI = [
                { maks: 0, label: 'Tidak Nyeri', kelas: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
                { maks: 3, label: 'Nyeri Ringan', kelas: 'bg-amber-50 text-amber-700 border-amber-200' },
                { maks: 6, label: 'Nyeri Sedang', kelas: 'bg-orange-50 text-orange-700 border-orange-200' },
                { maks: 10, label: 'Nyeri Berat', kelas: 'bg-red-50 text-red-700 border-red-200' }
            ];

            var radioMetode = document.querySelectorAll('input[name="metode_skala_nyeri"]');
            var panelVas = document.getElementById('panel-vas');
            var panelFlacc = document.getElementById('panel-flacc');
            var hasilVas = document.getElementById('hasil_vas');
            var hasilFlacc = document.getElementById('hasil_flacc');
            var hasilInterpretasi = document.getElementById('hasil_interpretasi');

            var FLACC = ['flacc_wajah', 'flacc_kaki', 'flacc_aktivitas', 'flacc_menangis', 'flacc_konsolabilitas'];

            function nilaiRadio(list) {
                for (var i = 0; i < list.length; i++) {
                    if (list[i].checked) return list[i].value;
                }
                return '';
            }

            function terisiRadio(nama) {
                var el = document.querySelector('input[name="' + nama + '"]:checked');
                return el ? el.value : null;
            }

            function kategoriDari(skor) {
                if (skor === null || isNaN(skor)) return null;
                for (var i = 0; i < KATEGORI.length; i++) {
                    if (skor <= KATEGORI[i].maks) return KATEGORI[i];
                }
                return null;
            }

            function syncSkor() {
                var metode = nilaiRadio(radioMetode);

                tampilkan(panelVas, metode === 'VAS', 'block');
                tampilkan(panelFlacc, metode === 'FLACC', 'block');

                // Hanya tampilan; server menghitung ulang sendiri.
                var total = null;
                if (metode === 'VAS') {
                    var vas = terisiRadio('skor_nyeri');
                    total = vas === null ? null : parseInt(vas, 10);
                    hasilVas.textContent = total === null ? '-' : total;
                    hasilFlacc.textContent = '-';
                } else {
                    hasilVas.textContent = '-';
                    var jumlah = 0;
                    var terisi = 0;
                    FLACC.forEach(function (nama) {
                        var v = terisiRadio(nama);
                        if (v !== null) { jumlah += parseInt(v, 10) || 0; terisi++; }
                    });
                    total = terisi === 5 ? jumlah : null;
                    hasilFlacc.textContent = total === null ? '-' : total;
                }

                var kategori = kategoriDari(total);
                if (kategori) {
                    hasilInterpretasi.textContent = kategori.label;
                    hasilInterpretasi.className = 'inline-block border rounded px-2.5 py-1 text-sm font-semibold ' + kategori.kelas;
                } else {
                    hasilInterpretasi.textContent = '-';
                    hasilInterpretasi.className = 'inline-block border rounded px-2.5 py-1 text-sm font-semibold bg-slate-100 text-slate-600 border-slate-200';
                }
            }

            Array.prototype.forEach.call(radioMetode, function (el) { el.addEventListener('change', syncSkor); });
            Array.prototype.forEach.call(document.querySelectorAll('input[name="skor_nyeri"]'), function (el) {
                el.addEventListener('change', syncSkor);
            });
            FLACC.forEach(function (nama) {
                Array.prototype.forEach.call(document.querySelectorAll('input[name="' + nama + '"]'), function (el) {
                    el.addEventListener('change', syncSkor);
                });
            });

            syncSkor();
        });
    </script>

@endsection