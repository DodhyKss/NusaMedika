@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Catatan Bedah';
        $subtitleForm = 'Catatan bedah di ruang operasi: pemeriksaan fisik, posisi, alat, pembedahan, pembiusan, drainase & instruksi pasca operasi.';
        $routeName = null;
        $routeUrl = url('emr/form/catatan_bedah');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_catatan' => 'Tanggal',
            'jam_catatan' => 'Jam',
            'jenis_operasi' => 'Jenis Operasi',
        ];

        $opsiKeadaanUmum = \App\Helpers\SelectOption::get('keadaan_umum_pulih');
        $opsiPosisi = \App\Helpers\SelectOption::get('posisi_operasi');
        $opsiJenisOperasi = ['Efektif', 'Darurat'];

        $panelText = [
            'prabedah' => 'Catatan Pra Bedah',
            'pre_operasi' => 'Catatan Pre Operasi',
        ];
        $panelAlat = [
            'peralatan' => 'Peralatan & Perlengkapan Operasi',
            'pasang_alat' => 'Alat yang Dipasang',
        ];
        $panelKamar = [
            'kebutuhan_kamar' => 'Kebutuhan Kamar',
            'klarifikasi' => 'Klarifikasi',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Catatan Bedah"
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
                slug="catatan_bedah"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS & PENULIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas &amp; Penulis Catatan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Catatan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_catatan" value="{{ $v('tanggal_catatan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_catatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Catatan <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_catatan" value="{{ $v('jam_catatan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_catatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Dokter Bedah Penulis <span class="text-red-500">*</span></label>
                            <x-select_dokter
                                name="dokter_bedah"
                                id="dokter_bedah"
                                label=""
                                placeholder="-- Pilih Dokter Bedah --"
                                :selected="$v('dokter_bedah')"
                                :required="true" />
                            @error('dokter_bedah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Petugas Pendamping <span class="text-red-500">*</span></label>
                            <x-select_pegawai
                                name="petugas_pendamping"
                                id="petugas_pendamping"
                                label=""
                                placeholder="-- Pilih Petugas --"
                                :selected="$v('petugas_pendamping')"
                                :required="true" />
                            @error('petugas_pendamping') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Operasi <span class="text-red-500">*</span></label>
                        <div class="flex gap-5">
                            @foreach ($opsiJenisOperasi as $o)
                                <label class="inline-flex items-center gap-1.5">
                                    <input type="radio" name="jenis_operasi" value="{{ $o }}" {{ $v('jenis_operasi') === $o ? 'checked' : '' }}>
                                    <span>{{ $o }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('jenis_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PRE OPERASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Aspek Pre Operasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @foreach ($panelText as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <textarea name="{{ $key }}" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($key) }}</textarea>
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= PEMERIKSAAN FISIK & POSISI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-indigo-50 border-b border-slate-200">
                    <span class="text-indigo-700 font-medium text-[15px]">Pemeriksaan Fisik &amp; Posisi Operasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Pemeriksaan Fisik Intra Operasi <span class="text-red-500">*</span></label>
                        <textarea name="pemeriksaan_fisik" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('pemeriksaan_fisik') }}</textarea>
                        @error('pemeriksaan_fisik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Kondisi Kulit <span class="text-red-500">*</span></label>
                            <input type="text" name="kulit" value="{{ $v('kulit') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Kondisi kulit...">
                            @error('kulit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Warna Kulit <span class="text-red-500">*</span></label>
                            <input type="text" name="warna_kulit" value="{{ $v('warna_kulit') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Warna kulit...">
                            @error('warna_kulit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Keadaan Umum <span class="text-red-500">*</span></label>
                            <select name="keadaan_umum" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Keadaan Umum --</option>
                                @foreach ($opsiKeadaanUmum as $o)
                                    <option value="{{ $o['value'] }}" {{ $v('keadaan_umum') === $o['value'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                                @endforeach
                            </select>
                            @error('keadaan_umum') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Posisi Operasi <span class="text-red-500">*</span></label>
                            <select name="posisi_operasi" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Posisi --</option>
                                @foreach ($opsiPosisi as $o)
                                    <option value="{{ $o['value'] }}" {{ $v('posisi_operasi') === $o['value'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                                @endforeach
                            </select>
                            @error('posisi_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium mb-1">Lokasi Operasi (lokasi sayatan) <span class="text-red-500">*</span></label>
                            <input type="text" name="lokasi_operasi" value="{{ $v('lokasi_operasi') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Lokasi sayatan...">
                            @error('lokasi_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= ALAT & KAMAR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Alat, Perlengkapan &amp; Kamar Operasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @foreach ($panelAlat as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            @if ($key === 'pasang_alat')
                                <input type="text" name="{{ $key }}" value="{{ $v($key) }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @else
                                <textarea name="{{ $key }}" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($key) }}</textarea>
                            @endif
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                    @foreach ($panelKamar as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            @if ($key === 'klarifikasi')
                                <input type="text" name="{{ $key }}" value="{{ $v($key) }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @else
                                <textarea name="{{ $key }}" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($key) }}</textarea>
                            @endif
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= PEMBEDAHAN, PEMBIUSAN & DRAINASE ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-purple-50 border-b border-slate-200">
                    <span class="text-purple-700 font-medium text-[15px]">Pembedahan, Pembiusan &amp; Drainase</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Pembedahan <span class="text-red-500">*</span></label>
                        <textarea name="pembedahan" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('pembedahan') }}</textarea>
                        @error('pembedahan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Pembiusan <span class="text-red-500">*</span></label>
                        <textarea name="pembiusan" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('pembiusan') }}</textarea>
                        @error('pembiusan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Pemakaian Drain <span class="text-red-500">*</span></label>
                            <input type="text" name="pemakaian_drain" value="{{ $v('pemakaian_drain') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('pemakaian_drain') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Torniquet <span class="text-red-500">*</span></label>
                            <input type="text" name="torniquet" value="{{ $v('torniquet') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Durasi pemakaian...">
                            @error('torniquet') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Diathermi <span class="text-red-500">*</span></label>
                            <input type="text" name="diathermi" value="{{ $v('diathermi') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('diathermi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= CAIRAN, DARAH & TIM ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Cairan, Darah &amp; Tim Operasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Jumlah Cairan (mL) <span class="text-red-500">*</span></label>
                            <input type="number" name="jumlah_cairan" min="0" value="{{ $v('jumlah_cairan') }}" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mL">
                            @error('jumlah_cairan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Perawatan Darah (tipe &amp; jumlah) <span class="text-red-500">*</span></label>
                            <input type="text" name="perawatan_darah" value="{{ $v('perawatan_darah') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Tipe & jumlah darah...">
                            @error('perawatan_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Sirkuler <span class="text-red-500">*</span></label>
                            <input type="text" name="sirkuler_a" value="{{ $v('sirkuler_a') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Nama sirkuler...">
                            @error('sirkuler_a') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Asisten <span class="text-red-500">*</span></label>
                            <input type="text" name="asisten" value="{{ $v('asisten') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Nama asisten...">
                            @error('asisten') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PASCA OPERASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Instruksi &amp; Diagnosa Pasca Operasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Instruksi Pasca Operasi <span class="text-red-500">*</span></label>
                        <textarea name="instruksi" rows="3" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('instruksi') }}</textarea>
                        @error('instruksi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Diagnosa Medis <span class="text-red-500">*</span></label>
                        <textarea name="diagnosa_medis" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('diagnosa_medis') }}</textarea>
                        @error('diagnosa_medis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>
@endsection
