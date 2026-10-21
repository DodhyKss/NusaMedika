@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Surveilans Pasien Terpasang IV Catheter Perifer';
        $subtitleForm = 'Bundle pencegahan phlebitis harian + skala PIVAS (0–4).';
        $routeName = null;
        $routeUrl = url('emr/form/surveilans_iv_catheter');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_evaluasi' => 'Tgl Evaluasi',
            'lokasi_iv' => 'Lokasi IV',
            'pivas_skor' => 'PIVAS',
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

        $cb = function ($name) use ($v) {
            $checked = $v($name) === '1' ? 'checked' : '';
            return '<input type="hidden" name="'.$name.'" value="0">'
                .'<input type="checkbox" name="'.$name.'" value="1" class="pivas-gejala" data-gejala="'.$name.'" '.$checked.'>';
        };

        $ivItem = [
            'iv_1' => 'Pemasangan dengan teknik aseptik',
            'iv_2' => 'Nomor IV catheter sesuai lokasi',
            'iv_3' => 'Jenis cairan sesuai lokasi pemasangan',
            'iv_4' => 'Metode fiksasi benar',
            'iv_5' => 'Kebersihan tangan sebelum & sesudah kontak dengan IV cath/infus',
            'iv_6' => 'Penutup IV catheter dengan transparan dressing',
            'iv_7' => 'Dokumentasi tertulis tanggal dan jam',
        ];

        $gejalaPivas = [
            'pivas_gejala_1' => 'Skor 0: tidak ada rasa sakit / tanda phlebitis',
            'pivas_gejala_2' => 'Skor 1: nyeri / kemerahan di sekitar penusukan',
            'pivas_gejala_3' => 'Skor 2: nyeri, pembengkakan, kemerahan, vena teraba',
            'pivas_gejala_4' => 'Skor 3–4: nyeri sepanjang kanula, edema/indurasi, drainase purulen, demam',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Surveilans IV Catheter"
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
                slug="surveilans_iv_catheter"
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
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4 max-w-4xl">
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
                        <label class="block text-xs font-medium mb-1">Tanggal Pasang</label>
                        <input type="date" name="tanggal_pasang" value="{{ $v('tanggal_pasang') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_pasang') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Lepas</label>
                        <input type="date" name="tanggal_lepas" value="{{ $v('tanggal_lepas') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_lepas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= LOKASI & IDENTITAS IV ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Lokasi, Nomor &amp; Jenis Cairan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4 max-w-4xl">
                    <div>
                        <label class="block text-xs font-medium mb-1">Lokasi IV Catheter <span class="text-red-500">*</span></label>
                        <select name="lokasi_iv" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <option value="">-- Pilih Lokasi --</option>
                            @foreach ($opsiLokasi as $opt)
                                <option value="{{ $opt['value'] }}" {{ $v('lokasi_iv') === $opt['value'] ? 'selected' : '' }}>
                                    {{ $opt['label'] ?? $opt['value'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('lokasi_iv') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Nomor IV Catheter <span class="text-red-500">*</span></label>
                        <input type="text" name="nomor_iv" value="{{ $v('nomor_iv') }}" maxlength="50" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('nomor_iv') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Cairan/Terapi <span class="text-red-500">*</span></label>
                        <input type="text" name="jenis_cairan" value="{{ $v('jenis_cairan') }}" maxlength="200" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('jenis_cairan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= ITEM PENCEGAHAN IV ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-sky-50 border-b border-slate-200">
                    <span class="text-sky-700 font-medium text-[15px]">Item Pencegahan Phlebitis</span>
                </div>
                <div class="p-5 bg-white text-sm divide-y divide-slate-100">
                    @foreach ($ivItem as $key => $label)
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

            {{-- ================= SKOR & GEJALA PIVAS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Skala PIVAS (Peripheral IV Assessment Scale)</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="max-w-sm">
                        <label class="block text-xs font-medium mb-1">Skor PIVAS <span class="text-red-500">*</span></label>
                        <select id="pivas_skor" name="pivas_skor" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <option value="">-- Pilih Skor --</option>
                            @foreach (\App\Helpers\SelectOption::get('opsi_pivas') as $opt)
                                <option value="{{ $opt['value'] }}" {{ $v('pivas_skor') === $opt['value'] ? 'selected' : '' }}>
                                    {{ $opt['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('pivas_skor') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        <p class="text-[11px] text-slate-500 mt-1">Skor ≥ 2 menjadi sinyal tindakan lanjutan (ganti lokasi / planned removal).</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-600 mb-2">Gejala (otomatis menyesuaikan skor — boleh disesuaikan) <span class="text-red-500">*</span></p>
                        <div class="space-y-1.5 max-w-3xl">
                            @foreach ($gejalaPivas as $key => $label)
                                <label class="flex items-start gap-2 text-sm">
                                    <span class="mt-0.5">{!! $cb($key) !!}</span>
                                    <span>{{ $label }}</span>
                                </label>
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function cek117Pivas() {
                    var skorEl = document.getElementById('pivas_skor');
                    var skor = skorEl ? parseInt(skorEl.value, 10) : -1;
                    if (isNaN(skor)) { return; }

                    // Pasangan skor -> gejala utama (KONSEP §4): 0->gejala_1,
                    // 1->gejala_2, 2->gejala_3, 3-4->gejala_4.
                    var pasangan = { 0: 1, 1: 2, 2: 3, 3: 4, 4: 4 };
                    var utama = 'pivas_gejala_' + pasangan[skor];
                    document.querySelectorAll('.pivas-gejala').forEach(function (cb) {
                        cb.checked = (cb.dataset.gejala === utama);
                    });
                }

                var skorEl = document.getElementById('pivas_skor');
                if (skorEl) { skorEl.addEventListener('change', cek117Pivas); }
            })();
        </script>
    </x-emr-split-layout>
@endsection
