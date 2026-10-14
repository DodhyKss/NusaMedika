@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Catatan Anestesia';
        $subtitleForm = 'Catatan anestesi lengkap: jalan napas, ventilasi, monitoring, obat & cairan intraoperatif.';
        $routeName = null;
        $routeUrl = url('emr/form/catatan_anestesia');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_catatan' => 'Tanggal',
            'jam_mulai' => 'Jam Mulai',
            'jenis_anestesi' => 'Jenis',
        ];

        $opsiJenis = ['Umum', 'Regional', 'Epidural', 'Spinal', 'Sedasi'];
        $opsiAirway = ['Ada', 'Tidak_ada'];
        $labelAirway = ['Ada' => 'Ada', 'Tidak_ada' => 'Tidak ada'];
        $opsiVentilasi = ['Spontan', 'Assisted', 'Controlled'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Catatan Anestesia"
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
                slug="catatan_anestesia"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS CATATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Catatan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Catatan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_catatan" value="{{ $v('tanggal_catatan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_catatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lama Anestesia</label>
                            <input type="text" id="preview-lama" value="{{ $v('lama_anestesia') }}" class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50" placeholder="Otomatis dari jam mulai & selesai" readonly>
                            <input type="hidden" name="lama_anestesia" id="field-lama" value="{{ $v('lama_anestesia') }}">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Mulai Anestesia <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_mulai" value="{{ $v('jam_mulai') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_mulai') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Selesai Anestesia <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_selesai" value="{{ $v('jam_selesai') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_selesai') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Dokter Anestesia <span class="text-red-500">*</span></label>
                            <x-select_dokter
                                name="dokter_anestesi"
                                id="dokter_anestesi"
                                label=""
                                placeholder="-- Pilih Dokter Anestesia --"
                                :selected="$v('dokter_anestesi')"
                                :required="true" />
                            @error('dokter_anestesi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Perawat Anestesia <span class="text-red-500">*</span></label>
                            <x-select_pegawai
                                name="perawat_anestesi"
                                id="perawat_anestesi"
                                label=""
                                placeholder="-- Pilih Perawat Anestesia --"
                                :selected="$v('perawat_anestesi')"
                                :required="true" />
                            @error('perawat_anestesi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= JENIS ANESTESI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Jenis Anestesia</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="flex flex-wrap gap-x-6 gap-y-2">
                        @foreach ($opsiJenis as $idx => $opsi)
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="jenis_anestesi" value="{{ $opsi }}" {{ $v('jenis_anestesi') === $opsi ? 'checked' : '' }}>
                                <span>{{ $opsi }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('jenis_anestesi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= JALAN NAPAS & VENTILASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-teal-50 border-b border-slate-200">
                    <span class="text-teal-700 font-medium text-[15px]">Jalan Napas &amp; Ventilasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Difficult Airway <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-x-6 gap-y-2 mt-1">
                            @foreach ($opsiAirway as $opsi)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="airway" value="{{ $opsi }}" {{ $v('airway') === $opsi ? 'checked' : '' }}>
                                    <span>{{ $labelAirway[$opsi] ?? $opsi }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('airway') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Jalan Napas (Alat &amp; Ukuran) <span class="text-red-500">*</span></label>
                            <input type="text" name="airway_2" value="{{ $v('airway_2') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. ETT cuffed 7.0">
                            @error('airway_2') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Sirkuit <span class="text-red-500">*</span></label>
                            <input type="text" name="sirkuit" value="{{ $v('sirkuit') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. Closed circuit">
                            @error('sirkuit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div id="blok-caudel" style="display:none">
                            <label class="block text-xs font-medium mb-1">Ukuran Caudel <span class="text-red-500">*</span></label>
                            <input type="text" name="caudel" value="{{ $v('caudel') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Ukuran caudel...">
                            @error('caudel') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Posisi <span class="text-red-500">*</span></label>
                            <select name="posisi" class="w-full border rounded-sm px-3 py-2 text-sm">
                                {!! \App\Helpers\SelectOption::render('posisi_operasi', $v('posisi'), '-- Pilih Posisi --') !!}
                            </select>
                            @error('posisi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Monitoring <span class="text-red-500">*</span></label>
                            <input type="text" name="monitor" value="{{ $v('monitor') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. ECG, NIBP, SpO2, EtCO2">
                            @error('monitor') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">ASA <span class="text-red-500">*</span></label>
                            <select name="asa" class="w-full border rounded-sm px-3 py-2 text-sm">
                                {!! \App\Helpers\SelectOption::render('asa_kelas', $v('asa'), '-- Pilih ASA --') !!}
                            </select>
                            @error('asa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Ventilasi <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-x-6 gap-y-2 mt-1">
                            @foreach ($opsiVentilasi as $opsi)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="radio" name="ventilasi" value="{{ $opsi }}" {{ $v('ventilasi') === $opsi ? 'checked' : '' }}>
                                    <span>{{ $opsi }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('ventilasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= RIWAYAT & PENUNJANG ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-purple-50 border-b border-slate-200">
                    <span class="text-purple-700 font-medium text-[15px]">Riwayat Penyakit &amp; Penunjang</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Hasil Laboratorium Penunjang <span class="text-red-500">*</span></label>
                            <textarea name="penunjang_lab" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Hasil lab penunjang...">{{ $v('penunjang_lab') }}</textarea>
                            @error('penunjang_lab') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Riwayat Penyakit &amp; Alergi <span class="text-red-500">*</span></label>
                            <textarea name="riwayat_penyakit" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Riwayat penyakit & alergi...">{{ $v('riwayat_penyakit') }}</textarea>
                            @error('riwayat_penyakit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Saraf Perifer <span class="text-red-500">*</span></label>
                            <input type="text" name="saraf_perifer" value="{{ $v('saraf_perifer') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Saraf perifer...">
                            @error('saraf_perifer') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Vaskular <span class="text-red-500">*</span></label>
                            <textarea name="vaskular" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Kondisi vaskular...">{{ $v('vaskular') }}</textarea>
                            @error('vaskular') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= TANDA VITAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-rose-50 border-b border-slate-200">
                    <span class="text-rose-700 font-medium text-[15px]">Tanda Vital</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Sistolik <span class="text-red-500">*</span></label>
                            <input type="number" name="sistolik" value="{{ $v('sistolik') }}" min="0" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mmHg">
                            @error('sistolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Diastolik <span class="text-red-500">*</span></label>
                            <input type="number" name="diastolik" value="{{ $v('diastolik') }}" min="0" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mmHg">
                            @error('diastolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Nadi <span class="text-red-500">*</span></label>
                            <input type="number" name="nadi" value="{{ $v('nadi') }}" min="0" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="x/menit">
                            @error('nadi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Pernapasan <span class="text-red-500">*</span></label>
                            <input type="number" name="pernapasan" value="{{ $v('pernapasan') }}" min="0" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="x/menit">
                            @error('pernapasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Suhu <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="suhu" value="{{ $v('suhu') }}" min="0" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="°C">
                            @error('suhu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Saturasi <span class="text-red-500">*</span></label>
                            <input type="number" name="saturasi" value="{{ $v('saturasi') }}" min="0" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="%">
                            @error('saturasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kesadaran <span class="text-red-500">*</span></label>
                            <select name="kesadaran" class="w-full border rounded-sm px-3 py-2 text-sm">
                                {!! \App\Helpers\SelectOption::render('kesadaran', $v('kesadaran'), '-- Pilih Kesadaran --') !!}
                            </select>
                            @error('kesadaran') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= INTRAOPERATIF & PASCA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Intraoperatif &amp; Pasca Anestesia</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Obat &amp; Cairan Intraoperatif <span class="text-red-500">*</span></label>
                        <textarea name="obat_cairan_intraop" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Obat & cairan intraoperatif...">{{ $v('obat_cairan_intraop') }}</textarea>
                        @error('obat_cairan_intraop') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Kejadian Tidak Terduga <span class="text-red-500">*</span></label>
                        <textarea name="kejadian_tidak_terduga" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Kejadian tidak terduga...">{{ $v('kejadian_tidak_terduga') }}</textarea>
                        @error('kejadian_tidak_terduga') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Penanganan Intraoperatif</label>
                        <textarea name="penanganan_intraop" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Wajib diisi bila ada kejadian tidak terduga...">{{ $v('penanganan_intraop') }}</textarea>
                        @error('penanganan_intraop') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Kondisi Akhir Anestesia <span class="text-red-500">*</span></label>
                            <textarea name="kondisi_akhir" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Kondisi akhir...">{{ $v('kondisi_akhir') }}</textarea>
                            @error('kondisi_akhir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Instruksi Pasca Anestesia <span class="text-red-500">*</span></label>
                            <textarea name="instruksi_pasca" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Instruksi pasca anestesia...">{{ $v('instruksi_pasca') }}</textarea>
                            @error('instruksi_pasca') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Ruang Tujuan <span class="text-red-500">*</span></label>
                            <x-select-ruang-perawatan name="ruang_tujuan" id="ruang_tujuan" :selected="$v('ruang_tujuan')" />
                            @error('ruang_tujuan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                var radiosJenis = Array.prototype.slice.call(document.querySelectorAll('[name="jenis_anestesi"]'));
                var blokCaudel = document.getElementById('blok-caudel');
                var inMulai = document.querySelector('[name="jam_mulai"]');
                var inSelesai = document.querySelector('[name="jam_selesai"]');
                var preview = document.getElementById('preview-lama');
                var hiddenLama = document.getElementById('field-lama');

                function syncCaudel() {
                    var aktif = radiosJenis.find(function (r) { return r.checked; });
                    var nilai = aktif ? aktif.value : '';
                    var neuraxial = nilai === 'Epidural' || nilai === 'Spinal';
                    if (blokCaudel) { blokCaudel.style.display = neuraxial ? '' : 'none'; }
                }

                function hitungLama() {
                    if (!inMulai || !inSelesai) { return; }
                    var m = inMulai.value;
                    var s = inSelesai.value;
                    if (!m || !s) { return; }
                    var tm = m.split(':');
                    var ts = s.split(':');
                    var menit = (parseInt(ts[0], 10) * 60 + parseInt(ts[1], 10)) - (parseInt(tm[0], 10) * 60 + parseInt(tm[1], 10));
                    if (menit < 0) { return; }
                    var teks = menit + ' menit';
                    if (preview) { preview.value = teks; }
                    if (hiddenLama) { hiddenLama.value = teks; }
                }

                radiosJenis.forEach(function (r) { r.addEventListener('change', syncCaudel); });
                if (inMulai) { inMulai.addEventListener('change', hitungLama); }
                if (inSelesai) { inSelesai.addEventListener('change', hitungLama); }
                syncCaudel();
                hitungLama();
            })();
        </script>
    </x-emr-split-layout>
@endsection
