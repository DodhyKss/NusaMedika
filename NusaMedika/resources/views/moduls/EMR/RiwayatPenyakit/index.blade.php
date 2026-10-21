@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Riwayat Penyakit';
        $subtitleForm = 'Anamnesis riwayat penyakit: diagnosa, keluhan, riwayat sebelumnya & sekarang, blok infeksius, imunologi, terapi, dan alergi.';
        $routeName = null;
        $routeUrl = url('emr/form/riwayat_penyakit');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');
        $grup = fn($key) => json_decode((string) old($key, $emr_data[$key] ?? ''), true) ?: [];

        $diagnosaTerpilih = array_map('strval', $grup('diagnosa_medis'));
        $keluhanTerpilih = array_map('strval', $grup('keluhan'));
        $sebelumnyaTerpilih = array_map('strval', $grup('riwayat_penyakit_sebelumnya'));
        $menularTerpilih = array_map('strval', $grup('menular_melalui'));

        $infeksiusFlag = old('infeksius_flag', $emr_data['infeksius_flag'] ?? '');
        $imunologiFlag = old('imunologi_flag', $emr_data['imunologi_flag'] ?? '');

        $headers = [
            'riwayat_penyakit_sekarang' => 'Riwayat Penyakit Sekarang',
            'alergi' => 'Alergi',
            'riwayat_perawatan_berulang' => 'Perawatan Berulang',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Penyakit"
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
                slug="riwayat_penyakit"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= DIAGNOSA MEDIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Diagnosa Medis</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label for="diagnosa_medis" class="block font-bold text-slate-800 mb-1.5">
                        Pilih Diagnosa Medis <span class="text-red-500">*</span>
                    </label>
                    <select id="diagnosa_medis" name="diagnosa_medis[]" multiple
                            class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        @foreach ($icdList as $icd)
                            <option value="{{ $icd->icd_id }}" @selected(in_array((string) $icd->icd_id, $diagnosaTerpilih, true))>
                                {{ $icd->kode_diagnosa }} - {{ $icd->nama_diagnosa }}
                            </option>
                        @endforeach
                    </select>
                    @error('diagnosa_medis')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- ================= ANAMNESIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Anamnesis</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Keluhan <span class="text-red-500">*</span></div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach (\App\Helpers\SelectOption::get('keluhan_anamnesis') as $opt)
                                <?php $id = 'kl_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="checkbox" id="{{ $id }}" name="keluhan[]"
                                           value="{{ $opt['value'] }}"
                                           @checked(in_array((string) $opt['value'], $keluhanTerpilih, true))
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('keluhan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Riwayat Penyakit Sebelumnya <span class="text-red-500">*</span></div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach (\App\Helpers\SelectOption::get('riwayat_penyakit_sebelumnya') as $opt)
                                <?php $id = 'rps_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="checkbox" id="{{ $id }}" name="riwayat_penyakit_sebelumnya[]"
                                           value="{{ $opt['value'] }}"
                                           @checked(in_array((string) $opt['value'], $sebelumnyaTerpilih, true))
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('riwayat_penyakit_sebelumnya')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="riwayat_penyakit_sekarang" class="block font-bold text-slate-800 mb-1.5">
                            Riwayat Penyakit Sekarang <span class="text-red-500">*</span>
                        </label>
                        <textarea id="riwayat_penyakit_sekarang" name="riwayat_penyakit_sekarang" rows="4" maxlength="2000"
                                  placeholder="Ceritakan riwayat penyakit yang sedang diderita"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('riwayat_penyakit_sekarang') }}</textarea>
                        @error('riwayat_penyakit_sekarang')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= BLOK INFEKSIUS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Infeksius</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Apakah penyakitnya infeksius? <span class="text-red-500">*</span></div>
                        <div class="flex flex-wrap gap-3">
                            @foreach (\App\Helpers\SelectOption::get('flag_ya_tidak') as $opt)
                                <?php $id = 'inf_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" id="{{ $id }}" name="infeksius_flag"
                                           value="{{ $opt['value'] }}"
                                           @checked((string) $v('infeksius_flag') === (string) $opt['value'])
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('infeksius_flag')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="wrap-infeksius" class="{{ $infeksiusFlag === 'ya' ? '' : 'hidden' }} space-y-5">
                        <div>
                            <div class="font-bold text-slate-800 mb-2">Menular Melalui <span class="text-red-500">*</span></div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                @foreach (\App\Helpers\SelectOption::get('menular_melalui') as $opt)
                                    <?php $id = 'mm_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="checkbox" id="{{ $id }}" name="menular_melalui[]"
                                               value="{{ $opt['value'] }}"
                                               @checked(in_array((string) $opt['value'], $menularTerpilih, true))
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('menular_melalui')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="font-bold text-slate-800 mb-2">Memerlukan Isolasi? <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('flag_ya_tidak') as $opt)
                                    <?php $id = 'imi_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="infeksius_memerlukan_isolasi"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('infeksius_memerlukan_isolasi') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('infeksius_memerlukan_isolasi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="infeksius_hasil_penunjang" class="block font-bold text-slate-800 mb-1.5">
                                Hasil Penunjang <span class="text-red-500">*</span>
                            </label>
                            <textarea id="infeksius_hasil_penunjang" name="infeksius_hasil_penunjang" rows="3" maxlength="1000"
                                      placeholder="Hasil pemeriksaan penunjang yang mendukung"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('infeksius_hasil_penunjang') }}</textarea>
                            @error('infeksius_hasil_penunjang')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= BLOK IMUNOLOGI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Imunologi</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Apakah ada masalah imunologi? <span class="text-red-500">*</span></div>
                        <div class="flex flex-wrap gap-3">
                            @foreach (\App\Helpers\SelectOption::get('flag_ya_tidak') as $opt)
                                <?php $id = 'imf_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" id="{{ $id }}" name="imunologi_flag"
                                           value="{{ $opt['value'] }}"
                                           @checked((string) $v('imunologi_flag') === (string) $opt['value'])
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('imunologi_flag')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="wrap-imunologi" class="{{ $imunologiFlag === 'ya' ? '' : 'hidden' }} space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <div class="font-bold text-slate-800 mb-2">Memerlukan Isolasi? <span class="text-red-500">*</span></div>
                                <div class="flex flex-wrap gap-3">
                                    @foreach (\App\Helpers\SelectOption::get('flag_ya_tidak') as $opt)
                                        <?php $id = 'imi2_'.md5($opt['value']); ?>
                                        <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                            <input type="radio" id="{{ $id }}" name="imunologi_memerlukan_isolasi"
                                                   value="{{ $opt['value'] }}"
                                                   @checked((string) $v('imunologi_memerlukan_isolasi') === (string) $opt['value'])
                                                   class="rounded text-cyan-600 focus:ring-cyan-500">
                                            <span class="text-slate-700">{{ $opt['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('imunologi_memerlukan_isolasi')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <div class="font-bold text-slate-800 mb-2">Pembatasan Pengunjung? <span class="text-red-500">*</span></div>
                                <div class="flex flex-wrap gap-3">
                                    @foreach (\App\Helpers\SelectOption::get('flag_ya_tidak') as $opt)
                                        <?php $id = 'imp_'.md5($opt['value']); ?>
                                        <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                            <input type="radio" id="{{ $id }}" name="imunologi_pembatasan_pengunjung"
                                                   value="{{ $opt['value'] }}"
                                                   @checked((string) $v('imunologi_pembatasan_pengunjung') === (string) $opt['value'])
                                                   class="rounded text-cyan-600 focus:ring-cyan-500">
                                            <span class="text-slate-700">{{ $opt['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('imunologi_pembatasan_pengunjung')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="imunologi_hasil_penunjang" class="block font-bold text-slate-800 mb-1.5">
                                Hasil Penunjang <span class="text-red-500">*</span>
                            </label>
                            <textarea id="imunologi_hasil_penunjang" name="imunologi_hasil_penunjang" rows="3" maxlength="1000"
                                      placeholder="Hasil pemeriksaan imunologi"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('imunologi_hasil_penunjang') }}</textarea>
                            @error('imunologi_hasil_penunjang')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= RIWAYAT TERAPI & LAINNYA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Terapi &amp; Lainnya</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <div class="font-bold text-slate-800 mb-2">Riwayat Kemoterapi <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('flag_ya_tidak') as $opt)
                                    <?php $id = 'kem_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="riwayat_kemoterapi"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('riwayat_kemoterapi') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('riwayat_kemoterapi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="font-bold text-slate-800 mb-2">Riwayat Radioterapi <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('flag_ya_tidak') as $opt)
                                    <?php $id = 'rad_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="riwayat_radioterapi"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('riwayat_radioterapi') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('riwayat_radioterapi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="font-bold text-slate-800 mb-2">Riwayat Operasi <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('flag_ya_tidak') as $opt)
                                    <?php $id = 'op_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="riwayat_operasi"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('riwayat_operasi') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('riwayat_operasi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="font-bold text-slate-800 mb-2">Riwayat Perawatan Berulang <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('flag_ya_tidak') as $opt)
                                    <?php $id = 'rpb_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="riwayat_perawatan_berulang"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('riwayat_perawatan_berulang') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('riwayat_perawatan_berulang')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="vaksin_covid" class="block font-bold text-slate-800 mb-1.5">Tanggal Vaksin COVID (terakhir)</label>
                        <input type="date" id="vaksin_covid" name="vaksin_covid"
                               value="{{ $v('vaksin_covid') }}"
                               class="w-full md:w-1/3 border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        @error('vaksin_covid')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="alergi" class="block font-bold text-slate-800 mb-1.5">
                            Alergi <span class="text-red-500">*</span>
                        </label>
                        <textarea id="alergi" name="alergi" rows="2" maxlength="1000"
                                  placeholder="Tulis daftar alergi; bila tidak ada tulis Tidak Ada"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('alergi') }}</textarea>
                        @error('alergi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="catatan_riwayat" class="block font-bold text-slate-800 mb-1.5">Catatan Riwayat</label>
                        <textarea id="catatan_riwayat" name="catatan_riwayat" rows="3" maxlength="1000"
                                  placeholder="Catatan tambahan (opsional)"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('catatan_riwayat') }}</textarea>
                        @error('catatan_riwayat')
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

            var infeksius = document.getElementsByName('infeksius_flag');
            var wrapInfeksius = document.getElementById('wrap-infeksius');
            function syncInfeksius() {
                tampilkan(wrapInfeksius, nilaiRadio('infeksius_flag') === 'ya', 'block');
            }
            Array.prototype.forEach.call(infeksius, function (el) {
                el.addEventListener('change', syncInfeksius);
            });
            syncInfeksius();

            var imunologi = document.getElementsByName('imunologi_flag');
            var wrapImunologi = document.getElementById('wrap-imunologi');
            function syncImunologi() {
                tampilkan(wrapImunologi, nilaiRadio('imunologi_flag') === 'ya', 'block');
            }
            Array.prototype.forEach.call(imunologi, function (el) {
                el.addEventListener('change', syncImunologi);
            });
            syncImunologi();
        });
    </script>

@endsection
