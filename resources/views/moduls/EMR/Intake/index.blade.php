@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Intake Cairan';
        $subtitleForm = 'Pencatatan seluruh cairan yang masuk: 6 cabang infus, syringe pump, oral, spoel, transfusi, dan intake lainnya.';
        $routeName = null;
        $routeUrl = url('emr/form/intake');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        $maksBaris = \App\Helpers\CairanHelper::MAKS_BARIS;
        $barisAwal = 3;

        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        // Baris yang sudah terisi (edit / validasi gagal) selalu ditampilkan.
        $barisTerisi = [];
        for ($i = 1; $i <= $maksBaris; $i++) {
            $ada = filled($v('nama_infus_'.$i)) || filled($v('volume_infus_'.$i))
                || filled($v('sisa_infus_'.$i)) || filled($v('ivfd_'.$i))
                || filled($v('syringe_pump_'.$i));
            if ($ada) {
                $barisTerisi[$i] = true;
            }
        }
        $barisTerpakai = $barisTerisi === [] ? $barisAwal : max(array_merge([$barisAwal], array_keys($barisTerisi)));

        // Catatan: baris snapshot nama cairan dibuang supaya tidak tampil dua kali.
        $catatan = \App\Helpers\CairanHelper::stripSnapshot((string) old('catatan', $emr_data['catatan'] ?? ''));
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Intake Cairan"
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
                slug="intake"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="['total_intake' => 'Total Intake (cc)']"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= CAIRAN INFUS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-cyan-600 font-medium text-[15px]">Cairan Infus</span>
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
                        <table class="w-full text-left" style="min-width: 720px;">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200">
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:40px;">No</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Cairan <span class="text-red-500">*</span></th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:130px;">Volume (cc)</th>
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider" style="width:130px;">Sisa (cc)</th>
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
                                            @error('volume_infus_'.$i)
                                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="number" step="any" min="0" name="volume_infus_{{ $i }}" data-role="volume"
                                                   value="{{ $v('volume_infus_'.$i) }}" placeholder="0"
                                                   class="w-full text-sm text-right border border-slate-300 rounded px-2 py-1.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                        </td>
                                        <td class="px-3 py-2 align-top">
                                            <input type="number" step="any" name="sisa_infus_{{ $i }}" data-role="sisa"
                                                   value="{{ $v('sisa_infus_'.$i) }}" placeholder="0"
                                                   class="w-full text-sm text-right border border-slate-300 rounded px-2 py-1.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                            <p class="mt-1 text-[11px] text-slate-400">Isi minus bila infus melampaui volume.</p>
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
                    <p class="text-xs text-slate-400">
                        Maksimal {{ $maksBaris }} cabang infus. Baris tanpa nama cairan diabaikan server.
                    </p>
                </div>
            </div>

            {{-- ================= INTAKE LAINNYA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Intake Lainnya</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach ([
                            'oral' => 'Oral (cc)',
                            'spoel' => 'Spoel (cc)',
                            'transfusi' => 'Transfusi (cc)',
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

            {{-- ================= SYRINGE PUMP ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Syringe Pump</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        @for ($i = 1; $i <= $maksBaris; $i++)
                            <div>
                                <label for="syringe_pump_{{ $i }}" class="block font-bold text-slate-800 mb-1.5">Slot {{ $i }} (cc)</label>
                                <input type="number" step="any" min="0" id="syringe_pump_{{ $i }}" name="syringe_pump_{{ $i }}"
                                       value="{{ $v('syringe_pump_'.$i) }}" placeholder="0"
                                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                                @error('syringe_pump_'.$i)
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- ================= RINGKASAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Ringkasan Intake</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="flex items-center gap-4">
                        <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500">Total Intake Cairan</div>
                            <div id="totalIntake" class="text-xl font-bold text-cyan-700">
                                {{ old('total_intake', $emr_data['total_intake'] ?? '-') }} cc
                            </div>
                        </div>
                        <p class="text-xs text-slate-400">
                            Dihitung otomatis di server dari seluruh cabang infus, syringe pump, dan intake lainnya.
                        </p>
                    </div>

                    <div>
                        <label for="catatan" class="block font-bold text-slate-800 mb-1.5">Catatan</label>
                        <textarea id="catatan" name="catatan" rows="3"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $catatan }}</textarea>
                        @error('catatan')
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

        // Tampil/sembunyi WAJIB lewat style.display DAN class `hidden`.
        // Tailwind v4 menaruh `.hidden` sebelum utility display lain di CSS,
        // jadi hanya memasang class `hidden` tidak cukup (lihat AGENTS.md).
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

        // Baris yang terisi selalu tampil, walau indeksnya lebih besar dari
        // jumlah baris yang sedang ditampilkan.
        function barisTerpakai(tr) {
            var ada = false;
            tr.querySelectorAll('input[data-role], select[data-role]').forEach(function (el) {
                if (el.value !== '' && el.value !== null) ada = true;
            });
            return ada;
        }

        // Atribut `name` pada baris tersembunyi DILAPAS supaya tidak terkirim
        // sama sekali. JANGAN pakai `disabled`: Select2 mengunci status
        // disabled saat di-init sehingga baris tidak bisa diaktifkan lagi.
        function sinkronNama(tr) {
            var tampil = barisTerpakai(tr) || Number(tr.dataset.item) <= tampilkanBaris;
            tr.querySelectorAll('input, select').forEach(function (el) {
                if (!el.dataset.namaAsli) el.dataset.namaAsli = el.getAttribute('name') || '';
                if (tampil) el.setAttribute('name', el.dataset.namaAsli);
                else el.removeAttribute('name');
            });
        }

        // Select2 harus SELALU destroy sebelum init ulang, kalau tidak
        // muncul container dropdown ganda dan baris tidak bisa diklik.
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

            tr.querySelectorAll('[data-role]').forEach(function (el) {
                el.addEventListener('change input', function () { hitungTotal(); });
            });

            var hapus = tr.querySelector('[data-role="hapus"]');
            if (hapus) {
                hapus.addEventListener('click', function () {
                    var $sel = $(tr).find('select.select2-cairan');
                    if ($sel.data('select2')) $sel.select2('destroy');

                    tr.querySelectorAll('input[data-role], select[data-role]').forEach(function (el) { el.value = ''; });

                    // Baris yang dikosongkan turun sampai baris teratas.
                    tampilkanBaris = Math.max(1, Math.min(tampilkanBaris - 1, MAKS));
                    pasangCairan(tr);
                    perbaruiBaris();
                });
            }

            sinkronNama(tr);
        }

        function perbaruiBaris() {
            tbody.querySelectorAll('tr[data-item]').forEach(function (tr) {
                sinkronNama(tr);
            });
            var btn = document.getElementById('btnTambahBaris');
            if (btn) btn.parentElement.style.display = tampilkanBaris >= MAKS ? 'none' : '';
            hitungTotal();
        }

        function hitungTotal() {
            var total = 0;
            tbody.querySelectorAll('tr[data-item]').forEach(function (tr) {
                var v = parseFloat(tr.querySelector('[data-role="volume"]').value || '0');
                if (!isNaN(v)) total += v;
            });
            ['oral', 'spoel', 'transfusi', 'intake_lainnya'].forEach(function (id) {
                var el = document.getElementById(id);
                var v = parseFloat((el && el.value) || '0');
                if (!isNaN(v)) total += v;
            });
            document.querySelectorAll('[name^="syringe_pump_"]').forEach(function (el) {
                var v = parseFloat(el.value || '0');
                if (!isNaN(v)) total += v;
            });

            var out = document.getElementById('totalIntake');
            if (out) out.textContent = (Math.round(total * 100) / 100) + ' cc';
        }

        tbody.querySelectorAll('tr[data-item]').forEach(function (tr) {
            pasangBaris(tr);
        });

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

        perbaruiBaris();
    });
    </script>

@endsection