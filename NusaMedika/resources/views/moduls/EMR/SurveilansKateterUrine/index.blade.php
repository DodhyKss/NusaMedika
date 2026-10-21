@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Surveilans Pasien Terpasang Kateter Urine';
        $subtitleForm = 'Bundle pencegahan CA-UTI harian — jenis/nomor kateter, 11 item pencegahan & gejala ISK.';
        $routeName = null;
        $routeUrl = url('emr/form/surveilans_kateter_urine');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_evaluasi' => 'Tgl Evaluasi',
            'no_kath_6' => 'No Kateter',
            'tanggal_pasang' => 'Tgl Pasang',
        ];

        $jawaban = ['Ya', 'Tidak', 'N/A'];
        $sel = function ($name, $list) use ($v) {
            $html = '<select name="'.$name.'" class="w-full border rounded-sm px-2 py-1.5 text-sm"><option value="">-- Pilih --</option>';
            foreach ($list as $opt) {
                $attr = $v($name) === $opt ? ' selected' : '';
                $html .= '<option value="'.$opt.'"'.$attr.'>'.$opt.'</option>';
            }
            return $html.'</select>';
        };

        $jenisItem = [
            'jenis_kath_silikon' => 'Kateter Silikon',
            'jenis_kath_folly' => 'Kateter Folly (balon)',
        ];
        $nomorItem = [
            'no_kath_6' => 'Fr 6', 'no_kath_8' => 'Fr 8', 'no_kath_10' => 'Fr 10',
            'no_kath_12' => 'Fr 12', 'no_kath_14' => 'Fr 14',
        ];
        $iskItem = [
            'isk_1' => 'Cuci tangan sesuai 5 moment',
            'isk_2' => 'Pemasangan dengan teknik aseptik',
            'isk_3' => 'Fiksasi dengan baik',
            'isk_4' => 'Urin bag di bawah bladder',
            'isk_5' => 'Posisi kateter baik/tidak tertekuk, terlipat',
            'isk_6' => 'Urin bag tidak menyentuh lantai',
            'isk_7' => 'Tidak bladder training dengan klem',
            'isk_8' => 'Tidak membuka sambungan antar cath dan selang',
            'isk_9' => 'Perineal hygiene dengan air dan sabun 2x/hari',
            'isk_10' => 'Gelas ukur terpisah antar pasien',
            'isk_11' => 'Ada indikasi pemakaian kateter urin',
        ];
        $gejalaIsk = [
            'isk_gejala_1' => 'a. Demam >= 38 C',
            'isk_gejala_2' => 'Nyeri supra-pubik',
            'isk_gejala_3' => 'Urgency / anyang-anyangan',
            'isk_gejala_4' => 'Frequency / sering kencing',
            'isk_gejala_5' => 'Dysuria / nyeri saat kencing',
            'isk_gejala_6' => 'b. Kuman biakan urine >= 10^5',
            'isk_gejala_7' => 'c. Pyuria (>= 10 leukosit urin)',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Surveilans Kateter Urine"
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
                slug="surveilans_kateter_urine"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS EVALUASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Evaluasi</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-4 gap-4 max-w-5xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_evaluasi" value="{{ $v('tanggal_evaluasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Waktu Evaluasi <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_evaluasi" value="{{ $v('waktu_evaluasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('waktu_evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Pasang <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pasang" value="{{ $v('tanggal_pasang') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_pasang') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Lepas <span class="text-[11px] text-slate-400">(kosong = masih terpasang)</span></label>
                        <input type="date" name="tanggal_lepas" value="{{ $v('tanggal_lepas') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_lepas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= JENIS & NOMOR KATETER ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Jenis &amp; Nomor Kateter Urine</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-5">
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Jenis Kateter <span class="text-red-500">*</span> <span class="font-normal">(tepat satu Ya, atau isi "Lainnya")</span></p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 max-w-4xl">
                            @foreach ($jenisItem as $key => $label)
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ $label }}</label>
                                    {!! $sel($key, $jawaban) !!}
                                    @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                            <div>
                                <label class="block text-xs text-slate-500 mb-1">Lainnya (sebutkan)</label>
                                <input type="text" name="jenis_kath_lainnya" value="{{ $v('jenis_kath_lainnya') }}" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('jenis_kath_lainnya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Nomor Kateter <span class="text-red-500">*</span> <span class="font-normal">(tepat satu Ya)</span></p>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 max-w-4xl">
                            @foreach ($nomorItem as $key => $label)
                                <div>
                                    <label class="block text-xs text-slate-500 mb-1">{{ $label }}</label>
                                    {!! $sel($key, $jawaban) !!}
                                    @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= ITEM PENCEGAHAN ISK ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-sky-50 border-b border-slate-200">
                    <span class="text-sky-700 font-medium text-[15px]">Item Pencegahan ISK (CA-UTI Bundle)</span>
                </div>
                <div class="p-5 bg-white text-sm divide-y divide-slate-100">
                    @foreach ($iskItem as $key => $label)
                        <div class="py-2 grid grid-cols-1 md:grid-cols-[1fr_140px] gap-3 items-center">
                            <span class="text-sm">{{ $loop->iteration }}. {{ $label }}</span>
                            <div>
                                {!! $sel($key, $jawaban) !!}
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= GEJALA & TANDA ISK ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-red-50 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                    <span class="text-red-700 font-medium text-[15px]">Gejala &amp; Tanda ISK</span>
                    <span id="badge-cauti" style="display:none"
                        class="text-xs font-semibold text-red-700 bg-red-100 border border-red-300 rounded-full px-3 py-1">
                        Kriteria CA-UTI terpenuhi — hubungi IPCN
                    </span>
                </div>
                <div class="p-5 bg-white text-sm divide-y divide-slate-100">
                    @foreach ($gejalaIsk as $key => $label)
                        <div class="py-2 grid grid-cols-1 md:grid-cols-[1fr_140px] gap-3 items-center">
                            <span class="text-sm">{{ $label }}</span>
                            <div>
                                {!! $sel($key, $jawaban) !!}
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endforeach
                    <p class="text-[11px] text-slate-500 pt-2">
                        Kriteria CA-UTI: minimal satu gejala klinis (1–5) DAN hasil kultur/pyuria (6 atau 7) bernilai Ya.
                    </p>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function cek118CaUti() {
                    var ya = function (name) {
                        var el = document.querySelector('select[name="' + name + '"]');
                        return el && el.value === 'Ya';
                    };

                    var klinis = false;
                    for (var i = 1; i <= 5; i++) {
                        if (ya('isk_gejala_' + i)) { klinis = true; break; }
                    }
                    var lab = ya('isk_gejala_6') || ya('isk_gejala_7');

                    var badge = document.getElementById('badge-cauti');
                    if (badge) { badge.style.display = (klinis && lab) ? '' : 'none'; }
                }

                document.querySelectorAll('select[name^="isk_gejala_"]').forEach(function (el) {
                    el.addEventListener('change', cek118CaUti);
                });
                cek118CaUti();
            })();
        </script>
    </x-emr-split-layout>
@endsection
