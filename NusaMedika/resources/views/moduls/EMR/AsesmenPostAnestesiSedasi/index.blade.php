@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Asesmen Post-Anestesi & Sedasi';
        $subtitleForm = 'Asesmen pasca-anestesi: skor pemulihan Aldrete/Bromage/Steward/PADSS, tanda vital, komplikasi & rekomendasi.';
        $routeName = null;
        $routeUrl = url('emr/form/asesmen_post_anestesi_sedasi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_asesmen' => 'Tanggal',
            'jam_asesmen' => 'Jam',
            'keadaan_umum' => 'Keadaan Umum',
        ];

        $opsiYaTidak = ['Ya', 'Tidak'];
        $opsiKesadaran = \App\Helpers\SelectOption::get('kesadaran');
        $opsiKeadaanUmum = \App\Helpers\SelectOption::get('keadaan_umum_pulih');

        // Item skor — label deskriptif dari tabel KONSEP §5.
        $aldereteItems = [
            'skor_alderete_kesadaran' => 'Kesadaran',
            'skor_alderete_aktivitas' => 'Aktivitas',
            'skor_alderete_ventilasi' => 'Ventilasi',
            'skor_alderete_warna_kulit' => 'Warna Kulit',
        ];
        $aldereteOpsi = [
            '2' => '2 — Sadar penuh / bergerak 4 ekstremitas / 8–10 respirasi menit / pink',
            '1' => '1 — Dapat dibangunkan / 2 ekstremitas / 6–8 respirasi menit / pucat',
            '0' => '0 — Tidak merespons / tidak bergerak / butuh ventilasi / biru-abu',
        ];
        $bromageItems = [
            'skor_bromage_tangan' => 'Tangan',
            'skor_bromage_lengan' => 'Lengan',
            'skor_bromage_kaki' => 'Kaki',
        ];
        $bromageOpsi = [
            '0' => '0 — Fleksi penuh (mobile penuh)',
            '1' => '1 — Fleksi > 90°, sedikit movemen',
            '2' => '2 — Fleksi < 90°',
            '3' => '3 — Tidak dapat bergerak',
        ];
        $stewardItems = [
            'skor_steward_pergerakan' => 'Pergerakan',
            'skor_steward_pernapasan' => 'Pernapasan',
            'skor_steward_kesadaran' => 'Kesadaran',
        ];
        $stewardOpsi = [
            '3' => '3 — Bergerak 4 ekstremitas / napas baik / responsif',
            '2' => '2 — Bergerak 2–3 ekstremitas / dispnea ringan / tidur dapat dibangunkan',
            '1' => '1 — Tidak bergerak / apnea',
            '0' => '0 — Tidak ada respons',
        ];
        $padssItems = [
            'skor_padss_vas' => 'Tanda Vital',
            'skor_padss_aktivitas' => 'Aktivitas',
        ];
        $padssOpsi = [
            '3' => '3 — Sesuai kriteria pasca-anestesi',
            '2' => '2 — Sedikit menyimpang',
            '1' => '1 — Menyimpang',
            '0' => '0 — Di luar kriteria',
        ];

        $komplikasiLabels = [
            'komplikasi_1' => 'Mual',
            'komplikasi_2' => 'Muntah',
            'komplikasi_3' => 'Menggigil',
            'komplikasi_4' => 'Hipotensi',
            'komplikasi_5' => 'Hipertensi',
            'komplikasi_6' => 'Vasoplegia',
        ];
        $keselamatanLabels = [
            'keselamatan_id' => 'Gelang identitas terpasang & sesuai',
            'keselamatan_lokasi' => 'Lokasi operasi telah dikonfirmasi',
            'keselamatan_site' => 'Site operasi telah dikonfirmasi',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Post-Anestesi"
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
                slug="asesmen_post_anestesi_sedasi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS ASESMEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Asesmen Pasca-Anestesi</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Asesmen <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_asesmen" value="{{ $v('tanggal_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Asesmen <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_asesmen" value="{{ $v('jam_asesmen') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <x-select_dokter
                            name="dokter_anestesi_id"
                            id="dokter_anestesi_id"
                            label="Dokter Anestesi Penilai"
                            placeholder="-- Pilih Dokter --"
                            :selected="$v('dokter_anestesi_id')"
                            :required="true" />
                    </div>
                </div>
            </div>

            {{-- ================= SKOR PEMULIHAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Skor Pemulihan Pasca-Anestesi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                        {{-- Aldrete --}}
                        <div class="border border-slate-200 rounded-sm p-4 bg-slate-50/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-600">Skor Aldrete (0–10)</span>
                                <span class="text-xs text-slate-500">Total: <b id="badge-alderete">—</b></span>
                            </div>
                            <div class="space-y-3">
                                @foreach ($aldereteItems as $key => $label)
                                    <div>
                                        <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                                        <select name="{{ $key }}" data-skor class="w-full border rounded-sm px-3 py-2 text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($aldereteOpsi as $ov => $ol)
                                                <option value="{{ $ov }}" {{ $v($key) === $ov ? 'selected' : '' }}>{{ $ol }}</option>
                                            @endforeach
                                        </select>
                                        @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <label class="block text-xs font-medium mb-1">Total Aldrete (otomatis)</label>
                                <input type="number" name="skor_alderete_total" id="total-alderete" value="{{ $v('skor_alderete_total') }}" readonly class="w-full border border-slate-200 bg-slate-100 rounded-sm px-3 py-2 text-sm font-semibold">
                            </div>
                        </div>

                        {{-- Bromage --}}
                        <div class="border border-slate-200 rounded-sm p-4 bg-slate-50/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-600">Skor Bromage (0–9)</span>
                                <span class="text-xs text-slate-500">Total: <b id="badge-bromage">—</b></span>
                            </div>
                            <div class="space-y-3">
                                @foreach ($bromageItems as $key => $label)
                                    <div>
                                        <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                                        <select name="{{ $key }}" data-skor class="w-full border rounded-sm px-3 py-2 text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($bromageOpsi as $ov => $ol)
                                                <option value="{{ $ov }}" {{ $v($key) === $ov ? 'selected' : '' }}>{{ $ol }}</option>
                                            @endforeach
                                        </select>
                                        @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <label class="block text-xs font-medium mb-1">Total Bromage (otomatis)</label>
                                <input type="number" name="skor_bromage_total" id="total-bromage" value="{{ $v('skor_bromage_total') }}" readonly class="w-full border border-slate-200 bg-slate-100 rounded-sm px-3 py-2 text-sm font-semibold">
                            </div>
                        </div>

                        {{-- Steward --}}
                        <div class="border border-slate-200 rounded-sm p-4 bg-slate-50/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-600">Skor Steward (0–9)</span>
                                <span class="text-xs text-slate-500">Total: <b id="badge-steward">—</b></span>
                            </div>
                            <div class="space-y-3">
                                @foreach ($stewardItems as $key => $label)
                                    <div>
                                        <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                                        <select name="{{ $key }}" data-skor class="w-full border rounded-sm px-3 py-2 text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($stewardOpsi as $ov => $ol)
                                                <option value="{{ $ov }}" {{ $v($key) === $ov ? 'selected' : '' }}>{{ $ol }}</option>
                                            @endforeach
                                        </select>
                                        @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <label class="block text-xs font-medium mb-1">Total Steward (otomatis)</label>
                                <input type="number" name="skor_steward_total" id="total-steward" value="{{ $v('skor_steward_total') }}" readonly class="w-full border border-slate-200 bg-slate-100 rounded-sm px-3 py-2 text-sm font-semibold">
                            </div>
                        </div>

                        {{-- PADSS --}}
                        <div class="border border-slate-200 rounded-sm p-4 bg-slate-50/50">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-600">Skor PADSS</span>
                                <span class="text-xs text-slate-500">Total: <b id="badge-padss">—</b></span>
                            </div>
                            <div class="space-y-3">
                                @foreach ($padssItems as $key => $label)
                                    <div>
                                        <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                                        <select name="{{ $key }}" data-skor class="w-full border rounded-sm px-3 py-2 text-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($padssOpsi as $ov => $ol)
                                                <option value="{{ $ov }}" {{ $v($key) === $ov ? 'selected' : '' }}>{{ $ol }}</option>
                                            @endforeach
                                        </select>
                                        @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <label class="block text-xs font-medium mb-1">Total PADSS (otomatis)</label>
                                <input type="number" name="skor_padss_total" id="total-padss" value="{{ $v('skor_padss_total') }}" readonly class="w-full border border-slate-200 bg-slate-100 rounded-sm px-3 py-2 text-sm font-semibold">
                            </div>
                        </div>
                    </div>

                    {{-- Kriteria pemulihan (dihitung server dari Aldrete) --}}
                    <div class="border border-slate-200 rounded-sm p-4 bg-white">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-600">Kriteria Pemulihan <span class="text-red-500">*</span></span>
                            <input type="hidden" name="kriteria_pemulihan" id="kriteria-pemulihan" value="{{ $v('kriteria_pemulihan') ?: 'Belum' }}">
                            <span id="badge-kriteria" class="px-3 py-1 rounded-full text-xs font-semibold border"></span>
                            <span class="text-[11px] text-slate-500">Dihitung otomatis dari Skor Aldrete (&gt; 8 Terpenuhi).</span>
                        </div>
                        @error('kriteria_pemulihan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= TANDA VITAL & KESADARAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanda Vital &amp; Kesadaran Pasca-Anestesi</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="col-span-2 md:col-span-4">
                            <label class="block text-xs font-medium mb-1">Kesadaran <span class="text-red-500">*</span></label>
                            <select name="kesadaran" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Kesadaran --</option>
                                @foreach ($opsiKesadaran as $o)
                                    <option value="{{ $o['value'] }}" {{ $v('kesadaran') === $o['value'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                                @endforeach
                            </select>
                            @error('kesadaran') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        @foreach ([
                            'sistolik' => 'TD Sistolik (mmHg)',
                            'diastolik' => 'TD Diastolik (mmHg)',
                            'nadi' => 'Nadi (x/menit)',
                            'pernapasan' => 'Pernapasan (x/menit)',
                            'suhu' => 'Suhu (°C)',
                            'saturasi' => 'Saturasi (%)',
                        ] as $key => $label)
                            <div>
                                <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                                <input type="number" step="any" name="{{ $key }}" value="{{ $v($key) }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= NYERI & KOMPLIKASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Nyeri &amp; Komplikasi Post-Anestesi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Nyeri <span class="text-red-500">*</span></label>
                            <div class="flex gap-4 pt-1">
                                @foreach ($opsiYaTidak as $o)
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="nyeri" value="{{ $o }}" {{ $v('nyeri') === $o ? 'checked' : '' }}>
                                        <span>{{ $o }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('nyeri') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div id="blok-skala-nyeri">
                            <label class="block text-xs font-medium mb-1">Skala Nyeri (VAS 0–10) <span class="text-red-500">*</span></label>
                            <input type="number" name="skala_nyeri" min="0" max="10" value="{{ $v('skala_nyeri') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('skala_nyeri') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Komplikasi Post-Anestesi</label>
                        <div class="flex flex-wrap gap-x-6 gap-y-2 pt-1">
                            @foreach ($komplikasiLabels as $key => $label)
                                <label class="inline-flex items-center gap-1.5">
                                    <input type="hidden" name="{{ $key }}" value="0">
                                    <input type="checkbox" name="{{ $key }}" value="1" {{ $v($key) === '1' ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Centang bila komplikasi terjadi.</p>
                    </div>
                </div>
            </div>

            {{-- ================= PASCA PULIH ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Pasca Pemulihan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Intake Oral <span class="text-red-500">*</span></label>
                            <div class="flex gap-4 pt-1">
                                @foreach ($opsiYaTidak as $o)
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="intake_oral_ada" value="{{ $o }}" {{ $v('intake_oral_ada') === $o ? 'checked' : '' }}>
                                        <span>{{ $o }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('intake_oral_ada') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Mobilisasi <span class="text-red-500">*</span></label>
                            <div class="flex gap-4 pt-1">
                                @foreach ($opsiYaTidak as $o)
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="mobilisasi" value="{{ $o }}" {{ $v('mobilisasi') === $o ? 'checked' : '' }}>
                                        <span>{{ $o }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('mobilisasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Keadaan Umum <span class="text-red-500">*</span></label>
                            <select name="keadaan_umum" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih --</option>
                                @foreach ($opsiKeadaanUmum as $o)
                                    <option value="{{ $o['value'] }}" {{ $v('keadaan_umum') === $o['value'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                                @endforeach
                            </select>
                            @error('keadaan_umum') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div id="blok-intake-catatan">
                            <label class="block text-xs font-medium mb-1">Catatan Intake Oral <span class="text-red-500" id="bintang-intake">*</span></label>
                            <input type="text" name="intake_oral_catatan" value="{{ $v('intake_oral_catatan') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('intake_oral_catatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div id="blok-mobilisasi-catatan">
                            <label class="block text-xs font-medium mb-1">Catatan Mobilisasi <span class="text-red-500" id="bintang-mobilisasi">*</span></label>
                            <input type="text" name="mobilisasi_catatan" value="{{ $v('mobilisasi_catatan') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('mobilisasi_catatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= KESELAMATAN PASIEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Keselamatan Pasien <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="flex flex-wrap gap-x-6 gap-y-2">
                        @foreach ($keselamatanLabels as $key => $label)
                            <label class="inline-flex items-center gap-1.5">
                                <input type="hidden" name="{{ $key }}" value="0">
                                <input type="checkbox" name="{{ $key }}" value="1" {{ $v($key) === '1' ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('keselamatan_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @error('keselamatan_lokasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @error('keselamatan_site') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= RUANG TUJUAN & FORMULIR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Ruang Tujuan &amp; Formulir</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Ruang Perawatan Tujuan <span class="text-red-500">*</span></label>
                            <x-select-ruang-perawatan name="ruangan_tujuan" id="ruangan_tujuan" :selected="$v('ruangan_tujuan')" />
                            @error('ruangan_tujuan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Formulir Pasien Pulih Tertunda</label>
                            <textarea name="formulir_tertunda" rows="3" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('formulir_tertunda') }}</textarea>
                            @error('formulir_tertunda') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= REKOMENDASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Rekomendasi Pasien Pulih <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <textarea name="rekomendasi" rows="3" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Rekomendasi tindak lanjut pasca-anestesi...">{{ $v('rekomendasi') }}</textarea>
                    @error('rekomendasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
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

                var nFormatter = function (el, total) {
                    el.value = (total === null || isNaN(total)) ? '' : String(total);
                };

                // ===== Hitung total skor (cerminan SkorPemulihanHelper) =====
                function hitungTotal(keys, max) {
                    var sum = 0;
                    for (var i = 0; i < keys.length; i++) {
                        var s = document.querySelector('select[name="' + keys[i] + '"]');
                        if (!s || s.value === '') return null;
                        var n = +s.value;
                        if (isNaN(n) || n < 0 || n > max) return null;
                        sum += n;
                    }
                    return sum;
                }
                var aldereteKeys = ['skor_alderete_kesadaran', 'skor_alderete_aktivitas', 'skor_alderete_ventilasi', 'skor_alderete_warna_kulit'];
                var bromageKeys = ['skor_bromage_tangan', 'skor_bromage_lengan', 'skor_bromage_kaki'];
                var stewardKeys = ['skor_steward_pergerakan', 'skor_steward_pernapasan', 'skor_steward_kesadaran'];
                var padssKeys = ['skor_padss_vas', 'skor_padss_aktivitas'];

                function hitungSkor() {
                    var a = hitungTotal(aldereteKeys, 2);
                    var b = hitungTotal(bromageKeys, 3);
                    var st = hitungTotal(stewardKeys, 3);
                    var p = hitungTotal(padssKeys, 3);

                    nFormatter(document.getElementById('total-alderete'), a);
                    nFormatter(document.getElementById('total-bromage'), b);
                    nFormatter(document.getElementById('total-steward'), st);
                    nFormatter(document.getElementById('total-padss'), p);
                    document.getElementById('badge-alderete').textContent = a === null ? '—' : a + '/10';
                    document.getElementById('badge-bromage').textContent = b === null ? '—' : b + '/9';
                    document.getElementById('badge-steward').textContent = st === null ? '—' : st + '/9';
                    document.getElementById('badge-padss').textContent = p === null ? '—' : p + '/6';

                    // Kriteria pemulihan dari ambang Aldrete (server menghitung ulang).
                    var badge = document.getElementById('badge-kriteria');
                    var hidden = document.getElementById('kriteria-pemulihan');
                    if (a === null) {
                        badge.textContent = 'Belum';
                        badge.className = 'px-3 py-1 rounded-full text-xs font-semibold border bg-slate-50 text-slate-500 border-slate-300';
                        hidden.value = 'Belum';
                    } else if (a <= 5) {
                        badge.textContent = 'Belum — Rujuk ICU';
                        badge.className = 'px-3 py-1 rounded-full text-xs font-semibold border bg-red-50 text-red-700 border-red-300';
                        hidden.value = 'Belum';
                    } else if (a <= 8) {
                        badge.textContent = 'Belum — Perlu Pemantauan';
                        badge.className = 'px-3 py-1 rounded-full text-xs font-semibold border bg-yellow-50 text-yellow-700 border-yellow-300';
                        hidden.value = 'Belum';
                    } else {
                        badge.textContent = 'Terpenuhi — Layak ke Ruang';
                        badge.className = 'px-3 py-1 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-300';
                        hidden.value = 'Terpenuhi';
                    }
                }
                document.querySelectorAll('select[data-skor]').forEach(function (s) {
                    s.addEventListener('change', hitungSkor);
                });
                hitungSkor();

                // ===== Toggle kondisional =====
                function syncNyeri() { tampilkan(document.getElementById('blok-skala-nyeri'), sel('nyeri') === 'Ya'); }
                document.querySelectorAll('input[name="nyeri"]').forEach(function (r) { r.addEventListener('change', syncNyeri); });
                syncNyeri();

                function syncIntake() {
                    var ya = sel('intake_oral_ada') === 'Ya';
                    tampilkan(document.getElementById('blok-intake-catatan'), ya);
                    tampilkan(document.getElementById('bintang-intake'), ya);
                }
                document.querySelectorAll('input[name="intake_oral_ada"]').forEach(function (r) { r.addEventListener('change', syncIntake); });
                syncIntake();

                function syncMobilisasi() {
                    var tidak = sel('mobilisasi') === 'Tidak';
                    tampilkan(document.getElementById('blok-mobilisasi-catatan'), tidak);
                    tampilkan(document.getElementById('bintang-mobilisasi'), tidak);
                }
                document.querySelectorAll('input[name="mobilisasi"]').forEach(function (r) { r.addEventListener('change', syncMobilisasi); });
                syncMobilisasi();
            })();
        </script>
    </x-emr-split-layout>
@endsection
