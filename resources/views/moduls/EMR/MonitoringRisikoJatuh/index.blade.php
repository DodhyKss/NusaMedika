@extends('layouts.iframe')

@section('content')
    @php
        use App\Helpers\EmrHelper;
        use App\Helpers\MonitoringRisikoJatuhHelper;
        use App\Helpers\RisikoJatuhHelper;

        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Monitoring & Evaluasi Risiko Jatuh';
        $subtitleForm = 'Re-assessment risiko jatuh per shift memakai instrumen sesuai usia, dilengkapi status perbandingan dan catatan kejadian jatuh.';
        $routeName = null;
        $routeUrl = url('emr/form/monitoring_risiko_jatuh');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        // Nilai jawaban item instrumen. old() selalu menang atas nilai tersimpan.
        $nilaiItem = [];
        foreach (array_keys(MonitoringRisikoJatuhHelper::items()) as $varItem) {
            $nilaiItem[$varItem] = old($varItem, $emr_data[$varItem] ?? '');
        }
        $nilaiItem['tug_detik'] = old('tug_detik', $emr_data['tug_detik'] ?? '');

        $instrumenAktif = RisikoJatuhHelper::normalisasiKode(
            old('risiko_jatuh_instrumen', $emr_data['risiko_jatuh_instrumen'] ?? '')
        ) ?: RisikoJatuhHelper::instrumenUntukUsia($usia);

        $kejadian = old('kejadian_jatuh', $emr_data['kejadian_jatuh'] ?? 'Tidak');

        // datetime-local memakai format "Y-m-dTH:i", sementara emr_detail bisa
        // menyimpan "Y-m-d H:i" (atau format lain). Normalkan supaya nilainya
        // selalu bisa dibaca balik oleh input tersebut.
        $waktuJatuh = (string) old('waktu_kejadian_jatuh', $emr_data['waktu_kejadian_jatuh'] ?? '');
        if ($waktuJatuh !== '') {
            $waktuJatuh = str_replace(' ', 'T', substr($waktuJatuh, 0, 16));
        }

        // old() mengembalikan array; nilai tersimpan berupa JSON string, tapi
        // `kosong()` (form baru) mengisinya dengan array kosong - jadi jangan
        // pernah mem-cast-nya langsung ke string.
        $oldInput = session()->getOldInput();
        if (is_array($oldInput) && array_key_exists('intervensi_pencegahan', $oldInput)) {
            $intervensiTerpilih = (array) $oldInput['intervensi_pencegahan'];
        } else {
            $tersimpan = $emr_data['intervensi_pencegahan'] ?? '';
            if (is_array($tersimpan)) {
                $intervensiTerpilih = $tersimpan;
            } else {
                $terdecode = json_decode((string) $tersimpan, true);
                $intervensiTerpilih = is_array($terdecode) ? $terdecode : [];
            }
        }

        $headers = [
            'risiko_jatuh_skor' => 'Skor',
            'risiko_jatuh_label' => 'Tingkat Risiko',
            'status_monitoring' => 'Status',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Monitoring Risiko Jatuh"
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
                slug="monitoring_risiko_jatuh"
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
                            <label for="tanggal_monitoring_jatuh" class="block font-bold text-slate-800 mb-1.5">
                                Tanggal Monitoring <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_monitoring_jatuh" name="tanggal_monitoring_jatuh"
                                   value="{{ $v('tanggal_monitoring_jatuh') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_monitoring_jatuh')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_monitoring_jatuh" class="block font-bold text-slate-800 mb-1.5">
                                Waktu Monitoring <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_monitoring_jatuh" name="waktu_monitoring_jatuh" step="60"
                                   value="{{ $v('waktu_monitoring_jatuh') ? substr((string) $v('waktu_monitoring_jatuh'), 0, 5) : '' }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_monitoring_jatuh')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="berat_badan" class="block font-bold text-slate-800 mb-1.5">Berat Badan (kg)</label>
                            <input type="number" step="any" min="1" max="300" id="berat_badan" name="berat_badan"
                                   value="{{ $v('berat_badan') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('berat_badan')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="kesadaran" class="block font-bold text-slate-800 mb-1.5">Kesadaran</label>
                            <select id="kesadaran" name="kesadaran"
                                    class="select2 w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('kesadaran', $v('kesadaran'), '-- Pilih Kesadaran --') !!}
                            </select>
                            @error('kesadaran')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PENILAIAN RISIKO JATUH ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Penilaian Risiko Jatuh</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end bg-slate-50 border border-slate-200 rounded-lg p-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Usia Pasien</label>
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-bold text-cyan-700 leading-none">{{ $usia !== null ? $usia : '-' }}</span>
                                <span class="text-xs text-slate-500">tahun</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">Instrumen default dipilih otomatis sesuai usia.</p>
                        </div>
                        <div>
                            <label for="risiko_jatuh_instrumen_select" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Instrumen Penilaian</label>
                            <select id="risiko_jatuh_instrumen_select" class="w-full text-sm border border-slate-300 rounded px-2.5 py-2 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                @foreach ($semuaInstrumen as $def)
                                    <option value="{{ $def['kode'] }}" @selected($def['kode'] === $instrumenAktif)>
                                        {{ $def['nama'] }} ({{ $def['rentang_usia'] }})
                                    </option>
                                @endforeach
                            </select>
                            <p id="risiko_jatuh_sumber" class="text-[11px] text-slate-400 mt-1">
                                {{ \App\Helpers\RisikoJatuhHelper::definisi($instrumenAktif)['sumber'] ?? '' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Norma TUG</label>
                            <div class="text-xs text-slate-600 leading-relaxed">
                                @if (! empty($norma['nilai']))
                                    Kelompok usia <strong>{{ $norma['label'] }}</strong><br>
                                    Rata-rata {{ number_format((float) $norma['rata'], 1, ',', '.') }} detik,
                                    batas atas <strong>{{ number_format((float) $norma['batas'], 1, ',', '.') }}</strong> detik.
                                @else
                                    {{ $norma['label'] }}
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Panel item per instrumen. Panel yang tidak aktif dinonaktifkan
                         oleh JS sehingga tidak ikut terkirim ke server. --}}
                    <div id="risiko_jatuh_items">
                        @foreach ($semuaInstrumen as $def)
                            @include('moduls.EMR.PartialForm._risiko_jatuh_instrumen', ['def' => $def, 'emr_data' => $nilaiItem])
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= HASIL & STATUS MONITORING ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Hasil Penilaian &amp; Status Monitoring</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div id="risiko_jatuh_hasil" class="border border-slate-300 rounded-sm overflow-hidden bg-white shadow-sm">
                        <div class="bg-slate-100 border-b border-slate-200 px-3 py-2">
                            <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Hasil Penilaian Risiko Jatuh</span>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                            <div class="text-center">
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">Skor</div>
                                <div id="risiko_jatuh_skor_view" class="text-4xl font-bold text-cyan-700 leading-tight">-</div>
                            </div>
                            <div class="text-center md:text-left">
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">Tingkat Risiko</div>
                                <div id="risiko_jatuh_label_view">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">Belum Dinilai</span>
                                </div>
                                <div id="risiko_jatuh_skala_view" class="text-[11px] text-slate-400 mt-1"></div>
                            </div>
                            <div>
                                <div class="text-[11px] uppercase tracking-wider text-slate-500">Intervensi</div>
                                <div id="risiko_jatuh_intervensi_view" class="text-xs text-slate-600 mt-1">-</div>
                            </div>
                        </div>
                        <div class="px-4 pb-4">
                            <div class="text-[11px] uppercase tracking-wider text-slate-500 mb-2">Tabel Kriteria &amp; Intervensi</div>
                            <div class="overflow-x-auto">
                                <table id="risiko_jatuh_tabel_kategori" class="w-full text-xs text-left border-collapse text-slate-700" style="min-width: 420px;">
                                    <tbody class="divide-y divide-slate-100"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-4 border border-slate-200 rounded-lg bg-slate-50">
                        <div class="text-[11px] uppercase tracking-wider text-slate-500">Status Monitoring</div>
                        <div id="status_monitoring_view" class="text-base font-semibold text-slate-700 mt-1">
                            @if ($v('status_monitoring') !== '')
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700">{{ $v('status_monitoring') }}</span>
                            @else
                                <span class="text-slate-400">Belum dapat dibandingkan</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            @if ($skorSebelumnya !== null)
                                Skor penilaian sebelumnya: <strong>{{ rtrim(rtrim(number_format((float) $skorSebelumnya, 1, ',', '.'), '0'), ',') }}</strong>.
                            @else
                                Ini monitoring pertama pada kunjungan ini, jadi belum ada skor sebelumnya untuk dibandingkan.
                            @endif
                            Status dihitung ulang di server dari perbandingan skor, dan arahnya mengikuti skor risiko jatuh:
                            <strong>skor turun = Membaik, skor naik = Memburuk</strong>.
                        </p>
                    </div>

                    <p class="mt-3 text-xs text-slate-400">
                        Skor, tingkat risiko, intervensi, ringkasan, dan status monitoring dihitung ulang di server
                        dari jawaban item di atas. Nilai yang dikirim browser untuk field tersebut diabaikan.
                    </p>
                </div>
            </div>

            {{-- ================= KEJADIAN JATUH ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kejadian Jatuh</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1.5">
                            Apakah terjadi jatuh pada periode ini? <span class="text-red-500">*</span>
                        </label>
                        <div class="flex flex-wrap gap-3">
                            @foreach (\App\Helpers\SelectOption::get('ya_tidak_kapital') as $opt)
                                <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" name="kejadian_jatuh" value="{{ $opt['value'] }}"
                                           @checked((string) $kejadian === (string) $opt['value'])
                                           class="rounded text-cyan-600 focus:ring-cyan-500">
                                    <span class="text-slate-700">{{ $opt['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('kejadian_jatuh')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="wrap-kejadian" class="{{ $kejadian === 'Ya' ? '' : 'hidden' }} space-y-4">
                        <div>
                            <label for="waktu_kejadian_jatuh" class="block font-bold text-slate-800 mb-1.5">
                                Waktu Kejadian Jatuh <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" id="waktu_kejadian_jatuh" name="waktu_kejadian_jatuh"
                                   value="{{ $waktuJatuh }}"
                                   class="w-full max-w-xs border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_kejadian_jatuh')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="keterangan_kejadian_jatuh" class="block font-bold text-slate-800 mb-1.5">
                                Keterangan Kejadian Jatuh <span class="text-red-500">*</span>
                            </label>
                            <textarea id="keterangan_kejadian_jatuh" name="keterangan_kejadian_jatuh" rows="3"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('keterangan_kejadian_jatuh') }}</textarea>
                            @error('keterangan_kejadian_jatuh')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="tindak_lanjut_jatuh" class="block font-bold text-slate-800 mb-1.5">
                                Tindak Lanjut Kejadian Jatuh <span class="text-red-500">*</span>
                            </label>
                            <textarea id="tindak_lanjut_jatuh" name="tindak_lanjut_jatuh" rows="3"
                                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('tindak_lanjut_jatuh') }}</textarea>
                            @error('tindak_lanjut_jatuh')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= INTERVENSI PENCEGAHAN JATUH ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Intervensi Pencegahan Jatuh</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-2">
                    <p class="text-xs text-slate-400">Centang intervensi yang benar-benar sudah dijalankan. Minimal satu butir wajib dipilih.</p>
                    @foreach ($intervensi as $opt)
                        <label class="flex items-start gap-2 px-2 py-1.5 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50">
                            <input type="checkbox" name="intervensi_pencegahan[]" value="{{ $opt['value'] }}"
                                   @checked(in_array($opt['value'], $intervensiTerpilih, true))
                                   class="rounded text-cyan-600 focus:ring-cyan-500 mt-0.5">
                            <span class="text-slate-700">{{ $opt['label'] }}</span>
                        </label>
                    @endforeach
                    @error('intervensi_pencegahan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- ================= CATATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Catatan</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <label for="catatan" class="block font-bold text-slate-800 mb-1.5">Keterangan</label>
                    <textarea id="catatan" name="catatan" rows="3"
                              class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">{{ $v('catatan') }}</textarea>
                    @error('catatan')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </fieldset>

        {{-- Input yang nilainya dipakai server sebagai pembanding / fallback.
             Nilai akhirnya tetap ditulis ulang oleh MonitoringRisikoJatuhHelper::hitung(). --}}
        <input type="hidden" name="risiko_jatuh_instrumen" id="risiko_jatuh_instrumen" value="{{ $v('risiko_jatuh_instrumen') !== '' ? $v('risiko_jatuh_instrumen') : $instrumenAktif }}">
        <input type="hidden" name="risiko_jatuh_skor" id="risiko_jatuh_skor" value="{{ $v('risiko_jatuh_skor') }}">
        <input type="hidden" name="risiko_jatuh_label" id="risiko_jatuh_label" value="{{ $v('risiko_jatuh_label') }}">
        <input type="hidden" name="risiko_jatuh_intervensi" id="risiko_jatuh_intervensi" value="{{ $v('risiko_jatuh_intervensi') }}">
        <input type="hidden" name="risiko_jatuh_ringkasan" id="risiko_jatuh_ringkasan" value="{{ $v('risiko_jatuh_ringkasan') }}">

    </x-emr-split-layout>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ============================================================
            // 1. Blok Kejadian Jatuh (tampil / sembunyi)
            // ============================================================
            // Tampil/sembunyi WAJIB lewat style.display DAN class "hidden" (lihat
            // AGENTS.md - .hidden utility Tailwind bisa dikalahkan utility display
            // lain, jadi set keduanya).
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

            var kejadianRadios = Array.prototype.slice.call(
                document.querySelectorAll('input[type="radio"][name="kejadian_jatuh"]')
            );
            var wrapKejadian = document.getElementById('wrap-kejadian');

            function syncKejadian() {
                var terpilih = null;
                for (var i = 0; i < kejadianRadios.length; i++) {
                    if (kejadianRadios[i].checked) { terpilih = kejadianRadios[i].value; break; }
                }
                tampilkan(wrapKejadian, terpilih === 'Ya', 'block');
            }

            kejadianRadios.forEach(function (el) {
                el.addEventListener('change', syncKejadian);
            });
            syncKejadian();

            // ============================================================
            // 2. Panel instrumen risiko jatuh + hitung skor langsung
            // ============================================================
            var wrapItems = document.getElementById('risiko_jatuh_items');
            if (!wrapItems) return;

            var INST = {!! json_encode($semuaInstrumen) !!};
            var NORMA = {!! json_encode($norma) !!};

            var elInstrumen = document.getElementById('risiko_jatuh_instrumen_select');
            var elSumber = document.getElementById('risiko_jatuh_sumber');
            var elSkorView = document.getElementById('risiko_jatuh_skor_view');
            var elLabelView = document.getElementById('risiko_jatuh_label_view');
            var elSkalaView = document.getElementById('risiko_jatuh_skala_view');
            var elIntervensiView = document.getElementById('risiko_jatuh_intervensi_view');
            var elTabel = document.getElementById('risiko_jatuh_tabel_kategori').querySelector('tbody');

            var WARNA = {
                slate: ['bg-slate-100', 'text-slate-600'],
                emerald: ['bg-emerald-100', 'text-emerald-700'],
                amber: ['bg-amber-100', 'text-amber-700'],
                red: ['bg-red-100', 'text-red-700']
            };

            function defInstrumen(kode) {
                for (var i = 0; i < INST.length; i++) {
                    if (INST[i].kode === kode) return INST[i];
                }
                return INST[0];
            }

            // Skor dari satu item: HDS (bobot_ya), MFS/Sydney (opsi), atau angka (TUG).
            function skorItem(item, nilai) {
                if (nilai === '' || nilai === null || nilai === undefined) return 0;
                if (item.bobot_ya !== undefined) return nilai === 'Ya' ? Number(item.bobot_ya) : 0;
                if (item.opsi) {
                    var v = item.opsi[nilai];
                    return v === undefined ? 0 : Number(v);
                }
                var n = parseFloat(String(nilai).replace(',', '.'));
                return isNaN(n) ? 0 : n;
            }

            function hitungSydney(def) {
                var ts = 0, ms = 0;
                def.item.forEach(function (it) {
                    var v = skorItem(it, nilaiItem(it));
                    if (it.var.indexOf('syd_ts_') === 0) ts += v;
                    if (it.var.indexOf('syd_ms_') === 0) ms += v;
                });
                var jumlah = ts + ms;
                return jumlah <= 2 ? 0 : (jumlah <= 6 ? 7 : jumlah);
            }

            function kategoriTug(detik) {
                if (detik >= 30) return 2;
                if (!NORMA || NORMA.batas === null) return 1;
                return detik <= NORMA.batas ? 0 : 1;
            }

            function kategoriUntuk(def, total) {
                if (def.kode === 'TUG') return kategoriTug(total);
                for (var i = 0; i < def.kategori.length; i++) {
                    if (total <= def.kategori[i].maks) return i;
                }
                return def.kategori.length - 1;
            }

            function badge(warna, teks) {
                var c = WARNA[warna] || WARNA.slate;
                return '<span class="inline-flex px-3 py-1 rounded-full text-xs font-bold ' + c[0] + ' ' + c[1] + '">' + teks + '</span>';
            }

            function setHidden(id, nilai) {
                var el = document.getElementById(id);
                if (el) el.value = nilai === null || nilai === undefined ? '' : nilai;
            }

            // PENTING: simpan SELURUH grup radio, bukan hanya radio pertama.
            // Kalau hanya radio pertama yang diambil lalu dibaca lewat .checked,
            // memilih opsi kedua akan membuat radio pertama ter-uncheck sehingga
            // jawaban terbaca kosong dan skor tidak pernah berubah (bug historis,
            // lihat AGENTS.md section Pengkajian Risiko Jatuh).
            function kumpulkanRef() {
                INST.forEach(function (def) {
                    var panel = document.getElementById('rj-def-' + def.kode);
                    if (!panel) return;

                    def.item.forEach(function (item) {
                        var found = panel.querySelectorAll('[name="' + item.var + '"]');
                        item.els = found.length ? Array.prototype.slice.call(found) : [];
                    });
                });
            }

            function nilaiItem(item) {
                if (!item.els || !item.els.length) return '';
                for (var i = 0; i < item.els.length; i++) {
                    var el = item.els[i];
                    if (el.type === 'radio') {
                        if (el.checked) return el.value;
                    } else {
                        return el.value;
                    }
                }
                return '';
            }

            function normaKriteriaTug() {
                if (!NORMA || NORMA.nilai === null) return 'tanpa norma khusus';
                return 'norma ' + NORMA.label + ' &le; ' + String(NORMA.batas).replace('.', ',') + ' dtk';
            }

            function hitungSekarang() {
                var kode = elInstrumen.value;
                var def = defInstrumen(kode);
                var total = 0, terisi = 0;

                def.item.forEach(function (item) {
                    var nilai = nilaiItem(item);
                    if (nilai === '' || nilai === null) return;
                    terisi++;
                    total += skorItem(item, nilai);
                });

                if (kode === 'SYDNEY') total = hitungSydney(def);
                if (kode === 'HDS') total = Math.min(def.skor_maks, def.skor_dasar + total);

                // Tabel kriteria instrumen aktif.
                elTabel.innerHTML = def.kategori.map(function (c, i) {
                    var krit = kode === 'TUG' ? normaKriteriaTug() : '';
                    var risiko = i < def.kategori.length - 1
                        ? 'skor &le; ' + c.maks
                        : 'skor &gt; ' + def.kategori[i - 1].maks;
                    return '<tr><td class="px-2 py-1.5 font-medium">' + c.label + '</td>'
                        + '<td class="px-2 py-1.5">' + risiko + (krit ? ' <span class="text-slate-400">(' + krit + ')</span>' : '') + '</td>'
                        + '<td class="px-2 py-1.5">' + c.intervensi + '</td></tr>';
                }).join('');

                setHidden('risiko_jatuh_instrumen', kode);

                if (!terisi) {
                    elSkorView.textContent = '-';
                    elLabelView.innerHTML = badge('slate', 'Belum Dinilai');
                    elSkalaView.textContent = '';
                    elIntervensiView.textContent = '-';
                    setHidden('risiko_jatuh_skor', '');
                    setHidden('risiko_jatuh_label', '');
                    setHidden('risiko_jatuh_intervensi', '');
                    setHidden('risiko_jatuh_ringkasan', '');
                    return;
                }

                var kat = def.kategori[kategoriUntuk(def, total)];
                var skorTampil = kode === 'TUG' ? Math.round(total * 10) / 10 : Math.round(total);

                elSkorView.textContent = skorTampil;
                elLabelView.innerHTML = badge(kat.warna, kat.label);
                elSkalaView.textContent = def.kategori.length + ' tingkat, skor minimal ' + def.skor_min
                    + (def.skor_maks ? ' maksimal ' + def.skor_maks : '');
                elIntervensiView.textContent = kat.intervensi;

                setHidden('risiko_jatuh_skor', skorTampil);
                setHidden('risiko_jatuh_label', kat.label);
                setHidden('risiko_jatuh_intervensi', kat.intervensi);

                var ringkas = skorTampil + ' - ' + kat.label;
                if (kode === 'TUG' && NORMA && NORMA.nilai) {
                    ringkas += ' (norma ' + NORMA.label + ': maksimal ' + NORMA.nilai + ' detik)';
                }
                setHidden('risiko_jatuh_ringkasan', ringkas);
            }

            function pasangListener() {
                INST.forEach(function (def) {
                    var panel = document.getElementById('rj-def-' + def.kode);
                    if (!panel) return;
                    panel.querySelectorAll('input, select').forEach(function (el) {
                        el.addEventListener('change', function () {
                            if (def.kode === elInstrumen.value) hitungSekarang();
                        });
                        el.addEventListener('input', function () {
                            if (def.kode === elInstrumen.value) hitungSekarang();
                        });
                    });
                });
            }

            // Hanya panel instrumen aktif yang tampil & submitting.
            function togglePanel() {
                var kode = elInstrumen.value;
                var def = defInstrumen(kode);
                INST.forEach(function (d) {
                    var panel = document.getElementById('rj-def-' + d.kode);
                    if (!panel) return;
                    var aktif = d.kode === kode;
                    panel.classList.toggle('hidden', !aktif);
                    if (!aktif) panel.style.display = 'none';
                    else panel.style.display = '';
                    panel.querySelectorAll('input, select').forEach(function (el) {
                        el.disabled = !aktif;
                    });
                });
                elSumber.textContent = def.sumber;
                hitungSekarang();
            }

            if (elInstrumen) elInstrumen.addEventListener('change', togglePanel);

            kumpulkanRef();
            pasangListener();
            if (elInstrumen) togglePanel();
        });
    </script>

@endsection
