@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Asesmen Kebutuhan Edukasi';
        $subtitleForm = 'Penilaian kesiapan pasien belajar: pendidikan, bicara & bahasa, kebudayaan, hambatan dan kebutuhan belajar.';
        $routeName = null;
        $routeUrl = url('emr/form/asesmen_kebutuhan_edukasi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        // Checkbox group disimpan sebagai JSON array pada satu variabel.
        $grup = fn($key) => json_decode((string) old($key, $emr_data[$key] ?? ''), true) ?: [];

        $nilaiKebudayaan = old('nilai_kebudayaan', $emr_data['nilai_kebudayaan'] ?? '');
        $kemampuanBicara = old('kemampuan_bicara', $emr_data['kemampuan_bicara'] ?? '');

        $grupFields = [
            'hambatan_belajar' => 'Hambatan Belajar',
            'kebutuhan_belajar' => 'Kebutuhan Belajar',
            'alasan_asesmen_ulang' => 'Alasan Asesmen Ulang',
            'respon_emosi_edukasi' => 'Respon Emosi',
        ];
        $grupKeys = [
            'hambatan_belajar' => 'hambatan_belajar',
            'kebutuhan_belajar' => 'kebutuhan_belajar',
            'alasan_asesmen_ulang' => 'alasan_asesmen_ulang',
            'respon_emosi_edukasi' => 'respon_emosi_edukasi',
        ];

        $headers = [
            'tingkat_pendidikan' => 'Pendidikan',
            'kemampuan_baca' => 'Kemampuan Baca',
            'sedia_terima_edukasi' => 'Sedia Edukasi',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Edukasi"
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
                slug="asesmen_kebutuhan_edukasi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= KEMAMPUAN PASIEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kemampuan Pasien</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Tingkat Pendidikan <span class="text-red-500">*</span></div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach (\App\Helpers\SelectOption::get('tingkat_pendidikan_edukasi') as $opt)
                                <?php $id = 'tp_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" id="{{ $id }}" name="tingkat_pendidikan"
                                           value="{{ $opt['value'] }}"
                                           @checked((string) $v('tingkat_pendidikan') === (string) $opt['value'])
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('tingkat_pendidikan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="font-bold text-slate-800 mb-2">Kemampuan Bicara <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('kemampuan_bicara') as $opt)
                                    <?php $id = 'kb_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="kemampuan_bicara"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('kemampuan_bicara') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('kemampuan_bicara')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="font-bold text-slate-800 mb-2">Kemampuan Baca <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('ada_tidak') as $opt)
                                    <?php $id = 'kbaca_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="kemampuan_baca"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('kemampuan_baca') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('kemampuan_baca')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="jenis_bahasa" class="block font-bold text-slate-800 mb-1.5">
                                Jenis Bahasa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="jenis_bahasa" name="jenis_bahasa" maxlength="100"
                                   value="{{ $v('jenis_bahasa') }}"
                                   placeholder="Contoh: Bahasa Indonesia / Bahasa Bugis"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                            @error('jenis_bahasa')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div id="wrap-penerjemah" class="{{ $kemampuanBicara === 'Tidak' ? '' : 'hidden' }}">
                            <div class="font-bold text-slate-800 mb-2">Perlu Penerjemah <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('perlu_penerjemah') as $opt)
                                    <?php $id = 'pp_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="perlu_penerjemah"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('perlu_penerjemah') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('perlu_penerjemah')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Nilai Kebudayaan <span class="text-red-500">*</span></div>
                        <div class="flex flex-wrap gap-3">
                            @foreach (\App\Helpers\SelectOption::get('ada_tidak') as $opt)
                                <?php $id = 'nk_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" id="{{ $id }}" name="nilai_kebudayaan"
                                           value="{{ $opt['value'] }}"
                                           @checked((string) $v('nilai_kebudayaan') === (string) $opt['value'])
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('nilai_kebudayaan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Field kondisional: hanya bila nilai kebudayaan = ada --}}
                    <div id="wrap-kebudayaan" class="{{ $nilaiKebudayaan === 'ada' ? '' : 'hidden' }}">
                        <label for="keterangan_kebudayaan" class="block font-bold text-slate-800 mb-1.5">
                            Keterangan Nilai Kebudayaan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="keterangan_kebudayaan" name="keterangan_kebudayaan" rows="2" maxlength="500"
                                  placeholder="Nilai kebudayaan yang perlu dihormati saat asuhan"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('keterangan_kebudayaan') }}</textarea>
                        @error('keterangan_kebudayaan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= HAMBATAN & KEBUTUHAN BELAJAR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Hambatan &amp; Kebutuhan Belajar</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    @foreach (['hambatan_belajar', 'kebutuhan_belajar'] as $grupKey)
                        <?php
                            $terpilih = $grup($grupKey);
                        ?>
                        <div>
                            <div class="font-bold text-slate-800 mb-2">
                                {{ $grupFields[$grupKey] }} <span class="text-red-500">*</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach (\App\Helpers\SelectOption::get($grupKeys[$grupKey]) as $opt)
                                    <?php $id = $grupKey.'_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="checkbox" id="{{ $id }}" name="{{ $grupKey }}[]"
                                               value="{{ $opt['value'] }}"
                                               @checked(in_array((string) $opt['value'], array_map('strval', $terpilih), true))
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error($grupKey)
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= KESEDIAAN & MATERI EDUKASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kesediaan &amp; Materi Edukasi</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Sedia Terima Edukasi <span class="text-red-500">*</span></div>
                        <div class="flex flex-wrap gap-3">
                            @foreach (\App\Helpers\SelectOption::get('sedia_terima_edukasi') as $opt)
                                <?php $id = 'ste_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" id="{{ $id }}" name="sedia_terima_edukasi"
                                           value="{{ $opt['value'] }}"
                                           @checked((string) $v('sedia_terima_edukasi') === (string) $opt['value'])
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('sedia_terima_edukasi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Alasan Asesmen Ulang</div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach (\App\Helpers\SelectOption::get('alasan_asesmen_ulang') as $opt)
                                <?php
                                    $id = 'aau_'.md5($opt['value']);
                                    $terpilihAau = $grup('alasan_asesmen_ulang');
                                ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="checkbox" id="{{ $id }}" name="alasan_asesmen_ulang[]"
                                           value="{{ $opt['value'] }}"
                                           @checked(in_array((string) $opt['value'], array_map('strval', $terpilihAau), true))
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('alasan_asesmen_ulang')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Respon Emosi Saat Asesmen</div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            @foreach (\App\Helpers\SelectOption::get('respon_emosi_edukasi') as $opt)
                                <?php
                                    $id = 'ree_'.md5($opt['value']);
                                    $terpilihRee = $grup('respon_emosi_edukasi');
                                ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="checkbox" id="{{ $id }}" name="respon_emosi_edukasi[]"
                                           value="{{ $opt['value'] }}"
                                           @checked(in_array((string) $opt['value'], array_map('strval', $terpilihRee), true))
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('respon_emosi_edukasi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="materi_edukasi" class="block font-bold text-slate-800 mb-1.5">
                            Materi Edukasi yang Sudah Diberikan
                        </label>
                        <textarea id="materi_edukasi" name="materi_edukasi" rows="4" maxlength="2000"
                                  placeholder="Materi edukasi yang telah diberikan sebelumnya"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('materi_edukasi') }}</textarea>
                        @error('materi_edukasi')
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

            var bicara = document.getElementsByName('kemampuan_bicara');
            var wrapPenerjemah = document.getElementById('wrap-penerjemah');
            function syncPenerjemah() {
                tampilkan(wrapPenerjemah, nilaiRadio('kemampuan_bicara') === 'Tidak', 'block');
            }
            Array.prototype.forEach.call(bicara, function (el) {
                el.addEventListener('change', syncPenerjemah);
            });
            syncPenerjemah();

            var kebudayaan = document.getElementsByName('nilai_kebudayaan');
            var wrapKebudayaan = document.getElementById('wrap-kebudayaan');
            function syncKebudayaan() {
                tampilkan(wrapKebudayaan, nilaiRadio('nilai_kebudayaan') === 'ada', 'block');
            }
            Array.prototype.forEach.call(kebudayaan, function (el) {
                el.addEventListener('change', syncKebudayaan);
            });
            syncKebudayaan();
        });
    </script>

@endsection
