@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Pengkajian Keperawatan Kritis';
        $subtitleForm = 'Pengkajian ABCDE pasien kritis: airway, breathing, circulation,disability, dan exposure.';
        $routeName = null;
        $routeUrl = url('emr/form/pengkajian_keperawatan_kritis');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');
        $grup = fn($key) => json_decode((string) old($key, $emr_data[$key] ?? ''), true) ?: [];

        $asalMasuk = old('asal_masuk', $emr_data['asal_masuk'] ?? '');
        $metodeNyeri = old('metode_skala_nyeri_kritis', $emr_data['metode_skala_nyeri_kritis'] ?? 'VAS');
        $suaraNafas = $grup('suara_nafas_terdengar');

        $headers = [
            'asal_masuk' => 'Asal Masuk',
            'nilai_kritis' => 'Nilai Kritis',
            'gcs_jumlah' => 'GCS',
            'skor_nyeri_kritis' => 'Skor Nyeri',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Pengkajian Kritis"
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
                slug="pengkajian_keperawatan_kritis"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= DATA MASUK ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Data Masuk</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="font-bold text-slate-800 mb-2">Asal Masuk <span class="text-red-500">*</span></div>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach (\App\Helpers\SelectOption::get('asal_masuk_kritis') as $opt)
                                    <?php $id = 'am_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="asal_masuk"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('asal_masuk') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('asal_masuk')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="wrap-asal-detail" class="{{ $asalMasuk === 'Lainnya' ? '' : 'hidden' }}">
                            <label for="asal_masuk_detail" class="block font-bold text-slate-800 mb-1.5">
                                Rincian Asal Masuk <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="asal_masuk_detail" name="asal_masuk_detail" maxlength="200"
                                   value="{{ $v('asal_masuk_detail') }}"
                                   placeholder="Tuliskan asal masuk lainnya"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                            @error('asal_masuk_detail')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="diagnosa_medis_kritis" class="block font-bold text-slate-800 mb-1.5">
                            Diagnosa Medis <span class="text-red-500">*</span>
                        </label>
                        <select id="diagnosa_medis_kritis" name="diagnosa_medis_kritis"
                                class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            <option value="">-- Pilih Diagnosa Medis --</option>
                            @foreach ($icdList as $icd)
                                <option value="{{ $icd->icd_id }}" @selected((string) $v('diagnosa_medis_kritis') === (string) $icd->icd_id)>
                                    {{ $icd->kode_diagnosa }} - {{ $icd->nama_diagnosa }}
                                </option>
                            @endforeach
                        </select>
                        @error('diagnosa_medis_kritis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= A - AIRWAY ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">A - Airway</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="font-bold text-slate-800 mb-2">Jalan Nafas <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-2">
                                @foreach (\App\Helpers\SelectOption::get('jalan_nafas_kritis') as $opt)
                                    <?php $id = 'jn_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="jalan_nafas"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('jalan_nafas') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('jalan_nafas')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <div class="font-bold text-slate-800 mb-2">Deviasi Trakea <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('ya_tidak_kapital') as $opt)
                                    <?php $id = 'dt_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="deviasi_trakea"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('deviasi_trakea') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('deviasi_trakea')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= B - BREATHING ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">B - Breathing</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Pernapasan <span class="text-red-500">*</span></div>
                        <div class="flex flex-wrap gap-3">
                            @foreach (\App\Helpers\SelectOption::get('pernapasan_kritis') as $opt)
                                <?php $id = 'pk_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" id="{{ $id }}" name="pernapasan_kritis"
                                           value="{{ $opt['value'] }}"
                                           @checked((string) $v('pernapasan_kritis') === (string) $opt['value'])
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('pernapasan_kritis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Suara Napas Terdengar <span class="text-red-500">*</span></div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach (\App\Helpers\SelectOption::get('suara_nafas') as $opt)
                                <?php $id = 'sn_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="checkbox" id="{{ $id }}" name="suara_nafas_terdengar[]"
                                           value="{{ $opt['value'] }}"
                                           @checked(in_array((string) $opt['value'], array_map('strval', $suaraNafas), true))
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('suara_nafas_terdengar')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= C - CIRCULATION ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">C - Circulation</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="font-bold text-slate-800 mb-2">Sirkulasi dan Perfusi <span class="text-red-500">*</span></div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        @foreach (\App\Helpers\SelectOption::get('sirkulasi_perfusi') as $opt)
                            <?php $id = 'sp_'.md5($opt['value']); ?>
                            <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                <input type="radio" id="{{ $id }}" name="sirkulasi_perfusi"
                                       value="{{ $opt['value'] }}"
                                       @checked((string) $v('sirkulasi_perfusi') === (string) $opt['value'])
                                       class="rounded text-cyan-600 focus:ring-cyan-500">
                                <span class="text-slate-700">{{ $opt['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('sirkulasi_perfusi')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- ================= D - DISABILITY ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">D - Disability</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="font-bold text-slate-800 mb-2">Kesadaran <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-2">
                                @foreach (\App\Helpers\SelectOption::get('kesadaran_kritis') as $opt)
                                    <?php $id = 'kk_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="kesadaran_kritis"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('kesadaran_kritis') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('kesadaran_kritis')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="font-bold text-slate-800 mb-2">Nilai Kritis <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('nilai_kritis') as $opt)
                                    <?php $id = 'nkritis_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="nilai_kritis"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('nilai_kritis') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('nilai_kritis')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 border border-slate-200 rounded-lg">
                            <div class="font-bold text-slate-800 mb-3">Glasgow Coma Scale <span class="text-red-500">*</span></div>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach (['gcs_e' => 'Eye (1-4)', 'gcs_m' => 'Motorik (1-6)', 'gcs_v' => 'Verbal (1-5)'] as $nama => $label)
                                    <div>
                                        <label for="{{ $nama }}" class="block text-xs font-medium text-slate-600 mb-1">{{ $label }}</label>
                                        <input type="number" id="{{ $nama }}" name="{{ $nama }}"
                                               min="1"
                                               max="{{ $nama === 'gcs_m' ? 6 : ($nama === 'gcs_v' ? 5 : 4) }}"
                                               step="1"
                                               value="{{ $v($nama) }}"
                                               class="w-full border border-slate-300 rounded px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                        @error($nama)
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 px-3 py-2 bg-slate-50 border border-slate-200 rounded">
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">Total GCS</div>
                                <div id="gcs_jumlah_view" class="text-sm font-semibold text-slate-700">
                                    {{ $v('gcs_jumlah') !== '' ? $v('gcs_jumlah') : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="p-4 border border-slate-200 rounded-lg">
                            <div class="font-bold text-slate-800 mb-3">Skor Nyeri</div>
                            <div class="mb-3">
                                <label for="metode_skala_nyeri_kritis" class="block text-xs font-medium text-slate-600 mb-1">Metode Skala</label>
                                <select id="metode_skala_nyeri_kritis" name="metode_skala_nyeri_kritis"
                                        class="select2 w-full border border-slate-300 rounded px-3 py-2 bg-slate-50 focus:bg-white outline-none text-slate-700">
                                    {!! \App\Helpers\SelectOption::render('metode_skala_nyeri', $metodeNyeri, '-- Pilih Metode --') !!}
                                </select>
                                @error('metode_skala_nyeri_kritis')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div id="wrap-vas" class="{{ $metodeNyeri === 'FLACC' ? 'hidden' : '' }}">
                                <label for="skor_vas_kritis" class="block text-xs font-medium text-slate-600 mb-1">VAS (0-10)</label>
                                <input type="number" id="skor_vas_kritis" name="skor_vas_kritis"
                                       min="0" max="10" step="1"
                                       value="{{ $v('skor_vas_kritis') }}"
                                       class="w-full border border-slate-300 rounded px-3 py-2 bg-slate-50 focus:bg-white outline-none text-slate-700">
                                @error('skor_vas_kritis')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div id="wrap-flacc" class="{{ $metodeNyeri === 'FLACC' ? '' : 'hidden' }} space-y-2">
                                @foreach ($itemFlacc as $nama => $butir)
                                    <div>
                                        <div class="text-[11px] font-medium text-slate-600 mb-1">{{ $butir['label'] }}</div>
                                        <div class="flex gap-2">
                                            @foreach ($butir['opsi'] as $opt)
                                                <?php $idFlacc = 'flacc_'.md5($nama.$opt['value']); ?>
                                                <label for="{{ $idFlacc }}" class="flex-1 text-center px-2 py-1.5 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 text-xs">
                                                    <input type="radio" id="{{ $idFlacc }}" name="{{ $nama }}"
                                                           value="{{ $opt['value'] }}"
                                                           @checked((string) $v($nama) === (string) $opt['value'])
                                                           class="hidden">
                                                    <span class="text-slate-600">{{ $opt['label'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error($nama)
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-3 px-3 py-2 bg-slate-50 border border-slate-200 rounded">
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">Skor Nyeri Kritis</div>
                                <div id="skor_nyeri_view" class="text-sm font-semibold text-slate-700">
                                    {{ $v('skor_nyeri_kritis') !== '' ? $v('skor_nyeri_kritis') : '-' }}
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-slate-400">
                                Skor dihitung ulang di server; butir FLACC tidak disimpan terpisah.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= E - EXPOSURE & HASIL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">E - Exposure &amp; Hasil Pengkajian</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <label for="eksposisi_kritis" class="block font-bold text-slate-800 mb-1.5">
                            Eksposisi (Findings Objektif) <span class="text-red-500">*</span>
                        </label>
                        <textarea id="eksposisi_kritis" name="eksposisi_kritis" rows="5" maxlength="2000"
                                  placeholder="Ringkas temuan objektif: konjungtiva, mukosa mulut, kulit &amp; turgor, abdomen."
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('eksposisi_kritis') }}</textarea>
                        @error('eksposisi_kritis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="masalah_keperawatan_kritis" class="block font-bold text-slate-800 mb-1.5">
                                Masalah Keperawatan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="masalah_keperawatan_kritis" name="masalah_keperawatan_kritis" maxlength="255"
                                   value="{{ $v('masalah_keperawatan_kritis') }}"
                                   placeholder="Masalah keperawatan utama pasien"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                            @error('masalah_keperawatan_kritis')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="alergi" class="block font-bold text-slate-800 mb-1.5">Alergi</label>
                            <textarea id="alergi" name="alergi" rows="3" maxlength="500"
                                      placeholder="Alergi yang diketahui (bila tidak ada tulis Tidak Ada)"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('alergi') }}</textarea>
                            @error('alergi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer w-max">
                            <input type="checkbox" id="dpo" name="dpo" value="Ya"
                                   @checked(in_array((string) $v('dpo'), ['1', 'Ya'], true))
                                   class="rounded text-cyan-600 focus:ring-cyan-500">
                            <span class="text-slate-700">Dalam Pengaruh Obat (DPO)</span>
                        </label>
                        @error('dpo')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="catatan_kritis" class="block font-bold text-slate-800 mb-1.5">Catatan Kritis</label>
                        <textarea id="catatan_kritis" name="catatan_kritis" rows="4" maxlength="2000"
                                  placeholder="Narasi kritis tim anestesi / perawat"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('catatan_kritis') }}</textarea>
                        @error('catatan_kritis')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tampil/sembunyi harus lewat style.display + lepas class "hidden"
            // (lihat AGENTS.md - utility .hidden Tailwind bisa dikalahkan utility
            // display lain, jadi keduanya harus dipasang).
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

            function nilaiRadio(nama) {
                var el = document.querySelector('input[name="' + nama + '"]:checked');
                return el ? el.value : '';
            }

            var asalMasuk = document.getElementsByName('asal_masuk');
            var wrapAsal = document.getElementById('wrap-asal-detail');
            function syncAsal() {
                tampilkan(wrapAsal, nilaiRadio('asal_masuk') === 'Lainnya', 'block');
            }
            Array.prototype.forEach.call(asalMasuk, function (el) {
                el.addEventListener('change', syncAsal);
            });
            syncAsal();

            var metode = document.getElementById('metode_skala_nyeri_kritis');
            var wrapVas = document.getElementById('wrap-vas');
            var wrapFlacc = document.getElementById('wrap-flacc');
            function hitungNyeri() {
                var val = metode ? metode.value : 'VAS';
                tampilkan(wrapVas, val !== 'FLACC', 'block');
                tampilkan(wrapFlacc, val === 'FLACC', 'block');

                var out = document.getElementById('skor_nyeri_view');
                if (!out) return;

                var skor = null;
                if (val === 'FLACC') {
                    var total = 0;
                    var lengkap = true;
                    ['flacc_wajah', 'flacc_kaki', 'flacc_aktivitas', 'flacc_menangis', 'flacc_konsolabilitas']
                        .forEach(function (nama) {
                            var v = nilaiRadio(nama);
                            if (v === '') { lengkap = false; return; }
                            total += parseInt(v, 10);
                        });
                    skor = lengkap ? total : null;
                } else {
                    var vas = document.getElementById('skor_vas_kritis');
                    if (vas && vas.value !== '') {
                        var n = parseInt(vas.value, 10);
                        skor = isNaN(n) ? null : n;
                    }
                }
                out.textContent = skor === null ? '-' : skor;
            }
            if (metode) {
                metode.addEventListener('change', hitungNyeri);
            }
            var inputVas = document.getElementById('skor_vas_kritis');
            if (inputVas) {
                inputVas.addEventListener('input', hitungNyeri);
            }
            ['flacc_wajah', 'flacc_kaki', 'flacc_aktivitas', 'flacc_menangis', 'flacc_konsolabilitas']
                .forEach(function (nama) {
                    Array.prototype.forEach.call(document.getElementsByName(nama), function (el) {
                        el.addEventListener('change', hitungNyeri);
                    });
                });
            hitungNyeri();

            var outGcs = document.getElementById('gcs_jumlah_view');
            function hitungGcs() {
                if (!outGcs) return;
                var total = 0;
                var lengkap = true;
                ['gcs_e', 'gcs_m', 'gcs_v'].forEach(function (id) {
                    var el = document.getElementById(id);
                    var v = el ? parseInt(el.value || '', 10) : NaN;
                    if (isNaN(v)) { lengkap = false; return; }
                    total += v;
                });
                outGcs.textContent = lengkap ? total : '-';
            }
            ['gcs_e', 'gcs_m', 'gcs_v'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.addEventListener('input', hitungGcs);
            });
            hitungGcs();
        });
    </script>

@endsection
