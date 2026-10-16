@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Monitoring Transfusi Darah';
        $subtitleForm = 'Pemantauan reaksi transfusi per ronde — gejala, tanda & tindakan bila terjadi reaksi.';
        $routeName = null;
        $routeUrl = url('emr/form/monitoring_transfusi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_evaluasi' => 'Tanggal',
            'jam' => 'Jam',
            'no_labu_darah' => 'No Labu',
        ];

        $maksGejala = \App\Http\Controllers\EMR\MonitoringTransfusi\MonitoringTransfusiController::MAKS_GEJALA;

        $opsiGejala = array_column(\App\Helpers\SelectOption::get('gejala_reaksi_transfusi'), 'value');
        $opsiTanda = array_column(\App\Helpers\SelectOption::get('tanda_reaksi_transfusi'), 'value');

        // Opsi komponen — rule server `in:` 5 nilai (tanpa Lainnya), opsi
        // blade ikut difilter (pelajaran form 91/99).
        $opsiKomponen = array_values(array_filter(
            array_column(\App\Helpers\SelectOption::get('jenis_komponen_darah'), 'value'),
            fn ($nilai) => $nilai !== 'Lainnya'
        ));

        // Daftar ronde yang sudah ada (chip navigasi) + ronde berikutnya.
        $daftarRonde = array_map('intval', array_keys($rondes));
        sort($daftarRonde);
        $rondeUrl = fn (int $n) => url('emr/form/monitoring_transfusi/'.$registrasiDetailId.'/'.$emr_id).'?ronde='.$n;
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Monitoring Transfusi"
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
                slug="monitoring_transfusi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        {{-- ================= NAVIGASI RONDE ================= --}}
        @if ($isEdit)
            <div class="mb-3 border border-slate-200 rounded-sm shadow-sm bg-white p-4">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Ronde:</span>
                        @foreach ($daftarRonde as $n)
                            <a href="{{ $rondeUrl($n) }}"
                                class="px-3 py-1.5 text-xs font-medium rounded-full border {{ $n === $rondeAktif ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' }}">
                                Ronde {{ $n }}
                            </a>
                        @endforeach
                    </div>
                    @if (! $isView)
                        <a href="{{ $rondeUrl($rondeBaru) }}"
                            class="px-3 py-1.5 text-xs font-medium border border-emerald-500 text-emerald-700 rounded-sm hover:bg-emerald-50">
                            + Tambah Ronde {{ $rondeBaru }}
                        </a>
                    @endif
                </div>
                <p class="text-[11px] text-slate-500 mt-2">Ronde aktif: <strong>{{ $rondeAktif }}</strong> — menyimpan menimpa isi ronde ini saja.</p>
            </div>
        @endif

        {{-- Input ronde ikut form milik x-emr-split-layout (jangan membuat
             <form> bersarang — parser HTML mengabaikan tag form bersarang). --}}
        @if ($isEdit)
            <input type="hidden" name="ronde" value="{{ $rondeAktif }}">
        @endif

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

                {{-- ================= IDENTITAS RONDE ================= --}}
                <div class="border border-slate-200 rounded-sm shadow-sm">
                    <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                        <span class="text-cyan-600 font-medium text-[15px]">Identitas Ronde {{ $rondeAktif }}</span>
                    </div>
                    <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_evaluasi" value="{{ $v('tanggal_evaluasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam <span class="text-red-500">*</span></label>
                            <input type="time" name="jam" value="{{ $v('jam') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">No Labu Darah <span class="text-red-500">*</span></label>
                            <input type="text" name="no_labu_darah" value="{{ $v('no_labu_darah') }}" maxlength="50" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('no_labu_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Darah Masuk (mL, kumulatif) <span class="text-red-500">*</span></label>
                            <input type="number" name="darah_masuk" value="{{ $v('darah_masuk') }}" min="0" max="10000" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('darah_masuk') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Komponen Darah <span class="text-red-500">*</span></label>
                            <select name="komponen_darah" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Komponen --</option>
                                @foreach ($opsiKomponen as $jenis)
                                    <option value="{{ $jenis }}" {{ $v('komponen_darah') === $jenis ? 'selected' : '' }}>{{ $jenis }}</option>
                                @endforeach
                            </select>
                            @error('komponen_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Skala Nyeri (0–10)</label>
                            <input type="number" name="skala_nyeri" value="{{ $v('skala_nyeri') }}" min="0" max="10" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('skala_nyeri') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- ================= TANDA VITAL ================= --}}
                <div class="border border-slate-200 rounded-sm shadow-sm">
                    <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                        <span class="text-amber-700 font-medium text-[15px]">Tanda Vital</span>
                    </div>
                    <div class="p-5 bg-white text-sm grid grid-cols-2 md:grid-cols-5 gap-4 max-w-4xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Sistolik <span class="text-red-500">*</span></label>
                            <input type="number" name="sistolik" value="{{ $v('sistolik') }}" min="50" max="300" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('sistolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Diastolik <span class="text-red-500">*</span></label>
                            <input type="number" name="diastolik" value="{{ $v('diastolik') }}" min="20" max="200" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('diastolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Nadi <span class="text-red-500">*</span></label>
                            <input type="number" name="nadi" value="{{ $v('nadi') }}" min="20" max="250" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('nadi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">RR <span class="text-red-500">*</span></label>
                            <input type="number" name="pernapasan" value="{{ $v('pernapasan') }}" min="5" max="80" step="1" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('pernapasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Suhu (°C) <span class="text-red-500">*</span></label>
                            <input type="number" name="suhu" value="{{ $v('suhu') }}" min="30" max="45" step="0.1" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('suhu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- ================= GEJALA & TANDA REAKSI ================= --}}
                <div class="border border-slate-200 rounded-sm shadow-sm">
                    <div class="px-4 py-3 bg-red-50 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
                        <span class="text-red-700 font-medium text-[15px]">Gejala &amp; Tanda Reaksi Transfusi</span>
                        <span id="badge-reaksi-berat" style="display:none"
                            class="text-xs font-semibold text-red-700 bg-red-100 border border-red-300 rounded-full px-3 py-1">
                            REAKSI BERAT terdeteksi — hentikan transfusi &amp; lapor dokter
                        </span>
                    </div>
                    <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-semibold text-slate-600 mb-2">Gejala (centang bila ada)</p>
                            <div class="space-y-1.5">
                                @for ($i = 1; $i <= $maksGejala; $i++)
                                    <label class="flex items-start gap-2 text-sm">
                                        <input type="checkbox" name="gejala_{{ $i }}" value="1" class="mt-0.5 gejala-tanda"
                                            data-berat="gejala" data-indeks="{{ $i }}"
                                            {{ $v('gejala_'.$i) == '1' ? 'checked' : '' }}>
                                        <span>{{ $opsiGejala[$i - 1] ?? ('Gejala '.$i) }}</span>
                                    </label>
                                @endfor
                            </div>
                            @for ($i = 1; $i <= $maksGejala; $i++)
                                @error('gejala_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            @endfor
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-slate-600 mb-2">Tanda (centang bila ada)</p>
                            <div class="space-y-1.5">
                                @for ($i = 1; $i <= $maksGejala; $i++)
                                    <label class="flex items-start gap-2 text-sm">
                                        <input type="checkbox" name="tanda_{{ $i }}" value="1" class="mt-0.5 gejala-tanda"
                                            data-berat="tanda" data-indeks="{{ $i }}"
                                            {{ $v('tanda_'.$i) == '1' ? 'checked' : '' }}>
                                        <span>{{ $opsiTanda[$i - 1] ?? ('Tanda '.$i) }}</span>
                                    </label>
                                @endfor
                            </div>
                            @for ($i = 1; $i <= $maksGejala; $i++)
                                @error('tanda_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            @endfor
                        </div>
                    </div>
                </div>

                {{-- ================= TINDAKAN & KELUHAN ================= --}}
                <div class="border border-slate-200 rounded-sm shadow-sm">
                    <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                        <span class="text-emerald-700 font-medium text-[15px]">Tindakan &amp; Keluhan Pasien</span>
                    </div>
                    <div class="p-5 bg-white text-sm space-y-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tindakan yang Dilakukan <span class="text-red-500">*</span></label>
                            <textarea name="tindakan" rows="2" maxlength="1000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('tindakan') }}</textarea>
                            @error('tindakan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Keluhan Pasien <span class="text-red-500">*</span></label>
                            <textarea name="keluhan" rows="2" maxlength="500" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keluhan') }}</textarea>
                            @error('keluhan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

            </fieldset>

        <script>
            (function () {
                // Indeks reaksi berat (KONSEP §7): gejala 7 & 9, tanda 1 & 5.
                var INDEKS_BERAT = { gejala: [7, 9], tanda: [1, 5] };
                var badge = document.getElementById('badge-reaksi-berat');

                function syncBadge() {
                    var berat = false;
                    document.querySelectorAll('.gejala-tanda').forEach(function (cb) {
                        var grup = cb.dataset.berat;
                        var indeks = parseInt(cb.dataset.indeks, 10);
                        if (cb.checked && INDEKS_BERAT[grup].indexOf(indeks) !== -1) {
                            berat = true;
                        }
                    });
                    badge.style.display = berat ? '' : 'none';
                }

                document.querySelectorAll('.gejala-tanda').forEach(function (cb) {
                    cb.addEventListener('change', syncBadge);
                });
                syncBadge();
            })();
        </script>
    </x-emr-split-layout>
@endsection
