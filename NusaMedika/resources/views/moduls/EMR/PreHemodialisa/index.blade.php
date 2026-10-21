@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Pre Hemodialisa';
        $subtitleForm = 'Pengkajian pra-sesi hemodialisa — data fokus, heparinisasi, lab & screening akses.';
        $routeName = null;
        $routeUrl = url('emr/form/pre_hemodialisa');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');
        $isChecked = fn ($key) => $v($key) === 'on' || $v($key) === '1' || $v($key) === 'YA';
        $headers = [
            'tanggal_pertemuan' => 'Tanggal',
            'waktu_pertemuan' => 'Waktu',
            'lama_hd' => 'Lama (mnt)',
        ];
        $labFields = [
            'hb' => 'Hb (g/dL)', 'ureum' => 'Ureum (mg/dL)', 'creatinine' => 'Creatinine (mg/dL)',
            'kalium' => 'Kalium (mEq/L)', 'sgot' => 'SGOT (U/L)', 'sgpt' => 'SGPT (U/L)',
        ];
        $terapiFields = [
            'transfusi' => 'Transfusi (ml/paket)', 'd40' => 'D 40% (ampul)', 'ca_glukonas' => 'Ca. Gluconas (ampul)',
            'kcl' => 'KCL (vial)', 'renxamin' => 'Renxamin (ml)', 'erythropoietin' => 'Erythropoietin (IU)',
        ];
        $aksesBoxes = [
            'akses_fistula' => 'AV. Fistula', 'akses_graft' => 'AV. Graft', 'akses_sndl' => 'SN',
            'akses_cdl' => 'CDL', 'akses_cdl_thermo' => 'CDL Long Therm', 'akses_femoral' => 'Femoral',
        ];
        $komunikasiBoxes = [
            'komunikasi_ada_kendala' => 'Ada Kendala', 'komunikasi_tidak_ada' => 'Tidak Ada', 'komunikasi_lain' => 'Lainnya',
        ];
        $rumahBoxes = [
            'perawat_rumah_ada' => 'Ada Yang Merawat', 'perawat_rumah_tidak' => 'Tidak Ada', 'perawat_rumah_lain' => 'Lainnya',
        ];
        $gaduhBoxes = [
            'gaduh_kondisi_saat_ini' => 'Kondisi Saat Ini', 'gaduh_tenang' => 'Tenang', 'gaduh_gelisah' => 'Gelisah',
            'gaduh_takut_tindakan' => 'Takut Tindakan', 'gaduh_marah' => 'Marah', 'gaduh_mudah_tersinggung' => 'Mudah Tersinggung',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Pre Hemodialisa"
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
                slug="pre_hemodialisa"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            {{-- ================= A. DATA FOKUS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">A. Data Fokus</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Pengkajian <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_pertemuan" value="{{ $v('tanggal_pertemuan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_pertemuan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Waktu Pengkajian <span class="text-red-500">*</span></label>
                            <input type="time" name="waktu_pertemuan" value="{{ $v('waktu_pertemuan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('waktu_pertemuan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Subjective (S) <span class="text-red-500">*</span></label>
                        <textarea name="subjective" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('subjective') }}</textarea>
                        @error('subjective') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Objective (O) <span class="text-red-500">*</span></label>
                        <textarea name="objective" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('objective') }}</textarea>
                        @error('objective') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach (['bb_awal' => 'BB Awal Sesi (kg)', 'berat_badan' => 'BB Saat Ini (kg)', 'bb_kering' => 'BB Kering (kg)', 'td_sistolik' => 'TD Sistolik', 'td_diastolik' => 'TD Diastolik', 'nadi' => 'Nadi', 'pernapasan' => 'Pernapasan', 'suhu' => 'Suhu (°C)', 'saturasi' => 'SpO2 (%)'] as $key => $label)
                            <div>
                                <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                                <input type="number" step="0.1" name="{{ $key }}" value="{{ $v($key) }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                        <div>
                            <label class="block text-xs font-medium mb-1">EWS (turunan)</label>
                            <input type="text" value="{{ $emr_data['ews'] ?? '-' }}" class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50" readonly>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Type Mesin HD <span class="text-red-500">*</span></label>
                            <input type="text" name="type_hd" value="{{ $v('type_hd') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('type_hd') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Dialiser <span class="text-red-500">*</span></label>
                            <input type="text" name="dialiser" value="{{ $v('dialiser') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('dialiser') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kesadaran <span class="text-red-500">*</span></label>
                            <select name="kesadaran" class="w-full border rounded-sm px-3 py-2 text-sm select2">
                                <option value="">— Pilih —</option>
                                {!! \App\Helpers\SelectOption::render('kesadaran', $v('kesadaran')) !!}
                            </select>
                            @error('kesadaran') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Riwayat Alergi <span class="text-red-500">*</span></label>
                        <textarea name="riwayat_alergi" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('riwayat_alergi') }}</textarea>
                        @error('riwayat_alergi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    {{-- GCS --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach (['gcs_e' => ['GCS E (1–4)', 1, 4], 'gcs_m' => ['GCS M (1–6)', 1, 6], 'gcs_v' => ['GCS V (1–5)', 1, 5]] as $key => [$label, $min, $max])
                            <div>
                                <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                                <input type="number" name="{{ $key }}" min="{{ $min }}" max="{{ $max }}" value="{{ $v($key) }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                        <div>
                            <label class="block text-xs font-medium mb-1">GCS Jumlah (turunan)</label>
                            <input type="text" value="{{ $emr_data['gcs_jumlah'] ?? '-' }}" class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50" readonly>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Jenis Dialyzer <span class="text-red-500">*</span></label>
                            <select name="dializer" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">— Pilih —</option>
                                {!! \App\Helpers\SelectOption::render('dialyzer', $v('dializer')) !!}
                            </select>
                            @error('dializer') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jenis Dialysat</label>
                            <select name="dialisat" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">— Pilih —</option>
                                {!! \App\Helpers\SelectOption::render('dialysat', $v('dialisat')) !!}
                            </select>
                            @error('dialisat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Akses Vaskuler <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach ($aksesBoxes as $key => $label)
                                <div class="flex items-center gap-2">
                                    <input type="hidden" name="{{ $key }}" value="off">
                                    <input type="checkbox" id="{{ $key }}" name="{{ $key }}" value="on" {{ $isChecked($key) ? 'checked' : '' }} class="rounded border-slate-300">
                                    <label for="{{ $key }}" class="text-sm">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        @error('akses_fistula') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= HEPARINISASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Heparinisasi</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Dosis Sirkulasi <span class="text-red-500">*</span></label>
                        <input type="text" name="dosis_sirkulasi" value="{{ $v('dosis_sirkulasi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('dosis_sirkulasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Dosis Awal / Bolus <span class="text-red-500">*</span></label>
                        <input type="text" name="dosis_bolus" value="{{ $v('dosis_bolus') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('dosis_bolus') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Maintenance (UI/jam) <span class="text-red-500">*</span></label>
                        <input type="number" name="dosis_maintenance" value="{{ $v('dosis_maintenance') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('dosis_maintenance') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanpa Heparinisasi — Penyebab (wajib bila Maintenance = 0)</label>
                        <textarea name="tanpa_heparinisasi" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('tanpa_heparinisasi') }}</textarea>
                        @error('tanpa_heparinisasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Prog Bilas NS 0,9% 100 cc/jam <span class="text-red-500">*</span></label>
                        <input type="text" name="prog_bilas" value="{{ $v('prog_bilas') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('prog_bilas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= WAKTU & ULTRAFILTRASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Waktu &amp; Ultrafiltrasi</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-2 md:grid-cols-4 gap-4">
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
                        <label class="block text-xs font-medium mb-1">Lama Dialisis (menit, turunan)</label>
                        <input type="text" value="{{ $emr_data['lama_hd'] ?? '-' }}" class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50" readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">UF (ml) <span class="text-red-500">*</span></label>
                        <input type="number" name="uf" value="{{ $v('uf') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('uf') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Qb (ml/mnt) <span class="text-red-500">*</span></label>
                        <input type="number" name="qb" value="{{ $v('qb') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('qb') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">TBV (ml) <span class="text-red-500">*</span></label>
                        <input type="number" name="tbv" value="{{ $v('tbv') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tbv') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= LABORATORIUM ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Laboratorium</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Lab <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_laborat" value="{{ $v('tgl_laborat') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tgl_laborat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jam Lab <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_laborat" value="{{ $v('jam_laborat') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('jam_laborat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    @foreach ($labFields as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="{{ $key }}" value="{{ $v($key) }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-medium mb-1">HBsAg <span class="text-red-500">*</span></label>
                        <select name="hbsag" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <option value="">— Pilih —</option>
                            {!! \App\Helpers\SelectOption::render('hbsag', $v('hbsag')) !!}
                        </select>
                        @error('hbsag') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium mb-1">Hasil Pemeriksaan Lain</label>
                        <textarea name="hasil_pemeriksaan_lain" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('hasil_pemeriksaan_lain') }}</textarea>
                        @error('hasil_pemeriksaan_lain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= TERAPI TAMBAHAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Terapi Tambahan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach ($terapiFields as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }}</label>
                            <input type="number" step="0.01" name="{{ $key }}" value="{{ $v($key) }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= PSIKOSOSIAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Psikososial &amp; Asesmen</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Riwayat Kesehatan Saat Ini <span class="text-red-500">*</span></label>
                        <textarea name="riwayat_kesehatan_saat_ini" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('riwayat_kesehatan_saat_ini') }}</textarea>
                        @error('riwayat_kesehatan_saat_ini') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Kendala Komunikasi</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($komunikasiBoxes as $key => $label)
                                <div class="flex items-center gap-2">
                                    <input type="hidden" name="{{ $key }}" value="off">
                                    <input type="checkbox" id="{{ $key }}" name="{{ $key }}" value="on" {{ $isChecked($key) ? 'checked' : '' }} class="rounded border-slate-300">
                                    <label for="{{ $key }}" class="text-sm">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        <input type="text" name="komunikasi_keterangan" value="{{ $v('komunikasi_keterangan') }}" placeholder="Keterangan bila Lainnya" class="mt-2 w-full border rounded-sm px-3 py-2 text-sm">
                        @error('komunikasi_keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Perawat di Rumah</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($rumahBoxes as $key => $label)
                                <div class="flex items-center gap-2">
                                    <input type="hidden" name="{{ $key }}" value="off">
                                    <input type="checkbox" id="{{ $key }}" name="{{ $key }}" value="on" {{ $isChecked($key) ? 'checked' : '' }} class="rounded border-slate-300">
                                    <label for="{{ $key }}" class="text-sm">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                        <input type="text" name="perawat_rumah_keterangan" value="{{ $v('perawat_rumah_keterangan') }}" placeholder="Keterangan bila Lainnya" class="mt-2 w-full border rounded-sm px-3 py-2 text-sm">
                        @error('perawat_rumah_keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Asesmen Gaduh Gelisah</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach ($gaduhBoxes as $key => $label)
                                <div class="flex items-center gap-2">
                                    <input type="hidden" name="{{ $key }}" value="off">
                                    <input type="checkbox" id="{{ $key }}" name="{{ $key }}" value="on" {{ $isChecked($key) ? 'checked' : '' }} class="rounded border-slate-300">
                                    <label for="{{ $key }}" class="text-sm">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= B–D. ASUHAN & RENCANA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Asuhan Keperawatan &amp; Rencana Sesi Berikutnya</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @foreach (['diagnosa_keperawatan' => 'Diagnosa Keperawatan', 'planning' => 'Planning (P)', 'evaluasi' => 'Evaluasi Keperawatan'] as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <textarea name="{{ $key }}" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($key) }}</textarea>
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal HD Berikutnya <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_hd_berikutnya" value="{{ $v('tanggal_hd_berikutnya') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_hd_berikutnya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Lama HD Rencana (jam) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.5" name="lama_hd_rencana" value="{{ $v('lama_hd_rencana') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('lama_hd_rencana') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= E. SCREENING AKSES ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">E. Screening Akses</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Screening Akses <span class="text-red-500">*</span></label>
                        <input type="date" name="screening_akses_tanggal" value="{{ $v('screening_akses_tanggal') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('screening_akses_tanggal') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Catatan Screening Akses</label>
                        <textarea name="screening_akses_catatan" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('screening_akses_catatan') }}</textarea>
                        @error('screening_akses_catatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Keterangan</label>
                        <textarea name="keterangan" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keterangan') }}</textarea>
                        @error('keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </fieldset>
    </x-emr-split-layout>
@endsection
