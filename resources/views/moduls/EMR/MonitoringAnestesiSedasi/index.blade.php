@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Monitoring Anestesi & Sedasi';
        $subtitleForm = 'Monitoring intra-operasi/intra-sedasi: hemodinamik, suhu, oksigenasi, kesadaran AVPU, nyeri & catatan kritis.';
        $routeName = null;
        $routeUrl = url('emr/form/monitoring_anestesi_sedasi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_monitoring' => 'Tanggal',
            'jam_mulai' => 'Jam Mulai',
            'status_monitoring' => 'Status',
        ];

        $opsiAvpu = ['Alert', 'Voice', 'Pain', 'Unresponsive'];
        $opsiStatus = ['Ongoing', 'Completed'];

        // Label menit per baris (UI saja — tidak disimpan; menit 0/5/15/30
        // adalah baris wajib sesuai KONSEP §6).
        $menitPerBaris = [0, 5, 15, 30, 45, 60, 75, 90, 105, 120, 135, 150, 165, 180, 195, 210, 225, 240, 255, 270];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Monitoring Anestesi"
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
                slug="monitoring_anestesi_sedasi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS MONITORING ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Monitoring</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Monitoring <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_monitoring" value="{{ $v('tanggal_monitoring') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_monitoring') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_mulai" value="{{ $v('jam_mulai') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_mulai') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_selesai" value="{{ $v('jam_selesai') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_selesai') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Status Monitoring <span class="text-red-500">*</span></label>
                            <select name="status_monitoring" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih --</option>
                                @foreach ($opsiStatus as $o)
                                    <option value="{{ $o }}" {{ $v('status_monitoring') === $o ? 'selected' : '' }}>{{ $o }}</option>
                                @endforeach
                            </select>
                            @error('status_monitoring') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= TABEL MONITORING BERKALA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-cyan-600 font-medium text-[15px]">Monitoring Berkala <span class="text-red-500">*</span></span>
                    <span class="text-[11px] text-slate-500">Baris menit 0/5/15/30 wajib diisi; baris berikutnya opsional.</span>
                </div>
                <div class="p-4 bg-white text-sm overflow-x-auto">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-100 text-slate-600">
                                <th class="border border-slate-200 px-2 py-2 text-left">Menit</th>
                                <th class="border border-slate-200 px-2 py-2 text-left">Hemodinamik (TD/N/RR/SpO2)</th>
                                <th class="border border-slate-200 px-2 py-2 text-left">Suhu (°C)</th>
                                <th class="border border-slate-200 px-2 py-2 text-left">Oksigenasi (SpO2/FiO2/EtCO2)</th>
                                <th class="border border-slate-200 px-2 py-2 text-left">Kesadaran (AVPU)</th>
                                <th class="border border-slate-200 px-2 py-2 text-left">Nyeri (VAS)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 1; $i <= 20; $i++)
                                @php $wajib = $i <= 4; @endphp
                                <tr class="{{ $wajib ? 'bg-amber-50/40' : '' }}">
                                    <td class="border border-slate-200 px-2 py-1.5 font-semibold text-slate-600 whitespace-nowrap">
                                        {{ $menitPerBaris[$i - 1] }} m
                                        @if ($wajib) <span class="text-red-500">*</span> @endif
                                    </td>
                                    <td class="border border-slate-200 px-1 py-1">
                                        <input type="text" name="hemodinamik_{{ $i }}" value="{{ $v('hemodinamik_'.$i) }}" maxlength="255" placeholder="120/80, 80, 16, 99"
                                            class="w-full border rounded-sm px-2 py-1.5 text-xs" style="min-width:150px">
                                        @error('hemodinamik_'.$i) <p class="text-[10px] text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="border border-slate-200 px-1 py-1">
                                        <input type="number" step="any" name="suhu_{{ $i }}" value="{{ $v('suhu_'.$i) }}"
                                            class="w-20 border rounded-sm px-2 py-1.5 text-xs">
                                        @error('suhu_'.$i) <p class="text-[10px] text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="border border-slate-200 px-1 py-1">
                                        <input type="text" name="oksigenasi_{{ $i }}" value="{{ $v('oksigenasi_'.$i) }}" maxlength="255" placeholder="99%, 50%, 35"
                                            class="w-full border rounded-sm px-2 py-1.5 text-xs" style="min-width:140px">
                                        @error('oksigenasi_'.$i) <p class="text-[10px] text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="border border-slate-200 px-1 py-1">
                                        <select name="kesadaran_{{ $i }}" class="border rounded-sm px-2 py-1.5 text-xs" style="min-width:110px">
                                            <option value="">--</option>
                                            @foreach ($opsiAvpu as $o)
                                                <option value="{{ $o }}" {{ $v('kesadaran_'.$i) === $o ? 'selected' : '' }}>{{ $o }}</option>
                                            @endforeach
                                        </select>
                                        @error('kesadaran_'.$i) <p class="text-[10px] text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="border border-slate-200 px-1 py-1">
                                        <input type="number" name="nyeri_{{ $i }}" min="0" max="10" value="{{ $v('nyeri_'.$i) }}"
                                            class="w-16 border rounded-sm px-2 py-1.5 text-xs">
                                        @error('nyeri_'.$i) <p class="text-[10px] text-red-600 mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= CATATAN KRITIS, LAMPIRAN & STATUS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Catatan Peristiwa Kritis &amp; Lampiran</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">
                            Kejadian Kritis <span class="text-red-500" id="bintang-kejadian">*</span>
                            <span class="text-[11px] font-normal text-slate-500">(wajib bila status Ongoing)</span>
                        </label>
                        <textarea name="kejadian_kritis" rows="3" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Peristiwa kritis selama monitoring...">{{ $v('kejadian_kritis') }}</textarea>
                        @error('kejadian_kritis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Lampiran Monitoring (scan/bukti)</label>
                        <div class="flex items-center gap-3">
                            <input type="file" id="input-lampiran" accept="image/*" class="text-xs">
                            <button type="button" id="btn-hapus-lampiran" class="text-xs text-red-600 underline" style="display:none">Hapus lampiran</button>
                        </div>
                        <input type="hidden" name="lampiran_monitoring" id="lampiran_monitoring" value="{{ $v('lampiran_monitoring') }}">
                        <p class="text-[11px] text-slate-500 mt-1">Gambar disimpan sebagai data URL (PNG/JPG).</p>
                        <div id="preview-lampiran" class="mt-2" style="display:none">
                            <img id="img-lampiran" class="max-h-48 border border-slate-200 rounded-sm" alt="Lampiran monitoring">
                        </div>
                        @error('lampiran_monitoring') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function sel(name) {
                    var r = document.querySelector('input[name="' + name + '"]:checked');
                    return r ? r.value : '';
                }
                function tampilkan(el, tampil, display) {
                    if (!el) return;
                    el.style.display = tampil ? (display || '') : 'none';
                }

                // Status Ongoing → kejadian kritis wajib (bintang; server juga
                // menerapkan required_if).
                function syncStatus() {
                    var s = document.querySelector('select[name="status_monitoring"]');
                    tampilkan(document.getElementById('bintang-kejadian'), s && s.value === 'Ongoing');
                }
                var statusEl = document.querySelector('select[name="status_monitoring"]');
                if (statusEl) { statusEl.addEventListener('change', syncStatus); }
                syncStatus();

                // Lampiran: file → data URL (input file sendiri tidak punya name).
                var input = document.getElementById('input-lampiran');
                var hidden = document.getElementById('lampiran_monitoring');
                var preview = document.getElementById('preview-lampiran');
                var img = document.getElementById('img-lampiran');
                var btnHapus = document.getElementById('btn-hapus-lampiran');

                function renderPreview() {
                    var val = hidden ? hidden.value : '';
                    var ada = !!val && val.indexOf('data:image/') === 0;
                    tampilkan(preview, ada);
                    tampilkan(btnHapus, ada);
                    if (ada) { img.src = val; }
                }
                if (input) {
                    input.addEventListener('change', function () {
                        var f = input.files && input.files[0];
                        if (!f) { hidden.value = ''; renderPreview(); return; }
                        var fr = new FileReader();
                        fr.onload = function () {
                            hidden.value = fr.result;
                            renderPreview();
                        };
                        fr.readAsDataURL(f);
                    });
                }
                if (btnHapus) {
                    btnHapus.addEventListener('click', function () {
                        hidden.value = '';
                        if (input) input.value = '';
                        renderPreview();
                    });
                }
                renderPreview();
            })();
        </script>
    </x-emr-split-layout>
@endsection
