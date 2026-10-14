@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Evaluasi Pre-Anestesi & Sedasi';
        $subtitleForm = 'Evaluasi pra-anestesi: anamnesis per sistem organ, skor ASA, rencana & persetujuan anestesi.';
        $routeName = null;
        $routeUrl = url('emr/form/evaluasi_pre_anestesi_sedasi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_evaluasi' => 'Tanggal',
            'jam_evaluasi' => 'Jam',
            'tipe_anestesia' => 'Tipe Anestesi',
        ];

        // Radio Ya/Tidak per panel — [variabel => label].
        $radioYaTidak = [
            'jalan_nafas' => 'Ada gangguan jalan napas / tidur',
            'snoring' => 'Mengorok (snoring) saat tidur',
            'pernapasan' => 'Keluhan sistem pernapasan',
            'jantung' => 'Keluhan kardiovaskular',
            'op_jantung_koroner' => 'Riwayat operasi jantung koroner',
            'serangan_jantung' => 'Riwayat serangan jantung',
            'hipertensi' => 'Riwayat hipertensi',
            'saraf_perifer' => 'Keluhan saraf perifer',
            'pingsan' => 'Riwayat pingsan',
            'kejang' => 'Riwayat kejang',
            'stroke' => 'Riwayat stroke',
            'diabetes' => 'Riwayat diabetes',
            'gangguan_tiroid' => 'Gangguan tiroid',
            'obesitas' => 'Obesitas',
            'ginjal_gagal' => 'Gagal ginjal',
            'susah_kencing' => 'Susah kencing / BAK terganggu',
            'abdomen' => 'Keluhan abdomen',
            'maag' => 'Maag',
            'muntah' => 'Muntah',
            'faring' => 'Keluhan faring',
            'gigi_geligi' => 'Keluhan gigi & geligi',
            'mobilitas' => 'Masalah mobilitas leher',
            'anemia' => 'Anemia',
            'kajar_aspirasi' => 'Risiko aspirasi',
            'ceklis_equip_emergi' => 'Peralatan darurat perlu disiapkan (cek list equip. emergi)',
            'anes_umum' => 'Rencana anestesi umum (general)',
            'anes_regional' => 'Rencana anestesi regional',
            'sedasi' => 'Rencana sedasi',
        ];

        $radioAlergi = [
            'alergi_latraks' => 'Alergi obat (latraks)',
            'alergi_makanan' => 'Alergi makanan',
            'alergi_plaster' => 'Alergi plaster / plester',
        ];

        $opsiAlergi = ['Ada', 'Tidak'];
        $opsiPekerjaan = \App\Helpers\SelectOption::get('pekerjaan');
        $opsiAsa = \App\Helpers\SelectOption::get('asa_kelas');
        // KONSEP §4: tipe anestesi hanya Umum / Regional / Lokal / Sedasi
        // (SelectOption key "jenis_anestesia" memuat 6 opsi utk form lain).
        $opsiTipeAnes = array_values(array_filter(
            \App\Helpers\SelectOption::get('jenis_anestesia'),
            fn ($o) => in_array($o['value'], ['Umum', 'Regional', 'Lokal', 'Sedasi'], true)
        ));
        $opsiYaTidak = ['Ya', 'Tidak'];
        $opsiSudahBelum = ['Sudah', 'Belum'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Evaluasi Pre-Anestesi"
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
                slug="evaluasi_pre_anestesi_sedasi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS & ANAMNESIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas & Anamnesis Pre-Anestesi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Evaluasi <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_evaluasi" value="{{ $v('tanggal_evaluasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Evaluasi <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_evaluasi" value="{{ $v('jam_evaluasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <x-select_dokter
                            name="dokter_anestesi_id"
                            id="dokter_anestesi_id"
                            label="Dokter Anestesi Penilai"
                            placeholder="-- Pilih Dokter --"
                            :selected="$v('dokter_anestesi_id')"
                            :required="true" />
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Nama Pasien <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_pasien" value="{{ $v('nama_pasien') }}" readonly class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm">
                            @error('nama_pasien') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_lahir" value="{{ $v('tanggal_lahir') }}" readonly class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm">
                            <p class="text-[11px] text-slate-500 mt-1">Umur: <span id="umur-preview">—</span></p>
                            @error('tanggal_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <div class="flex gap-4 pt-1">
                                @foreach (['L', 'P'] as $jk)
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="jenis_kelamin" value="{{ $jk }}" {{ $v('jenis_kelamin') === $jk ? 'checked' : '' }}>
                                        <span>{{ $jk === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('jenis_kelamin') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Pekerjaan <span class="text-red-500">*</span></label>
                            <select name="pekerjaan" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih --</option>
                                @foreach ($opsiPekerjaan as $opsi)
                                    <option value="{{ $opsi['value'] }}" {{ $v('pekerjaan') === $opsi['value'] ? 'selected' : '' }}>{{ $opsi['label'] }}</option>
                                @endforeach
                            </select>
                            @error('pekerjaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Riwayat Penyakit Keluarga <span class="text-red-500">*</span></label>
                            <textarea name="riwayat_penyakit_keluarga" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('riwayat_penyakit_keluarga') }}</textarea>
                            @error('riwayat_penyakit_keluarga') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Riwayat Pengobatan <span class="text-red-500">*</span></label>
                            <textarea name="riwayat_pengobatan" rows="3" placeholder="Obat yang dikonsumsi saat ini (termasuk antiplatelet/antikoagulan)." class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('riwayat_pengobatan') }}</textarea>
                            @error('riwayat_pengobatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= ALERGI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Alergi Pre-Anestesi <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($radioAlergi as $key => $label)
                            <div>
                                <span class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></span>
                                <div class="flex gap-4">
                                    @foreach ($opsiAlergi as $opsi)
                                        <label class="inline-flex items-center gap-1">
                                            <input type="radio" name="{{ $key }}" value="{{ $opsi }}" {{ $v($key) === $opsi ? 'checked' : '' }}>
                                            <span>{{ $opsi }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Alergi Obat Timbul / Keterangan <span class="text-red-500" id="bintang-alergi" style="{{ $v('alergi_latraks') === 'Ada' ? '' : 'display:none' }}">*</span></label>
                        <textarea name="alergi_obat_timbul" rows="2" placeholder="Sebutkan obat yang menimbulkan alergi — wajib diisi bila ada alergi obat." class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('alergi_obat_timbul') }}</textarea>
                        @error('alergi_obat_timbul') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= JALAN NAPAS & TIDUR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Jalan Napas & Tidur <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    @foreach (['jalan_nafas', 'snoring'] as $key)
                        <div>
                            <span class="block text-xs font-medium mb-1">{{ $radioYaTidak[$key] }} <span class="text-red-500">*</span></span>
                            <div class="flex gap-4">
                                @foreach ($opsiYaTidak as $opsi)
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="{{ $key }}" value="{{ $opsi }}" {{ $v($key) === $opsi ? 'checked' : '' }}>
                                        <span>{{ $opsi }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-medium mb-1">Gangguan Pernapasan <span class="text-red-500" id="bintang-gp" style="{{ $v('jalan_nafas') === 'Ya' ? '' : 'display:none' }}">*</span></label>
                        <textarea name="gangguan_pernafasan" rows="2" placeholder="Wajib diisi bila ada gangguan jalan napas." class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('gangguan_pernafasan') }}</textarea>
                        @error('gangguan_pernafasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= SISTEM PERNAPASAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Sistem Pernapasan <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <span class="block text-xs font-medium mb-1">{{ $radioYaTidak['pernapasan'] }} <span class="text-red-500">*</span></span>
                        <div class="flex gap-4">
                            @foreach ($opsiYaTidak as $opsi)
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="pernapasan" value="{{ $opsi }}" {{ $v('pernapasan') === $opsi ? 'checked' : '' }}>
                                    <span>{{ $opsi }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('pernapasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Penyakit Paru (keterangan)</label>
                            <textarea name="penyakit_paru" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('penyakit_paru') }}</textarea>
                            @error('penyakit_paru') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-3 gap-3 items-start">
                            <div>
                                <label class="block text-xs font-medium mb-1">Suhu (°C) <span class="text-red-500">*</span></label>
                                <input type="number" step="0.1" name="suhu" value="{{ $v('suhu') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('suhu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">TD (mmHg) <span class="text-red-500">*</span></label>
                                <input type="text" name="td" value="{{ $v('td') }}" placeholder="120/80" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <p class="text-[11px] mt-1" id="badge-td"></p>
                                @error('td') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Nadi <span class="text-red-500">*</span></label>
                                <input type="number" name="nadi" value="{{ $v('nadi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('nadi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= SISTEM KARDIOVASKULAR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Sistem Kardiovaskular <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @foreach (['jantung', 'op_jantung_koroner', 'serangan_jantung', 'hipertensi'] as $key)
                            <div>
                                <span class="block text-xs font-medium mb-1">{{ $radioYaTidak[$key] }} <span class="text-red-500">*</span></span>
                                <div class="flex gap-4">
                                    @foreach ($opsiYaTidak as $opsi)
                                        <label class="inline-flex items-center gap-1">
                                            <input type="radio" name="{{ $key }}" value="{{ $opsi }}" {{ $v($key) === $opsi ? 'checked' : '' }}>
                                            <span>{{ $opsi }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Riwayat Transfusi</label>
                        <textarea name="riwayat_transfusi" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('riwayat_transfusi') }}</textarea>
                        @error('riwayat_transfusi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= SISTEM SARAF ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Sistem Saraf <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @foreach (['saraf_perifer', 'pingsan', 'kejang', 'stroke'] as $key)
                            <div>
                                <span class="block text-xs font-medium mb-1">{{ $radioYaTidak[$key] }} <span class="text-red-500">*</span></span>
                                <div class="flex gap-4">
                                    @foreach ($opsiYaTidak as $opsi)
                                        <label class="inline-flex items-center gap-1">
                                            <input type="radio" name="{{ $key }}" value="{{ $opsi }}" {{ $v($key) === $opsi ? 'checked' : '' }}>
                                            <span>{{ $opsi }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Keterangan Saraf</label>
                        <textarea name="saraf" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('saraf') }}</textarea>
                        @error('saraf') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= SISTEM ENDOKRIN & METABOLIK ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Sistem Endokrin & Metabolik <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div id="blok-hamil" style="{{ $v('jenis_kelamin') === 'P' ? '' : 'display:none' }}">
                            <label class="block text-xs font-medium mb-1">Umur Kehamilan (bulan) <span class="text-red-500">*</span></label>
                            <input type="number" name="hamil_bulan" min="1" max="9" value="{{ $v('hamil_bulan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <p class="text-[11px] text-slate-500 mt-1">Wajib untuk perempuan &lt; 60 tahun.</p>
                            @error('hamil_bulan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        @foreach (['diabetes', 'gangguan_tiroid', 'obesitas'] as $key)
                            <div>
                                <span class="block text-xs font-medium mb-1">{{ $radioYaTidak[$key] }} <span class="text-red-500">*</span></span>
                                <div class="flex gap-4">
                                    @foreach ($opsiYaTidak as $opsi)
                                        <label class="inline-flex items-center gap-1">
                                            <input type="radio" name="{{ $key }}" value="{{ $opsi }}" {{ $v($key) === $opsi ? 'checked' : '' }}>
                                            <span>{{ $opsi }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="max-w-xs">
                        <label class="block text-xs font-medium mb-1">Gula Darah (mg/dL) <span class="text-red-500">*</span></label>
                        <input type="number" name="gula_darah" value="{{ $v('gula_darah') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('gula_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= SISTEM GINJAL & KENCING ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Sistem Ginjal & Kencing <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach (['ginjal_gagal', 'susah_kencing'] as $key)
                            <div>
                                <span class="block text-xs font-medium mb-1">{{ $radioYaTidak[$key] }} <span class="text-red-500">*</span></span>
                                <div class="flex gap-4">
                                    @foreach ($opsiYaTidak as $opsi)
                                        <label class="inline-flex items-center gap-1">
                                            <input type="radio" name="{{ $key }}" value="{{ $opsi }}" {{ $v($key) === $opsi ? 'checked' : '' }}>
                                            <span>{{ $opsi }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-md">
                        <div>
                            <label class="block text-xs font-medium mb-1">Kreatinin <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="kreatinin" value="{{ $v('kreatinin') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('kreatinin') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Ureum <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="ureum" value="{{ $v('ureum') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('ureum') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= SISTEM PENCERNAAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Sistem Pencernaan <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach (['abdomen', 'maag', 'muntah', 'faring', 'gigi_geligi', 'mobilitas'] as $key)
                            <div>
                                <span class="block text-xs font-medium mb-1">{{ $radioYaTidak[$key] }} <span class="text-red-500">*</span></span>
                                <div class="flex gap-4">
                                    @foreach ($opsiYaTidak as $opsi)
                                        <label class="inline-flex items-center gap-1">
                                            <input type="radio" name="{{ $key }}" value="{{ $opsi }}" {{ $v($key) === $opsi ? 'checked' : '' }}>
                                            <span>{{ $opsi }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= SISTEM DARAH & KOAGULASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Sistem Darah & Koagulasi <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <span class="block text-xs font-medium mb-1">{{ $radioYaTidak['anemia'] }} <span class="text-red-500">*</span></span>
                        <div class="flex gap-4">
                            @foreach ($opsiYaTidak as $opsi)
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="anemia" value="{{ $opsi }}" {{ $v('anemia') === $opsi ? 'checked' : '' }}>
                                    <span>{{ $opsi }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('anemia') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1">Leukosit <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="leukosit" value="{{ $v('leukosit') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('leukosit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Trombosit <span class="text-red-500">*</span></label>
                            <input type="number" name="trombosit" value="{{ $v('trombosit') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <p class="text-[11px] mt-1" id="badge-trombosit"></p>
                            @error('trombosit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kadar Gula Darah <span class="text-red-500">*</span></label>
                            <input type="number" name="kadar_gula_darah" value="{{ $v('kadar_gula_darah') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <p class="text-[11px] mt-1" id="badge-gula"></p>
                            @error('kadar_gula_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Prothrombin Time (PT) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="prothrombin_time" value="{{ $v('prothrombin_time') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('prothrombin_time') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= KAJAR & RISIKO ASPIRASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kajar & Risiko Aspirasi <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach (['kajar_aspirasi', 'ceklis_equip_emergi'] as $key)
                            <div>
                                <span class="block text-xs font-medium mb-1">{{ $radioYaTidak[$key] }} <span class="text-red-500">*</span></span>
                                <div class="flex gap-4">
                                    @foreach ($opsiYaTidak as $opsi)
                                        <label class="inline-flex items-center gap-1">
                                            <input type="radio" name="{{ $key }}" value="{{ $opsi }}" {{ $v($key) === $opsi ? 'checked' : '' }}>
                                            <span>{{ $opsi }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Perlu Alat Bantu <span class="text-red-500" id="bintang-alat" style="{{ $v('ceklis_equip_emergi') === 'Ya' ? '' : 'display:none' }}">*</span></label>
                        <textarea name="perlu_alat_bantu" rows="2" placeholder="Wajib diisi bila peralatan darurat diperlukan (Ya)." class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('perlu_alat_bantu') }}</textarea>
                        @error('perlu_alat_bantu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= SKOR ASA & NYERI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Skor ASA & Nyeri <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Klasifikasi ASA <span class="text-red-500">*</span></label>
                            <select name="asa" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Kelas ASA --</option>
                                @foreach ($opsiAsa as $opsi)
                                    <option value="{{ $opsi['value'] }}" {{ $v('asa') === $opsi['value'] ? 'selected' : '' }}>{{ $opsi['label'] }}</option>
                                @endforeach
                            </select>
                            @error('asa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <span class="block text-xs font-medium mb-1">Nyeri <span class="text-red-500">*</span></span>
                            <div class="flex gap-4 pt-1">
                                @foreach ($opsiYaTidak as $opsi)
                                    <label class="inline-flex items-center gap-1">
                                        <input type="radio" name="nyeri" value="{{ $opsi }}" {{ $v('nyeri') === $opsi ? 'checked' : '' }}>
                                        <span>{{ $opsi }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('nyeri') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= RENCANA ANESTESI & SEDASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Rencana Anestesi & Sedasi <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tipe Anestesi <span class="text-red-500">*</span></label>
                            <select name="tipe_anestesia" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Tipe --</option>
                                @foreach ($opsiTipeAnes as $opsi)
                                    <option value="{{ $opsi['value'] }}" {{ $v('tipe_anestesia') === $opsi['value'] ? 'selected' : '' }}>{{ $opsi['label'] }}</option>
                                @endforeach
                            </select>
                            @error('tipe_anestesia') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            @foreach (['anes_umum', 'anes_regional', 'sedasi'] as $key)
                                <div>
                                    <span class="block text-xs font-medium mb-1">{{ $radioYaTidak[$key] }} <span class="text-red-500">*</span></span>
                                    <div class="flex gap-3">
                                        @foreach ($opsiYaTidak as $opsi)
                                            <label class="inline-flex items-center gap-1">
                                                <input type="radio" name="{{ $key }}" value="{{ $opsi }}" {{ $v($key) === $opsi ? 'checked' : '' }}>
                                                <span>{{ $opsi }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PERSETUJUAN & CATATAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Persetujuan & Catatan <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <span class="block text-xs font-medium mb-1">Persetujuan Anestesi <span class="text-red-500">*</span></span>
                        <div class="flex gap-4">
                            @foreach ($opsiSudahBelum as $opsi)
                                <label class="inline-flex items-center gap-1">
                                    <input type="radio" name="persetujuan_anestesi" value="{{ $opsi }}" {{ $v('persetujuan_anestesi') === $opsi ? 'checked' : '' }}>
                                    <span>{{ $opsi }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-red-600 mt-1" id="peringatan-persetujuan" style="{{ $v('persetujuan_anestesi') === 'Belum' ? '' : 'display:none' }}">
                            Persetujuan belum diberikan — simpan akan DITOLAK.
                        </p>
                        @error('persetujuan_anestesi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Catatan Evaluasi Pre-Anestesi <span class="text-red-500">*</span></label>
                        <textarea name="catatan_evaluasi" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('catatan_evaluasi') }}</textarea>
                        @error('catatan_evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                function sel(name) {
                    var el = document.querySelector('input[name="' + name + '"]:checked');
                    return el ? el.value : '';
                }
                function tampilkan(el, tampil, display) {
                    if (!el) return;
                    el.style.display = tampil ? (display || '') : 'none';
                }

                // Umur dari tanggal_lahir (readonly, prefilled pasien).
                var inputLahir = document.querySelector('input[name="tanggal_lahir"]');
                var umurEl = document.getElementById('umur-preview');
                function hitungUmur() {
                    if (!inputLahir || !umurEl) return;
                    var tgl = inputLahir.value;
                    if (!tgl) { umurEl.textContent = '—'; return; }
                    var lahir = new Date(tgl);
                    var kini = new Date();
                    var umur = kini.getFullYear() - lahir.getFullYear();
                    var bulan = kini.getMonth() - lahir.getMonth();
                    if (bulan < 0 || (bulan === 0 && kini.getDate() < lahir.getDate())) umur--;
                    umurEl.textContent = umur >= 0 ? umur + ' tahun' : '—';
                }
                if (inputLahir) {
                    inputLahir.addEventListener('change', hitungUmur);
                    hitungUmur();
                }

                // Blok kehamilan hanya untuk perempuan.
                var blokHamil = document.getElementById('blok-hamil');
                document.querySelectorAll('input[name="jenis_kelamin"]').forEach(function (r) {
                    r.addEventListener('change', function () {
                        tampilkan(blokHamil, sel('jenis_kelamin') === 'P');
                    });
                });

                // Bintang wajib alergi obat / gangguan pernapasan / alat bantu.
                var latraks = document.querySelectorAll('input[name="alergi_latraks"]');
                latraks.forEach(function (r) {
                    r.addEventListener('change', function () {
                        tampilkan(document.getElementById('bintang-alergi'), sel('alergi_latraks') === 'Ada');
                    });
                });
                document.querySelectorAll('input[name="jalan_nafas"]').forEach(function (r) {
                    r.addEventListener('change', function () {
                        tampilkan(document.getElementById('bintang-gp'), sel('jalan_nafas') === 'Ya');
                    });
                });
                document.querySelectorAll('input[name="ceklis_equip_emergi"]').forEach(function (r) {
                    r.addEventListener('change', function () {
                        tampilkan(document.getElementById('bintang-alat'), sel('ceklis_equip_emergi') === 'Ya');
                    });
                });

                // Peringatan persetujuan (server tetap menolak).
                document.querySelectorAll('input[name="persetujuan_anestesi"]').forEach(function (r) {
                    r.addEventListener('change', function () {
                        tampilkan(document.getElementById('peringatan-persetujuan'), sel('persetujuan_anestesi') === 'Belum');
                    });
                });

                // Badge klinis (tampilan saja, KONSEP §4).
                var td = document.querySelector('input[name="td"]');
                var badgeTd = document.getElementById('badge-td');
                function cekTd() {
                    if (!td || !badgeTd) return;
                    var m = (td.value || '').match(/(\d{2,3})\s*\/\s*(\d{2,3})/);
                    if (m && (+m[1] > 180 || +m[2] > 110)) {
                        badgeTd.innerHTML = '<span class="text-red-600 font-semibold">Hipertensi berat — konsultasi anestesi</span>';
                    } else {
                        badgeTd.textContent = '';
                    }
                }
                if (td) { td.addEventListener('input', cekTd); cekTd(); }

                var tro = document.querySelector('input[name="trombosit"]');
                var badgeTro = document.getElementById('badge-trombosit');
                function cekTrombosit() {
                    if (!tro || !badgeTro) return;
                    var val = parseFloat(tro.value);
                    badgeTro.innerHTML = (!isNaN(val) && val > 0 && val < 100000)
                        ? '<span class="text-red-600 font-semibold">Risiko neuraksial</span>' : '';
                }
                if (tro) { tro.addEventListener('input', cekTrombosit); cekTrombosit(); }

                var gula = document.querySelector('input[name="kadar_gula_darah"]');
                var badgeGula = document.getElementById('badge-gula');
                function cekGula() {
                    if (!gula || !badgeGula) return;
                    var val = parseFloat(gula.value);
                    badgeGula.innerHTML = (!isNaN(val) && val > 200)
                        ? '<span class="text-red-600 font-semibold">DM tidak terkontrol</span>' : '';
                }
                if (gula) { gula.addEventListener('input', cekGula); cekGula(); }
            })();
        </script>
    </x-emr-split-layout>
@endsection
