@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Asesmen Akhir Kehidupan';
        $subtitleForm = 'Kerangka waktu asesmen, gejala fisik terminal, dan keputusan paliatif pasien akhir kehidupan.';
        $routeName = null;
        $routeUrl = url('emr/form/asesmen_akhir_kehidupan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');
        $nyeriTerakhir = old('nyeri_terakhir', $emr_data['nyeri_terakhir'] ?? '');

        $headers = [
            'tanggal_asesmen_akhir' => 'Tanggal Asesmen',
            'kesadaran_terminal' => 'Kesadaran',
            'nyeri_terakhir' => 'Nyeri',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Akhir"
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
                slug="asesmen_akhir_kehidupan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= KERANGKA WAKTU ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kerangka Waktu Asesmen</span>
                </div>
                <div class="p-5 bg-white grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                    <div>
                        <label for="waktu_asesmen_awal" class="block font-bold text-slate-800 mb-1.5">
                            Tanggal Asesmen Awal Kehidupan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="waktu_asesmen_awal" name="waktu_asesmen_awal"
                               value="{{ $v('waktu_asesmen_awal') }}"
                               class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        @error('waktu_asesmen_awal')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="waktu_asesmen_akhir_hari" class="block font-bold text-slate-800 mb-1.5">
                            Tanggal Asesmen Akhir Kehidupan <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="waktu_asesmen_akhir_hari" name="waktu_asesmen_akhir_hari"
                               value="{{ $v('waktu_asesmen_akhir_hari') }}"
                               class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        <p class="mt-1 text-xs text-slate-400">Tidak boleh lebih awal dari tanggal asesmen awal.</p>
                        @error('waktu_asesmen_akhir_hari')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_asesmen_akhir" class="block font-bold text-slate-800 mb-1.5">
                            Tanggal Asesmen Ini <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="tanggal_asesmen_akhir" name="tanggal_asesmen_akhir"
                               value="{{ $v('tanggal_asesmen_akhir') }}"
                               class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        @error('tanggal_asesmen_akhir')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="waktu_asesmen_akhir" class="block font-bold text-slate-800 mb-1.5">
                            Waktu Asesmen Ini <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="waktu_asesmen_akhir" name="waktu_asesmen_akhir" step="60"
                               value="{{ $v('waktu_asesmen_akhir') ? substr((string) $v('waktu_asesmen_akhir'), 0, 5) : '' }}"
                               class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        @error('waktu_asesmen_akhir')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ================= KONDISI PASIEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kondisi Pasien</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Kesadaran Terminal <span class="text-red-500">*</span></div>
                        <div class="flex flex-wrap gap-3">
                            @foreach (\App\Helpers\SelectOption::get('kesadaran_kritis') as $opt)
                                <?php $id = 'kterminal_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" id="{{ $id }}" name="kesadaran_terminal"
                                           value="{{ $opt['value'] }}"
                                           @checked((string) $v('kesadaran_terminal') === (string) $opt['value'])
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('kesadaran_terminal')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="font-bold text-slate-800 mb-2">Nyeri Terakhir <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('ya_tidak_kapital') as $opt)
                                    <?php $id = 'nt_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="nyeri_terakhir"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) $v('nyeri_terakhir') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('nyeri_terakhir')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="wrap-skor-nyeri" class="{{ $nyeriTerakhir === 'Ya' ? '' : 'hidden' }}">
                            <label for="skor_nyeri" class="block font-bold text-slate-800 mb-1.5">
                                Skor Nyeri (0-10) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="skor_nyeri" name="skor_nyeri" min="0" max="10" step="1"
                                   value="{{ $v('skor_nyeri') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('skor_nyeri')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="gejala_terminal" class="block font-bold text-slate-800 mb-1.5">
                            Gejala Terminal <span class="text-red-500">*</span>
                        </label>
                        <textarea id="gejala_terminal" name="gejala_terminal" rows="4" maxlength="2000"
                                  placeholder="Gejala fisik terminal yang ditemukan"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('gejala_terminal') }}</textarea>
                        @error('gejala_terminal')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tindakan_paliatif" class="block font-bold text-slate-800 mb-1.5">
                            Tindakan Paliatif <span class="text-red-500">*</span>
                        </label>
                        <textarea id="tindakan_paliatif" name="tindakan_paliatif" rows="4" maxlength="2000"
                                  placeholder="Keputusan dan tindakan paliatif yang diambil"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('tindakan_paliatif') }}</textarea>
                        @error('tindakan_paliatif')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="catatan_keluarga" class="block font-bold text-slate-800 mb-1.5">Catatan Keluarga</label>
                        <textarea id="catatan_keluarga" name="catatan_keluarga" rows="3" maxlength="1000"
                                  placeholder="Catatan tambahan terkait keluarga (opsional)"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ $v('catatan_keluarga') }}</textarea>
                        @error('catatan_keluarga')
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

            var nyeri = document.getElementsByName('nyeri_terakhir');
            var wrapSkor = document.getElementById('wrap-skor-nyeri');
            function syncNyeri() {
                var el = document.querySelector('input[name="nyeri_terakhir"]:checked');
                tampilkan(wrapSkor, el && el.value === 'Ya', 'block');
            }
            Array.prototype.forEach.call(nyeri, function (el) {
                el.addEventListener('change', syncNyeri);
            });
            syncNyeri();
        });
    </script>

@endsection
