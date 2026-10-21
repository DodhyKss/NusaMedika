@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Respon Emosi & Status Mental';
        $subtitleForm = 'Asesmen psikososial dasar: respon emosi, status mental, dan kebutuhan perlindungan.';
        $routeName = null;
        $routeUrl = url('emr/form/respon_emosi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $grup = function ($key) {
            $nilai = json_decode((string) old($key, $emr_data[$key] ?? ''), true);

            return is_array($nilai) ? array_map('strval', $nilai) : [];
        };

        // `kebutuhan_perlindungan` disimpan sebagai {"butuh":"ya","butir":[...]}.
        $perlindungan = json_decode((string) old('kebutuhan_perlindungan', $emr_data['kebutuhan_perlindungan'] ?? ''), true);
        if (! is_array($perlindungan)) {
            $perlindungan = ['butuh' => '', 'butir' => []];
        }
        $butuhPerlindungan = $perlindungan['butuh'] ?? '';
        $butirTerpilih = array_map('strval', $perlindungan['butir'] ?? []);

        $responTerpilih = $grup('respon_emosi');
        $mentalTerpilih = $grup('status_mental');

        $headers = [
            'kebutuhan_teridentifikasi' => 'Kebutuhan Teridentifikasi',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Respon Emosi"
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
                slug="respon_emosi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= RESPON EMOSI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Respon Emosi</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        @foreach (\App\Helpers\SelectOption::get('respon_emosi') as $opt)
                            <?php $id = 're_'.md5($opt['value']); ?>
                            <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" id="{{ $id }}" name="respon_emosi[]"
                                       value="{{ $opt['value'] }}"
                                       @checked(in_array((string) $opt['value'], $responTerpilih, true))
                                       class="rounded text-cyan-600 focus:ring-cyan-500">
                                <span class="text-slate-700">{{ $opt['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('respon_emosi')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- ================= STATUS MENTAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Status Mental</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach (\App\Helpers\SelectOption::get('status_mental') as $opt)
                            <?php $id = 'sm_'.md5($opt['value']); ?>
                            <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" id="{{ $id }}" name="status_mental[]"
                                       value="{{ $opt['value'] }}"
                                       @checked(in_array((string) $opt['value'], $mentalTerpilih, true))
                                       class="rounded text-cyan-600 focus:ring-cyan-500">
                                <span class="text-slate-700">{{ $opt['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('status_mental')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- ================= KEBUTUHAN PERLINDUNGAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kebutuhan Perlindungan</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">

                    <div>
                        <div class="font-bold text-slate-800 mb-2">Apakah ada kebutuhan perlindungan? <span class="text-red-500">*</span></div>
                        <div class="flex flex-wrap gap-3">
                            @foreach (\App\Helpers\SelectOption::get('flag_ya_tidak') as $opt)
                                <?php $id = 'kp_'.md5($opt['value']); ?>
                                <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                    <input type="radio" id="{{ $id }}" name="kebutuhan_perlindungan"
                                           value="{{ $opt['value'] }}"
                                           @checked((string) $butuhPerlindungan === (string) $opt['value'])
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('kebutuhan_perlindungan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="wrap-perlindungan" class="{{ $butuhPerlindungan === 'ya' ? '' : 'hidden' }} space-y-5">
                        <div>
                            <div class="font-bold text-slate-800 mb-2">Kebutuhan Teridentifikasi <span class="text-red-500">*</span></div>
                            <div class="flex flex-wrap gap-3">
                                @foreach (\App\Helpers\SelectOption::get('kebutuhan_teridentifikasi') as $opt)
                                    <?php $id = 'kt_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="radio" id="{{ $id }}" name="kebutuhan_teridentifikasi"
                                               value="{{ $opt['value'] }}"
                                               @checked((string) old('kebutuhan_teridentifikasi', $emr_data['kebutuhan_teridentifikasi'] ?? '') === (string) $opt['value'])
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('kebutuhan_teridentifikasi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="font-bold text-slate-800 mb-2">Identifikasi Kebutuhan Perlindungan</div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach (\App\Helpers\SelectOption::get('identifikasi_perlindungan') as $opt)
                                    <?php $id = 'ip_'.md5($opt['value']); ?>
                                    <label for="{{ $id }}" class="flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                                        <input type="checkbox" id="{{ $id }}" name="identifikasi_perlindungan[]"
                                               value="{{ $opt['value'] }}"
                                               @checked(in_array((string) $opt['value'], $butirTerpilih, true))
                                               class="rounded text-cyan-600 focus:ring-cyan-500">
                                        <span class="text-slate-700">{{ $opt['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('identifikasi_perlindungan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-400">
                                Pilihan di atas disimpan bersama flag ya/tidak pada satu variabel
                                <code>kebutuhan_perlindungan</code>.
                            </p>
                        </div>
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

            var kebutuhan = document.getElementsByName('kebutuhan_perlindungan');
            var wrapPerlindungan = document.getElementById('wrap-perlindungan');
            function syncPerlindungan() {
                var el = document.querySelector('input[name="kebutuhan_perlindungan"]:checked');
                tampilkan(wrapPerlindungan, el && el.value === 'ya', 'block');
            }
            Array.prototype.forEach.call(kebutuhan, function (el) {
                el.addEventListener('change', syncPerlindungan);
            });
            syncPerlindungan();
        });
    </script>

@endsection
