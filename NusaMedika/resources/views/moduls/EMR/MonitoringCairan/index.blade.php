@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Monitoring Cairan';
        $subtitleForm = 'Satu baris = satu ronde observasi. Kebutuhan cairan, IWL, total, balance, dan flag abnormal dihitung server.';
        $routeName = null;
        $routeUrl = url('emr/form/monitoring_cairan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $maksBaris = \App\Helpers\CairanHelper::MAKS_BARIS;
        $barisAwal = 3;

        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $barisTerisi = [];
        for ($i = 1; $i <= $maksBaris; $i++) {
            $ada = filled($v('nama_infus_'.$i)) || filled($v('dosis_infus_'.$i))
                || filled($v('volume_infus_'.$i)) || filled($v('ivfd_'.$i));
            if ($ada) {
                $barisTerisi[$i] = true;
            }
        }
        $barisTerpakai = $barisTerisi === [] ? $barisAwal : max(array_merge([$barisAwal], array_keys($barisTerisi)));
    @endphp

    <x-emr-split-layout
        titleRiwayat="Ronde Monitoring Cairan"
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
            {{-- Ronde = satu baris emr. Nomor ronde dihitung saat baca. --}}
            @if (count($ronde) > 0)
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-4">
                    <div class="overflow-x-auto drag-scroll">
                        <table class="w-full text-left border-collapse" style="min-width: 620px;">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="px-3 py-3 text-center w-12">Ronde</th>
                                    <th class="px-3 py-3">Jam</th>
                                    <th class="px-3 py-3">Berat Badan</th>
                                    <th class="px-3 py-3">Intake</th>
                                    <th class="px-3 py-3">Output</th>
                                    <th class="px-3 py-3">Balance</th>
                                    <th class="px-3 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="text-xs divide-y divide-slate-100">
                                @foreach ($ronde as $r)
                                    @php($nilai = $r['nilai'])
                                    @php($balance = (float) ($nilai['balance_cairan'] ?? 0))
                                    <tr class="{{ (int) ($r['emr_id'] ?? 0) === (int) ($emr_id ?? 0) ? 'bg-blue-50/60' : '' }}">
                                        <td class="px-3 py-2 text-center font-bold text-slate-500">{{ $r['ronde'] }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $r['tgl_jam'] ? \Carbon\Carbon::parse($r['tgl_jam'])->format('d/m/Y H:i') : '-' }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $nilai['berat_badan'] ?? '-' }} Kg</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $nilai['total_intake'] ?? '-' }} cc</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $nilai['total_output'] ?? '-' }} cc</td>
                                        <td class="px-3 py-2 font-bold {{ $balance < 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $nilai['balance_cairan'] ?? '-' }} cc</td>
                                        <td class="px-3 py-2">
                                            @if (count($r['abnormal']) > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-700 border border-red-200">
                                                    ABNORMAL
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Normal
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <x-emr-history-table
                slug="monitoring_cairan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="['waktu_monitoring' => 'Jam', 'total_intake' => 'Intake (cc)', 'total_output' => 'Output (cc)']"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS RONDE ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Ronde Observasi</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="tanggal_monitoring" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_monitoring" name="tanggal_monitoring"
                                   value="{{ substr((string) $v('tanggal_monitoring'), 0, 10) }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('tanggal_monitoring')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_monitoring" class="block font-bold text-slate-800 mb-1.5">
                                Waktu Observasi <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_monitoring" name="waktu_monitoring" step="60"
                                   value="{{ $v('waktu_monitoring') ? substr((string) $v('waktu_monitoring'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('waktu_monitoring')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="berat_badan" class="block font-bold text-slate-800 mb-1.5">
                                Berat Badan (Kg) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="any" min="1" max="400" id="berat_badan" name="berat_badan"
                                   value="{{ $v('berat_badan') }}" placeholder="contoh: 70"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('berat_badan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="suhu" class="block font-bold text-slate-800 mb-1.5">Suhu (C)</label>
                            <input type="number" step="any" min="30" max="45" id="suhu" name="suhu"
                                   value="{{ $v('suhu') }}" placeholder="contoh: 37"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                            @error('suhu')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">
                        Kebutuhan cairan harian = berat badan x 40 (minimum) s/d berat badan x 50 (maksimum). Dipakai juga untuk flag abnormal.
                    </p>
                </div>
            </div>

            {{-- ================= INTAKE RONDE INI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-cyan-600 font-medium text-[15px]">Intake Ronde Ini</span>
                    @unless ($isView)
                        <button type="button" id="btnTambahBaris"
                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Baris Cairan
                        </button>
                    @endunless
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">
                    <div class="overflow-x-auto rounded-lg border border-slate-200">
                        <table class="w-full text-left" style="min-width: 820px;">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:40px;">No</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Cairan</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:120px;">Dosis (cc)</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:130px;">Volume Masuk (cc)</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:110px;">Sisa (cc)</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:120px;">IVFD (cc/menit)</th>
                                    @unless ($isView)
                                        <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:40px;">&nbsp;</th>
                                    @endunless
                                </tr>
                            </thead>
                            <tbody id="tbodyInfus" class="divide-y divide-slate-100">
                                @for ($i = 1; $i <= $maksBaris; $i++)
                                    <tr data-item="{{ $i }}" class="{{ $i <= $barisTerpakai ? '' : 'hidden' }}"
                                        style="{{ $i <= $barisTerpakai ? '' : 'display:none' }}">
                                        <td class="px-3 py-2 text-center text-slate-400 align-middle">{{ $i }}</td>
                                        <td class="px-3 py-2 align-top">
                                            <select name="nama_infus_{{ $i }}" data-role="nama"
                                                    class="select2-cairan w-full text-sm border border-slate-300 rounded px-2 py-1.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                                <option value="">-- Pilih Cairan --</option>
                                                @foreach ($barangs as $b)
                                                    <option value="{{ $b->barang_id }}" @selected((int) old('nama_infus_'.$i, $emr_data['nama_infus_'.$i] ?? 0) === (int) $b->barang_id)>
                                                        {{ $b->nama_barang }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('nama_infus_'.$i)
                                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                            @error('dosis_infus_'.$i)
                                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="number" step="any" min="0" name="dosis_infus_{{ $i }}" data-role="dosis"
                                                   value="{{ $v('dosis_infus_'.$i) }}" placeholder="0"
                                                   class="w-full text-sm text-right border border-slate-300 rounded px-2 py-1.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="number" step="any" min="0" name="volume_infus_{{ $i }}" data-role="volume"
                                                   value="{{ $v('volume_infus_'.$i) }}" placeholder="0"
                                                   class="w-full text-sm text-right border border-slate-300 rounded px-2 py-1.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="text" data-role="sisa"
                                                   value="{{ $v('sisa_infus_'.$i) }}" placeholder="otomatis"
                                                   readonly
                                                   class="w-full text-sm text-right border border-slate-200 rounded px-2 py-1.5 bg-slate-100 text-slate-500 outline-none">
                                            <p class="mt-1 text-[11px] text-slate-400">Turunan: dosis - volume.</p>
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="number" step="any" min="0" name="ivfd_{{ $i }}" data-role="ivfd"
                                                   value="{{ $v('ivfd_'.$i) }}" placeholder="0"
                                                   class="w-full text-sm text-right border border-slate-300 rounded px-2 py-1.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                        </td>
                                        @unless ($isView)
                                            <td class="px-3 py-2 align-top text-center">
                                                <button type="button" data-role="hapus" title="Kosongkan Baris"
                                                        class="p-1.5 text-red-500 hover:bg-red-50 rounded-md transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A1 1 0 0116.138 21H7.862a1 1 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </td>
                                        @endunless
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach ([
                            'intake_oral' => 'Oral (cc)',
                            'intake_spoel' => 'Spoel (cc)',
                            'intake_transfusi' => 'Transfusi (cc)',
                            'intake_lainnya' => 'Lainnya (cc)',
                        ] as $name => $label)
                            <div>
                                <label for="{{ $name }}" class="block font-bold text-slate-800 mb-1.5">{{ $label }}</label>
                                <input type="number" step="any" min="0" id="{{ $name }}" name="{{ $name }}"
                                       value="{{ $v($name) }}" placeholder="0"
                                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                @error($name)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= OUTPUT RONDE INI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Output Ronde Ini</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        @foreach ([
                            'output_urine' => 'Urine (cc)',
                            'output_drain' => 'Drain (cc)',
                            'output_ngt' => 'NGT (cc)',
                            'output_wsd' => 'WSD (cc)',
                            'output_lainnya' => 'Lainnya (cc)',
                        ] as $name => $label)
                            <div>
                                <label for="{{ $name }}" class="block font-bold text-slate-800 mb-1.5">{{ $label }}</label>
                                <input type="number" step="any" min="0" id="{{ $name }}" name="{{ $name }}"
                                       value="{{ $v($name) }}" placeholder="0"
                                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                @error($name)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-3 text-xs text-slate-400">
                        Output IWL tidak diinput: dihitung otomatis dari berat badan dan suhu ronde ini.
                    </p>
                </div>
            </div>

            {{-- ================= RINGKASAN BALANCE ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Ringkasan Balance Cairan</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        @foreach ([
                            'total_intake' => 'Total Intake (cc)',
                            'output_iwl' => 'Output IWL (cc)',
                            'total_output' => 'Total Output (cc)',
                            'kebutuhan_cairan' => 'Kebutuhan Cairan',
                            'total_intake_24jam' => 'Intake 24 Jam (cc)',
                        ] as $name => $label)
                            <div class="px-3 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">{{ $label }}</div>
                                <div class="text-lg font-bold text-slate-700">
                                    {{ old($name, $emr_data[$name] ?? '-') }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Balance Cairan Ronde Ini</div>
                            <div id="balanceCairan" class="text-xl font-bold text-cyan-700">
                                {{ old('balance_cairan', $emr_data['balance_cairan'] ?? '-') }} cc
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 flex-1">
                            Total intake dikurangi total output. Dihitung otomatis di server; kolom
                            <code class="px-1 bg-slate-100 rounded">flag_abnormal</code> diisi server juga
                            (1 = abnormal, 0 = normal, kosong = tidak dinilai).
                        </p>
                    </div>

                    <div>
                        <label for="keterangan_monitoring" class="block font-bold text-slate-800 mb-1.5">Keterangan Monitoring Cairan</label>
                        <textarea id="keterangan_monitoring" name="keterangan_monitoring" rows="3"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('keterangan_monitoring') }}</textarea>
                        @error('keterangan_monitoring')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var MAKS = {{ $maksBaris }};
        var tampilkanBaris = {{ $barisTerpakai }};
        var isView = {{ $isView ? 'true' : 'false' }};
        var tbody = document.getElementById('tbodyInfus');
        if (!tbody) return;

        var ADA_SELECT2 = !isView && (typeof $ !== 'undefined') && (typeof $.fn.select2 === 'function');

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

        function barisTerpakai(tr) {
            var ada = false;
            tr.querySelectorAll('input[data-role]:not([readonly]), select[data-role]').forEach(function (el) {
                if (el.value !== '' && el.value !== null) ada = true;
            });
            return ada;
        }

        // Baris tersembunyi melepas atribut `name` (BUKAN `disabled`, karena
        // Select2 mengunci status disabled saat di-init).
        function sinkronNama(tr) {
            var tampil = barisTerpakai(tr) || Number(tr.dataset.item) <= tampilkanBaris;
            tr.querySelectorAll('input, select').forEach(function (el) {
                if (!el.dataset.namaAsli) el.dataset.namaAsli = el.getAttribute('name') || '';
                if (tampil) el.setAttribute('name', el.dataset.namaAsli);
                else el.removeAttribute('name');
            });
        }

        // Select2 selalu destroy sebelum init ulang.
        function pasangCairan(tr) {
            if (!ADA_SELECT2) return;
            var $sel = $(tr).find('select.select2-cairan');
            if (!$sel.length) return;
            if ($sel.data('select2')) $sel.select2('destroy');
            $sel.select2({
                placeholder: '-- Pilih Cairan --',
                allowClear: true,
                width: '100%'
            });
        }

        function pasangBaris(tr) {
            pasangCairan(tr);

            tr.querySelectorAll('input[data-role], select[data-role]').forEach(function (el) {
                el.addEventListener('input change', hitungBaris);
            });

            var hapus = tr.querySelector('[data-role="hapus"]');
            if (hapus) {
                hapus.addEventListener('click', function () {
                    var $sel = $(tr).find('select.select2-cairan');
                    if ($sel.data('select2')) $sel.select2('destroy');

                    tr.querySelectorAll('input[data-role], select[data-role]').forEach(function (el) { el.value = ''; });

                    tampilkanBaris = Math.max(1, Math.min(tampilkanBaris - 1, MAKS));
                    pasangCairan(tr);
                    perbaruiBaris();
                });
            }

            hitungBaris(tr);
            sinkronNama(tr);
        }

        function hitungBaris(tr) {
            var baris = tr || null;
            var rows = baris ? [baris] : Array.prototype.slice.call(tbody.querySelectorAll('tr[data-item]'));

            rows.forEach(function (row) {
                var nama = row.querySelector('[data-role="nama"]');
                var dosis = row.querySelector('[data-role="dosis"]');
                var volume = row.querySelector('[data-role="volume"]');
                var sisa = row.querySelector('[data-role="sisa"]');
                if (!dosis || !volume || !sisa) return;

                var d = parseFloat(dosis.value || '0');
                var v = parseFloat(volume.value || '0');

                // Volume tidak boleh melebihi sisa infus (dosis).
                if (!isNaN(v) && !isNaN(d) && d > 0 && v > d) {
                    volume.value = d;
                    v = d;
                }

                sisa.value = (!nama || nama.value === '') ? '' : (isNaN(d) || d <= 0 ? '' : Math.round((d - v) * 100) / 100);
            });

            hitungTotal();
        }

        function hitungTotal() {
            var intake = 0;
            var output = 0;

            tbody.querySelectorAll('tr[data-item]').forEach(function (tr) {
                var v = parseFloat(tr.querySelector('[data-role="volume"]').value || '0');
                if (!isNaN(v)) intake += v;
            });

            ['intake_oral', 'intake_spoel', 'intake_transfusi', 'intake_lainnya'].forEach(function (id) {
                var el = document.getElementById(id);
                var v = parseFloat((el && el.value) || '0');
                if (!isNaN(v)) intake += v;
            });

            ['output_urine', 'output_drain', 'output_ngt', 'output_wsd', 'output_lainnya'].forEach(function (id) {
                var el = document.getElementById(id);
                var v = parseFloat((el && el.value) || '0');
                if (!isNaN(v)) output += v;
            });

            var out = document.getElementById('balanceCairan');
            if (out) {
                var b = intake - output;
                out.textContent = (Math.round(b * 100) / 100) + ' cc';
                out.classList.toggle('text-red-600', b < 0);
            }
        }

        function perbaruiBaris() {
            tbody.querySelectorAll('tr[data-item]').forEach(sinkronNama);
            var btn = document.getElementById('btnTambahBaris');
            if (btn) btn.parentElement.style.display = tampilkanBaris >= MAKS ? 'none' : '';
            hitungTotal();
        }

        tbody.querySelectorAll('tr[data-item]').forEach(pasangBaris);

        var btnTambah = document.getElementById('btnTambahBaris');
        if (btnTambah) {
            btnTambah.addEventListener('click', function () {
                if (tampilkanBaris >= MAKS) return;
                tampilkanBaris++;
                var tr = tbody.querySelector('tr[data-item="' + String(tampilkanBaris) + '"]');
                if (tr) {
                    pasangCairan(tr);
                    perbaruiBaris();
                    var sel = tr.querySelector('[data-role="nama"]');
                    if (sel && sel.focus) sel.focus();
                }
            });
        }

        ['intake_oral', 'intake_spoel', 'intake_transfusi', 'intake_lainnya',
            'output_urine', 'output_drain', 'output_ngt', 'output_wsd', 'output_lainnya'
        ].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('input', hitungTotal);
        });

        perbaruiBaris();
    });
    </script>

@endsection