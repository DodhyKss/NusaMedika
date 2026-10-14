@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Laporan Operasi';
        $subtitleForm = 'Dokumen resmi hasil operasi oleh dokter bedah — tindakan, temuan, komplikasi, transfusi & instruksi pasca operasi.';
        $routeName = null;
        $routeUrl = url('emr/form/laporan_operasi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_laporan' => 'Tanggal',
            'jam_mulai_operasi' => 'Jam Mulai',
            'jam_selesai_operasi' => 'Jam Selesai',
            'nama_tindakan_operasi' => 'Tindakan',
        ];

        $opsiJenisOperasi = \App\Helpers\SelectOption::get('jenis_operasi');
        $opsiYaTidak = ['Ya', 'Tidak'];
        $komplikasi = [
            'komplikasi_operasi_1' => 'Perdarahan',
            'komplikasi_operasi_2' => 'Infeksi',
            'komplikasi_operasi_3' => 'Atonia Uteri',
            'komplikasi_operasi_4' => 'Kerusakan Organ',
            'komplikasi_operasi_5' => 'Luka Lokal (Lokalise)',
            'komplikasi_operasi_6' => 'Re-open / Revisi',
        ];
        $textareaDiagnosa = [
            'diagnosa_pra_operasi' => 'Diagnosa Pra Operasi',
            'diagnosa_pasca_operasi' => 'Diagnosa Pasca Operasi',
            'temuan_operasi' => 'Temuan Operasi',
            'prosedur_operasi' => 'Prosedur yang Dilakukan',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Laporan Operasi"
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
                slug="laporan_operasi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= WAKTU OPERASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Waktu Operasi</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Laporan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_laporan" value="{{ $v('tanggal_laporan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_laporan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lama Operasi</label>
                            <input type="text" name="lama_operasi" id="lama-operasi" value="{{ $v('lama_operasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50" readonly placeholder="Otomatis dari jam mulai – selesai">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Mulai Operasi <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_mulai_operasi" id="jam-mulai" value="{{ $v('jam_mulai_operasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_mulai_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Selesai Operasi <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_selesai_operasi" id="jam-selesai" value="{{ $v('jam_selesai_operasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_selesai_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= TINDAKAN & JENIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tindakan &amp; Jenis Operasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Nama Tindakan Operasi <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_tindakan_operasi" value="{{ $v('nama_tindakan_operasi') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Contoh: Appendektomi">
                        @error('nama_tindakan_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Operasi <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-5">
                            @foreach ($opsiJenisOperasi as $o)
                                <label class="inline-flex items-center gap-1.5">
                                    <input type="radio" name="jenis_operasi" value="{{ $o['value'] }}" {{ $v('jenis_operasi') === $o['value'] ? 'checked' : '' }}>
                                    <span>{{ $o['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('jenis_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach (['jenis_anas_umum' => 'Anestesi Umum', 'jenis_anas_lokal' => 'Anestesi Lokal'] as $key => $label)
                            <div>
                                <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                                <div class="flex gap-5 pt-1">
                                    @foreach ($opsiYaTidak as $o)
                                        <label class="inline-flex items-center gap-1.5">
                                            <input type="radio" name="{{ $key }}" value="{{ $o }}" {{ $v($key) === $o ? 'checked' : '' }}>
                                            <span>{{ $o }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= TIM OPERASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tim Operasi</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Dokter Bedah Operator <span class="text-red-500">*</span></label>
                        <x-select_dokter
                            name="dokter_bedah_operator"
                            id="dokter_bedah_operator"
                            label=""
                            placeholder="-- Pilih Dokter Bedah --"
                            :selected="$v('dokter_bedah_operator')"
                            :required="true" />
                        @error('dokter_bedah_operator') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Dokter Anestesi <span class="text-red-500">*</span></label>
                        <x-select_dokter
                            name="dokter_anestesi"
                            id="dokter_anestesi"
                            label=""
                            placeholder="-- Pilih Dokter Anestesi --"
                            :selected="$v('dokter_anestesi')"
                            :required="true" />
                        @error('dokter_anestesi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= DIAGNOSA, TEMUAN & PROSEDUR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Diagnosa, Temuan &amp; Prosedur</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @foreach ($textareaDiagnosa as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <textarea name="{{ $key }}" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($key) }}</textarea>
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= KOMPLIKASI, TRANSFUSI & IMPLAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Komplikasi, Transfusi &amp; Implan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Komplikasi Operasi</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach ($komplikasi as $key => $label)
                                <label class="inline-flex items-center gap-2 border border-slate-200 rounded-sm px-3 py-2">
                                    <input type="hidden" name="{{ $key }}" value="0">
                                    <input type="checkbox" name="{{ $key }}" value="1" {{ $v($key) === '1' ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Transfusi Darah <span class="text-red-500">*</span></label>
                            <div class="flex gap-5 pt-1">
                                @foreach ($opsiYaTidak as $o)
                                    <label class="inline-flex items-center gap-1.5">
                                        <input type="radio" name="transfusi_darah" value="{{ $o }}" {{ $v('transfusi_darah') === $o ? 'checked' : '' }}>
                                        <span>{{ $o }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('transfusi_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div id="blok-jumlah-transfusi">
                            <label class="block text-xs font-medium mb-1">Jumlah Transfusi (kantong) <span class="text-red-500" id="bintang-jumlah">*</span></label>
                            <input type="number" name="jumlah_transfusi" min="1" value="{{ $v('jumlah_transfusi') }}" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Jumlah kantong">
                            @error('jumlah_transfusi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Pasang Implan <span class="text-red-500">*</span></label>
                            <div class="flex gap-5 pt-1">
                                @foreach ($opsiYaTidak as $o)
                                    <label class="inline-flex items-center gap-1.5">
                                        <input type="radio" name="implan_pasang" value="{{ $o }}" {{ $v('implan_pasang') === $o ? 'checked' : '' }}>
                                        <span>{{ $o }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('implan_pasang') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div id="blok-jenis-implan">
                            <label class="block text-xs font-medium mb-1">Jenis Implan <span class="text-red-500" id="bintang-implan">*</span></label>
                            <input type="text" name="jenis_implan" value="{{ $v('jenis_implan') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="ORIF / OREF / Remove Implant / Lain">
                            @error('jenis_implan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= INSTRUMEN & SPECIMEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Instrumen, Kasa &amp; Specimen</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Instrumen &amp; Kasa Terpakai <span class="text-red-500">*</span></label>
                        <textarea name="instrumen_kasa" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Jumlah instrumen, kasa pengaman, dll.">{{ $v('instrumen_kasa') }}</textarea>
                        @error('instrumen_kasa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Specimen Diterima <span class="text-red-500">*</span></label>
                            <div class="flex gap-5 pt-1">
                                @foreach ($opsiYaTidak as $o)
                                    <label class="inline-flex items-center gap-1.5">
                                        <input type="radio" name="specimen_diterima" value="{{ $o }}" {{ $v('specimen_diterima') === $o ? 'checked' : '' }}>
                                        <span>{{ $o }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('specimen_diterima') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div id="blok-nama-specimen">
                            <label class="block text-xs font-medium mb-1">Nama / Jenis Specimen <span class="text-red-500" id="bintang-specimen">*</span></label>
                            <input type="text" name="nama_specimen" value="{{ $v('nama_specimen') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Tipe spesimen...">
                            @error('nama_specimen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PASCA OPERASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kehilangan Darah, Cairan &amp; Instruksi Pasca Operasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Kehilangan Darah (cc) <span class="text-red-500">*</span></label>
                            <input type="number" name="kehilangan_darah" id="kehilangan-darah" min="0" value="{{ $v('kehilangan_darah') }}" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="cc">
                            @error('kehilangan_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            <div id="badge-pendarahan" class="hidden mt-2 px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-300">
                                Pendarahan signifikan — lengkapi laporan.
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Cairan Masuk (cc) <span class="text-red-500">*</span></label>
                            <input type="number" name="cairan_masuk" min="0" value="{{ $v('cairan_masuk') }}" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="cc">
                            @error('cairan_masuk') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Ruangan Pasca Operasi <span class="text-red-500">*</span></label>
                            <x-select-ruang-perawatan name="ruangan_pasca_operasi" id="ruangan_pasca_operasi" :selected="$v('ruangan_pasca_operasi')" />
                            @error('ruangan_pasca_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Instruksi Pasca Operasi <span class="text-red-500">*</span></label>
                        <textarea name="instruksi_pasca_operasi" rows="3" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Instruksi perawatan, diet, kontrol, dll.">{{ $v('instruksi_pasca_operasi') }}</textarea>
                        @error('instruksi_pasca_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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

                // Lama operasi (preview — server menghitung ulang).
                function syncLama() {
                    var m = document.getElementById('jam-mulai').value;
                    var s = document.getElementById('jam-selesai').value;
                    var out = document.getElementById('lama-operasi');
                    if (!out) return;
                    if (!m || !s || s < m) {
                        out.value = '';
                        return;
                    }
                    var mins = (s.split(':'))[0] * 60 + (s.split(':'))[1]
                        - ((m.split(':'))[0] * 60 + (m.split(':'))[1]);
                    out.value = mins + ' menit';
                }
                ['jam-mulai', 'jam-selesai'].forEach(function (id) {
                    document.getElementById(id).addEventListener('change', syncLama);
                });
                syncLama();

                // Toggle kondisional.
                function syncTransfusi() {
                    var ya = sel('transfusi_darah') === 'Ya';
                    tampilkan(document.getElementById('blok-jumlah-transfusi'), ya);
                    tampilkan(document.getElementById('bintang-jumlah'), ya);
                }
                document.querySelectorAll('input[name="transfusi_darah"]').forEach(function (r) {
                    r.addEventListener('change', syncTransfusi);
                });
                syncTransfusi();

                function syncImplan() {
                    var ya = sel('implan_pasang') === 'Ya';
                    tampilkan(document.getElementById('blok-jenis-implan'), ya);
                    tampilkan(document.getElementById('bintang-implan'), ya);
                }
                document.querySelectorAll('input[name="implan_pasang"]').forEach(function (r) {
                    r.addEventListener('change', syncImplan);
                });
                syncImplan();

                function syncSpecimen() {
                    var ya = sel('specimen_diterima') === 'Ya';
                    tampilkan(document.getElementById('blok-nama-specimen'), ya);
                    tampilkan(document.getElementById('bintang-specimen'), ya);
                }
                document.querySelectorAll('input[name="specimen_diterima"]').forEach(function (r) {
                    r.addEventListener('change', syncSpecimen);
                });
                syncSpecimen();

                // Badge pendarahan signifikan (tampilan).
                function syncBadge() {
                    var input = document.getElementById('kehilangan-darah');
                    var badge = document.getElementById('badge-pendarahan');
                    if (!input || !badge) return;
                    var val = parseInt(input.value, 10);
                    badge.classList.toggle('hidden', !(val > 500));
                }
                var kd = document.getElementById('kehilangan-darah');
                if (kd) {
                    kd.addEventListener('input', syncBadge);
                    syncBadge();
                }
            })();
        </script>
    </x-emr-split-layout>
@endsection
