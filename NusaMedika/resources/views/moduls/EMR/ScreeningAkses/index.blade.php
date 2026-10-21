@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Screening Akses Vaskular';
        $subtitleForm = 'Screening kelayakan AV Shunt/Cimino vs CDL sebelum pemasangan/penggantian.';
        $routeName = null;
        $routeUrl = url('emr/form/screening_akses');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'jenis_spesimen' => 'Jenis Screening',
        ];

        $jawaban = ['Ya', 'Tidak'];
        $sel = function ($name, $list) use ($v) {
            $html = '<select name="'.$name.'" class="w-full border rounded-sm px-2 py-1.5 text-sm"><option value="">-- Pilih --</option>';
            foreach ($list as $opt) {
                $attr = $v($name) === $opt ? ' selected' : '';
                $html .= '<option value="'.$opt.'"'.$attr.'>'.$opt.'</option>';
            }
            return $html.'</select>';
        };

        $blokAv = [
            'Kondisi Akses' => [
                'aks_area_1' => 'Area AV shunt',
                'aks_area_2' => 'Fundus teraba',
                'aks_area_3' => 'Hematoma',
            ],
            'Steal Syndrome' => [
                'aks_steal_1' => 'Jari tangan',
                'aks_steal_2' => 'Nyeri',
                'aks_steal_3' => 'Baal',
                'aks_steal_4' => 'Tangan keriput',
                'aks_steal_5' => 'Aneurisma',
                'aks_steal_6' => 'Ulserasi',
                'aks_steal_7' => 'Frekuensi nyeri',
                'aks_steal_8' => 'Screening test',
                'aks_steal_9' => 'Oedema tangan',
            ],
        ];
        $blokCdl = [
            'Kondisi CDL, Exit Site & Kondisi Pasien' => [
                'aks_cdl_1' => 'Kepatenan',
                'aks_cdl_2' => 'Tutup',
                'aks_cdl_3' => 'Keretakan',
                'aks_cdl_4' => 'Lokasi',
                'aks_exit_1' => 'Tanda bahaya',
                'aks_exit_2' => 'Pembengkakan',
                'aks_exit_3' => 'Perdarahan',
                'aks_exit_4' => 'Posisi',
                'aks_exit_5' => 'Pemicu nyeri',
                'aks_exit_6' => 'Memar',
                'aks_pasien_1' => 'Benjolan',
            ],
            'Tanda CDL Disfungsi/Malfungsi & Terkait Pasien' => [
                'aks_disfungsi_1' => 'Tekanan arteri',
                'aks_disfungsi_2' => 'Tekanan vena',
                'aks_disfungsi_3' => 'Tekanan psikologis',
                'aks_nyeri_1' => 'Risiko perdarahan',
                'aks_nyeri_2' => 'Pernapasan',
                'aks_nyeri_3' => 'Jenis kateter',
                'aks_nyeri_4' => 'Antisipasi perdarahan',
            ],
        ];

    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Screening Akses"
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
                slug="screening_akses"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= JENIS SCREENING ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Jenis Screening</span>
                </div>
                <div class="p-5 bg-white text-sm max-w-xl">
                    <label class="block text-xs font-medium mb-1">Jenis Screening Akses <span class="text-red-500">*</span></label>
                    <select id="jenis_spesimen" name="jenis_spesimen" class="w-full border rounded-sm px-3 py-2 text-sm">
                        <option value="">-- Pilih Jenis --</option>
                        @foreach ($opsiJenis as $opt)
                            <option value="{{ $opt['value'] }}" {{ $v('jenis_spesimen') === $opt['value'] ? 'selected' : '' }}>
                                {{ $opt['label'] ?? $opt['value'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_spesimen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= BLOK AV SHUNT / CIMINO ================= --}}
            <div id="blok-av" class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-emerald-700 font-medium text-[15px]">Screening AV Shunt / Cimino</span>
                    <span id="badge-kontraindikasi" style="display:none"
                        class="text-xs font-semibold text-red-700 bg-red-100 border border-red-300 rounded-full px-3 py-1">
                        Terdapat kontraindikasi — konsultasikan ke dokter
                    </span>
                </div>
                <div class="p-5 bg-white text-sm space-y-5">
                    @foreach ($blokAv as $grup => $items)
                        <div>
                            <p class="text-xs font-semibold text-slate-600 mb-2">{{ $grup }}</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 max-w-4xl">
                                @foreach ($items as $key => $label)
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">{{ $label }}</label>
                                        {!! $sel($key, $jawaban) !!}
                                        @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= BLOK CDL ================= --}}
            <div id="blok-cdl" class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Screening Akses Vaskuler CDL</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-5">
                    @foreach ($blokCdl as $grup => $items)
                        <div>
                            <p class="text-xs font-semibold text-slate-600 mb-2">{{ $grup }}</p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 max-w-4xl">
                                @foreach ($items as $key => $label)
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">{{ $label }}</label>
                                        {!! $sel($key, $jawaban) !!}
                                        @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                var AV = ['aks_area_1', 'aks_area_2', 'aks_area_3', 'aks_steal_1', 'aks_steal_2', 'aks_steal_3',
                    'aks_steal_4', 'aks_steal_5', 'aks_steal_6', 'aks_steal_7', 'aks_steal_8', 'aks_steal_9'];
                var CDL = ['aks_cdl_1', 'aks_cdl_2', 'aks_cdl_3', 'aks_cdl_4', 'aks_exit_1', 'aks_exit_2',
                    'aks_exit_3', 'aks_exit_4', 'aks_exit_5', 'aks_exit_6', 'aks_pasien_1',
                    'aks_disfungsi_1', 'aks_disfungsi_2', 'aks_disfungsi_3',
                    'aks_nyeri_1', 'aks_nyeri_2', 'aks_nyeri_3', 'aks_nyeri_4'];

                var VAL_AV = 'SCREENING AKSES AV SHUNT/CIMINO';
                var VAL_CDL = 'SCREENING AKSES VASKULER CDL';

                function isiKosong(keys) {
                    // Select di blok non-aktif wajib tetap terkirim (rule server
                    // required utk SEMUA aks_*; filteredData men-null-kan blok
                    // non-aktif — jadi isi default 'Tidak' kalau kosong).
                    keys.forEach(function (name) {
                        var el = document.querySelector('select[name="' + name + '"]');
                        if (el && el.value === '') { el.value = 'Tidak'; }
                    });
                }

                function cek120Kontraindikasi() {
                    var ya = function (name) {
                        var el = document.querySelector('select[name="' + name + '"]');
                        return el && el.value === 'Ya';
                    };
                    var kena = false;
                    AV.forEach(function (name) { if (name.indexOf('aks_steal_') === 0 && ya(name)) { kena = true; } });
                    ['aks_exit_1', 'aks_exit_2', 'aks_exit_3'].forEach(function (name) { if (ya(name)) { kena = true; } });

                    var badge = document.getElementById('badge-kontraindikasi');
                    if (badge) { badge.style.display = kena ? '' : 'none'; }
                }

                function sync() {
                    var jenis = document.getElementById('jenis_spesimen').value;
                    var keAv = jenis === VAL_AV;
                    var keCdl = jenis === VAL_CDL;

                    var blokAv = document.getElementById('blok-av');
                    var blokCdl = document.getElementById('blok-cdl');
                    if (blokAv) { blokAv.style.display = keCdl ? 'none' : ''; }
                    if (blokCdl) { blokCdl.style.display = keAv ? 'none' : ''; }

                    if (keAv) { isiKosong(CDL); }
                    if (keCdl) { isiKosong(AV); }
                    cek120Kontraindikasi();
                }

                document.getElementById('jenis_spesimen').addEventListener('change', sync);
                document.querySelectorAll('#blok-av select, #blok-cdl select').forEach(function (el) {
                    el.addEventListener('change', cek120Kontraindikasi);
                });
                sync();
            })();
        </script>
    </x-emr-split-layout>
@endsection
