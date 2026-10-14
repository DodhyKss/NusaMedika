@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Pengkajian Awal Keperawatan Perina';
        $subtitleForm = 'Profil ibu, kehamilan, dan SAP neonatal head-to-toe.';
        $routeName = null;
        $routeUrl = url('emr/form/pengkajian_perina');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'nama_bayi' => 'Nama Bayi',
            'tanggal_pengkajian' => 'Tanggal',
            'jenis_kelamin_bayi' => 'JK',
        ];

        $checkboxHamil = [
            'penyakit_hamil_anemia' => 'Anemia', 'penyakit_hamil_jantung' => 'Penyakit Jantung',
            'penyakit_hamil_hipertensi' => 'Hipertensi', 'penyakit_hamil_tb' => 'Tuberkulosis',
            'penyakit_hamil_diabetes' => 'Diabetes', 'penyakit_hamil_sifilis' => 'Sifilis',
            'penyakit_hamil_lain' => 'Lainnya',
        ];
        $checkboxKomplikasi = [
            'komplikasi_pendarahan' => 'Pendarahan', 'komplikasi_peb' => 'PEB',
            'komplikasi_infeksi' => 'Infeksi', 'komplikasi_disproporsi' => 'Disproporsi Feto-Pelvik',
            'komplikasi_lain' => 'Lainnya',
        ];

        $opsiSap = [
            'kepala' => ['Normal', 'Tidak Normal', 'Caput Succedaneum', 'Cephal Hematom', 'Lainnya'],
            'ubun_ubun' => ['Besar', 'Cekung', 'Cembung'],
            'mata' => ['Normal', 'Simetris', 'Tidak Simetris', 'Sclera Kuning', 'Perdarahan'],
            'telinga' => ['Simetris', 'Tidak Simetris', 'Lainnya'],
            'mulut' => ['Simetris', 'Tidak Simetris', 'Labio Schizis', 'Palato Schizis', 'Lainnya'],
            'hidung' => ['Lubang Ada', 'Tidak Ada', 'Kotor', 'Tidak Kotor'],
            'leher' => ['Pergerakan Aktif', 'Kaku', 'Tidak Bereaksi'],
            'menangis' => ['Kuat', 'Lemah', 'Merintih'],
            'punggung' => ['Normal', 'Kifosis', 'Lordosis', 'Skoliosis', 'Spina Bifida', 'Lainnya'],
            'anus' => ['Normal', 'Atresia Ani', 'Lainnya'],
            'genitalia_pria' => ['Normal', 'Hipospadia', 'Phimosis', 'Episdalia', 'Lainnya'],
            'keluaran_genitalia' => ['Tidak Ada Pengeluaran', 'Lendir', 'Darah'],
        ];
        $jumlahButir = [
            'kepala' => 5, 'ubun_ubun' => 3, 'mata' => 5, 'telinga' => 3, 'mulut' => 5,
            'hidung' => 4, 'leher' => 3, 'menangis' => 3, 'punggung' => 6, 'anus' => 3,
            'genitalia_pria' => 5, 'keluaran_genitalia' => 3,
        ];
        $labelButir = [
            'kepala' => 'Kepala', 'ubun_ubun' => 'Ubun-ubun', 'mata' => 'Mata', 'telinga' => 'Telinga',
            'mulut' => 'Mulut', 'hidung' => 'Hidung', 'leher' => 'Leher', 'menangis' => 'Menangis',
            'punggung' => 'Punggung', 'anus' => 'Anus', 'genitalia_pria' => 'Genitalia Pria',
            'keluaran_genitalia' => 'Keluaran Genitalia',
        ];

        $opsiTunggal = [
            'warna_kulit' => ['Merah Muda', 'Pucat', 'Sianosis', 'Ikterus', 'Vernik', 'Rambut Lanugo', 'Rashes', 'Hidrasi'],
            'pergerakan_bayi' => ['Aktif', 'Lemah'],
            'meconium' => ['Ada', 'Tidak Ada'],
            'dada_bayi' => ['Simetris', 'Tidak Simetris'],
            'perut_bayi' => ['Lembek', 'Kembung', 'Teraba Hernia'],
            'umbilikal' => ['Normal', 'Omphalocel'],
            'bising_usus' => ['Ada', 'Tidak Ada'],
            'jari_tangan' => ['Normal', 'Polidactili', 'Syndactili'],
            'pergerakan_tangan' => ['Aktif', 'Lemah'],
            'garis_tangan' => ['Jelas', 'Tidak Jelas'],
            'tangan_keriput' => ['Keriput', 'Tidak Keriput'],
            'tangan_terkelupas' => ['Terkelupas', 'Tidak Terkelupas'],
            'jari_kaki' => ['Normal', 'Polidactili', 'Syndactili'],
            'pergerakan_kaki' => ['Aktif', 'Lemah'],
            'garis_kaki' => ['Jelas', 'Tidak Jelas'],
            'kaki_keriput' => ['Keriput', 'Tidak Keriput'],
            'kaki_terkelupas' => ['Terkelupas', 'Tidak Terkelupas'],
            'kuku' => ['Panjang', 'Pendek'],
            'labio_mayora' => ['Normal', 'Tidak Normal'],
            'labio_minora' => ['Normal', 'Tidak Normal'],
        ];
        $labelTunggal = [
            'warna_kulit' => 'Warna Kulit', 'pergerakan_bayi' => 'Pergerakan Bayi', 'meconium' => 'Meconium',
            'dada_bayi' => 'Dada', 'perut_bayi' => 'Perut', 'umbilikal' => 'Umbilikal', 'bising_usus' => 'Bising Usus',
            'jari_tangan' => 'Jari Tangan', 'pergerakan_tangan' => 'Pergerakan Tangan', 'garis_tangan' => 'Garis Tangan',
            'tangan_keriput' => 'Tangan Keriput', 'tangan_terkelupas' => 'Tangan Terkelupas',
            'jari_kaki' => 'Jari Kaki', 'pergerakan_kaki' => 'Pergerakan Kaki', 'garis_kaki' => 'Garis Kaki',
            'kaki_keriput' => 'Kaki Keriput', 'kaki_terkelupas' => 'Kaki Terkelupas', 'kuku' => 'Kuku',
            'labio_mayora' => 'Labio Mayora', 'labio_minora' => 'Labio Minora',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Pengkajian Perina"
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
                slug="pengkajian_perina"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS PENGAJIAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Pengkajian</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-lg">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Pengkajian <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_pengkajian" value="{{ $v('tanggal_pengkajian') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_pengkajian') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Pengkajian <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_pengkajian" value="{{ $v('jam_pengkajian') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_pengkajian') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= IDENTITAS BAYI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Bayi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Nama Bayi <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_bayi" value="{{ $v('nama_bayi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('nama_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Lahir <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_lahir_bayi" value="{{ $v('tanggal_lahir_bayi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_lahir_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Lahir <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_lahir_bayi" value="{{ $v('jam_lahir_bayi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_lahir_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2"><input type="radio" name="jenis_kelamin_bayi" value="L" {{ $v('jenis_kelamin_bayi') === 'L' ? 'checked' : '' }}> L</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="jenis_kelamin_bayi" value="P" {{ $v('jenis_kelamin_bayi') === 'P' ? 'checked' : '' }}> P</label>
                            </div>
                            @error('jenis_kelamin_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Berat Badan Lahir (kg) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0.3" max="6" name="berat_badan_lahir" value="{{ $v('berat_badan_lahir') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <p class="text-[11px] text-slate-500 mt-1">Satuan kilogram.</p>
                            @error('berat_badan_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tinggi Badan Lahir (cm) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="tinggi_badan_lahir" value="{{ $v('tinggi_badan_lahir') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tinggi_badan_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Lingkar Kepala (cm) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="lingkar_kepala_bayi" value="{{ $v('lingkar_kepala_bayi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('lingkar_kepala_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Denyut Jantung (/menit) <span class="text-red-500">*</span></label>
                            <input type="number" name="denyut_jantung_bayi" value="{{ $v('denyut_jantung_bayi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('denyut_jantung_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Pernapasan (/menit) <span class="text-red-500">*</span></label>
                            <input type="number" name="pernapasan_bayi" value="{{ $v('pernapasan_bayi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('pernapasan_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Suhu Rektal (°C) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="suhu_rectal_bayi" value="{{ $v('suhu_rectal_bayi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('suhu_rectal_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanda Pengenal <span class="text-red-500">*</span></label>
                            <input type="text" name="tanda_pengenal_bayi" value="{{ $v('tanda_pengenal_bayi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanda_pengenal_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tempat Lahir <span class="text-red-500">*</span></label>
                            <input type="text" name="tempat_lahir_bayi" value="{{ $v('tempat_lahir_bayi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tempat_lahir_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= IDENTITAS IBU & AYAH ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Ibu & Ayah</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Nama Ibu <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_ibu" value="{{ $v('nama_ibu') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('nama_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Umur Ibu <span class="text-red-500">*</span></label>
                            <input type="text" name="umur_ibu" value="{{ $v('umur_ibu') }}" placeholder="cth: 28 th" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('umur_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Agama Ibu <span class="text-red-500">*</span></label>
                            <select name="agama_ibu" class="w-full border rounded-sm px-3 py-2 text-sm select2">
                                <option value="">-- Pilih --</option>
                                {!! \App\Helpers\SelectOption::render('agama', $v('agama_ibu')) !!}
                            </select>
                            @error('agama_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Pendidikan Ibu <span class="text-red-500">*</span></label>
                            <select name="pendidikan_ibu" class="w-full border rounded-sm px-3 py-2 text-sm select2">
                                <option value="">-- Pilih --</option>
                                {!! \App\Helpers\SelectOption::render('pendidikan', $v('pendidikan_ibu')) !!}
                            </select>
                            @error('pendidikan_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Pekerjaan Ibu <span class="text-red-500">*</span></label>
                            <select name="pekerjaan_ibu" class="w-full border rounded-sm px-3 py-2 text-sm select2">
                                <option value="">-- Pilih --</option>
                                {!! \App\Helpers\SelectOption::render('pekerjaan', $v('pekerjaan_ibu')) !!}
                            </select>
                            @error('pekerjaan_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Alamat Ibu <span class="text-red-500">*</span></label>
                            <textarea name="alamat_ibu" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('alamat_ibu') }}</textarea>
                            @error('alamat_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Nama Ayah <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_ayah" value="{{ $v('nama_ayah') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('nama_ayah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Umur Ayah <span class="text-red-500">*</span></label>
                            <input type="text" name="umur_ayah" value="{{ $v('umur_ayah') }}" placeholder="cth: 30 th" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('umur_ayah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Agama Ayah</label>
                            <select name="agama_ayah" class="w-full border rounded-sm px-3 py-2 text-sm select2">
                                <option value="">-- Pilih --</option>
                                {!! \App\Helpers\SelectOption::render('agama', $v('agama_ayah')) !!}
                            </select>
                            @error('agama_ayah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-lg">
                        <div>
                            <label class="block text-xs font-medium mb-1">Pendidikan Ayah</label>
                            <select name="pendidikan_ayah" class="w-full border rounded-sm px-3 py-2 text-sm select2">
                                <option value="">-- Pilih --</option>
                                {!! \App\Helpers\SelectOption::render('pendidikan', $v('pendidikan_ayah')) !!}
                            </select>
                            @error('pendidikan_ayah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Pekerjaan Ayah</label>
                            <select name="pekerjaan_ayah" class="w-full border rounded-sm px-3 py-2 text-sm select2">
                                <option value="">-- Pilih --</option>
                                {!! \App\Helpers\SelectOption::render('pekerjaan', $v('pekerjaan_ayah')) !!}
                            </select>
                            @error('pekerjaan_ayah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= KEHAMILAN & ANTENATAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kehamilan & Antenatal</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tinggi Ibu (cm) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="tinggi_ibu" value="{{ $v('tinggi_ibu') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tinggi_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Berat Ibu (kg) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="berat_ibu" value="{{ $v('berat_ibu') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('berat_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Presentasi Bayi <span class="text-red-500">*</span></label>
                            <input type="text" name="presentasi_bayi" value="{{ $v('presentasi_bayi') }}" placeholder="cephalic / breech / transverse" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('presentasi_bayi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-2">Pemeriksaan Antenatal <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2"><input type="radio" name="antenatal_care" value="1" class="opsi-anc" {{ (string) $v('antenatal_care') === '1' ? 'checked' : '' }}> Teratur</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="antenatal_care" value="2" class="opsi-anc" {{ (string) $v('antenatal_care') === '2' ? 'checked' : '' }}> Tidak Teratur</label>
                            </div>
                            @error('antenatal_care') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div id="blok-lokasi-anc" style="{{ (string) $v('antenatal_care') === '1' ? '' : 'display:none' }}">
                            <label class="block text-xs font-medium mb-1">Lokasi ANC <span class="text-red-500">*</span></label>
                            <input type="text" name="lokasi_antenatal_care" value="{{ $v('lokasi_antenatal_care') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('lokasi_antenatal_care') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Imunisasi Hamil <span class="text-red-500">*</span></label>
                            <select name="imunisasi_hamil" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih --</option>
                                <option value="Lengkap" {{ $v('imunisasi_hamil') === 'Lengkap' ? 'selected' : '' }}>Lengkap</option>
                                <option value="Tidak Lengkap" {{ $v('imunisasi_hamil') === 'Tidak Lengkap' ? 'selected' : '' }}>Tidak Lengkap</option>
                            </select>
                            @error('imunisasi_hamil') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">HPHT <span class="text-red-500">*</span></label>
                            <input type="date" name="hpht" value="{{ $v('hpht') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('hpht') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Taksiran Partus <span class="text-red-500">*</span></label>
                            <input type="date" name="taksiran_partus" value="{{ $v('taksiran_partus') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('taksiran_partus') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">USG Ibu</label>
                            @for ($i = 1; $i <= 5; $i++)
                                <input type="text" name="usg_ibu_{{ $i }}" value="{{ $v('usg_ibu_'.$i) }}" placeholder="USG {{ $i }}" class="w-full border rounded-sm px-3 py-2 text-sm mb-1">
                            @endfor
                            @error('usg_ibu_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-2">Penyakit Selama Hamil</label>
                        <div class="flex flex-wrap gap-x-5 gap-y-2">
                            @foreach ($checkboxHamil as $key => $label)
                                <label class="inline-flex items-center gap-2">
                                    <input type="hidden" name="{{ $key }}" value="0">
                                    <input type="checkbox" name="{{ $key }}" value="1" class="cb-hamil" data-key="{{ $key }}" {{ $v($key) == '1' ? 'checked' : '' }}> {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('penyakit_hamil_anemia') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-penyakit-lainnya" style="{{ $v('penyakit_hamil_lain') == '1' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Penyakit Lainnya <span class="text-red-500">*</span></label>
                        <input type="text" name="penyakit_hamil_lainnya" value="{{ $v('penyakit_hamil_lainnya') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('penyakit_hamil_lainnya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-2">Komplikasi Kehamilan</label>
                        <div class="flex flex-wrap gap-x-5 gap-y-2">
                            @foreach ($checkboxKomplikasi as $key => $label)
                                <label class="inline-flex items-center gap-2">
                                    <input type="hidden" name="{{ $key }}" value="0">
                                    <input type="checkbox" name="{{ $key }}" value="1" class="cb-komplikasi" data-key="{{ $key }}" {{ $v($key) == '1' ? 'checked' : '' }}> {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('komplikasi_pendarahan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-komplikasi-lainnya" style="{{ $v('komplikasi_lain') == '1' ? '' : 'display:none' }}">
                        <label class="block text-xs font-medium mb-1">Komplikasi Lainnya <span class="text-red-500">*</span></label>
                        <input type="text" name="komplikasi_lainnya" value="{{ $v('komplikasi_lainnya') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('komplikasi_lainnya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= LABORATORIUM IBU ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Laboratorium Ibu</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Golongan Darah <span class="text-red-500">*</span></label>
                            <select name="golongan_darah_ibu" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih --</option>
                                {!! \App\Helpers\SelectOption::render('golongan_darah', $v('golongan_darah_ibu')) !!}
                            </select>
                            @error('golongan_darah_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">HB <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="hb_ibu" value="{{ $v('hb_ibu') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('hb_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Leukosit <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="leukosit_ibu" value="{{ $v('leukosit_ibu') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('leukosit_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Gula Darah <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="gula_darah_ibu" value="{{ $v('gula_darah_ibu') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('gula_darah_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Urine Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="urine_lengkap_ibu" value="{{ $v('urine_lengkap_ibu') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('urine_lengkap_ibu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= SAP NEONATAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">SAP Neonatal (Head-to-Toe)</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @foreach ($jumlahButir as $prefix => $n)
                        <div>
                            <label class="block text-xs font-medium mb-2">{{ $labelButir[$prefix] }}</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-x-5 gap-y-2">
                                @for ($i = 1; $i <= $n; $i++)
                                    <select name="{{ $prefix }}_{{ $i }}" class="w-full border rounded-sm px-2 py-1.5 text-xs">
                                        <option value="">--</option>
                                        @foreach ($opsiSap[$prefix] as $opsi)
                                            <option value="{{ $opsi }}" {{ $v($prefix.'_'.$i) === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                                        @endforeach
                                    </select>
                                @endfor
                            </div>
                            @error($prefix.'_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        @if (in_array($prefix, ['kepala', 'telinga', 'mulut', 'punggung', 'anus', 'genitalia_pria']))
                            <div>
                                <label class="block text-xs font-medium mb-1">{{ $labelButir[$prefix] }} — Lainnya</label>
                                <input type="text" name="{{ $prefix }}_lain" value="{{ $v($prefix.'_lain') }}" class="w-full border rounded-sm px-3 py-2 text-sm max-w-xl">
                                @error($prefix.'_lain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endif
                    @endforeach

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($opsiTunggal as $key => $opsi)
                            <div>
                                <label class="block text-xs font-medium mb-1">{{ $labelTunggal[$key] }}</label>
                                <select name="{{ $key }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                    <option value="">--</option>
                                    @foreach ($opsi as $o)
                                        <option value="{{ $o }}" {{ $v($key) === $o ? 'selected' : '' }}>{{ $o }}</option>
                                    @endforeach
                                </select>
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= ELIMINASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Eliminasi Pertama</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tgl BAB Pertama</label>
                            <input type="date" name="tgl_bab_pertama" value="{{ $v('tgl_bab_pertama') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tgl_bab_pertama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam BAB Pertama</label>
                            <input type="time" name="jam_bab_pertama" value="{{ $v('jam_bab_pertama') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_bab_pertama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tgl BAK Pertama</label>
                            <input type="date" name="tgl_bak_pertama" value="{{ $v('tgl_bak_pertama') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tgl_bak_pertama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam BAK Pertama</label>
                            <input type="time" name="jam_bak_pertama" value="{{ $v('jam_bak_pertama') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_bak_pertama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                document.querySelectorAll('.opsi-anc').forEach(function (r) {
                    r.addEventListener('change', function () {
                        document.getElementById('blok-lokasi-anc').style.display = this.value === '1' ? '' : 'none';
                    });
                });
                function pasang(cbClass, blokId, dataKey) {
                    document.querySelectorAll('.' + cbClass).forEach(function (cb) {
                        cb.addEventListener('change', function () {
                            if (this.dataset.key === dataKey) {
                                document.getElementById(blokId).style.display = this.checked ? '' : 'none';
                            }
                        });
                    });
                }
                pasang('cb-hamil', 'blok-penyakit-lainnya', 'penyakit_hamil_lain');
                pasang('cb-komplikasi', 'blok-komplikasi-lainnya', 'komplikasi_lain');
            })();
        </script>
    </x-emr-split-layout>
@endsection
