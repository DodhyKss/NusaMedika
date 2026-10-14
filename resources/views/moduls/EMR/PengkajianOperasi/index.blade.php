@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Pengkajian Pre/Intra/Post Operasi';
        $subtitleForm = 'Pengkajian keperawatan perioperatif — aspek Pre, Intra & Post Operasi dalam satu formulir.';
        $routeName = null;
        $routeUrl = url('emr/form/pengkajian_operasi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_pengkajian' => 'Tanggal',
            'jam_pengkajian' => 'Jam',
            'fase' => 'Fase',
        ];

        $opsiFase = ['Pre', 'Intra', 'Post'];
        $opsiJenisOperasi = \App\Helpers\SelectOption::get('jenis_operasi');
        $opsiStatusEmosi = \App\Helpers\SelectOption::get('status_emosi');
        $opsiPosisiOperasi = \App\Helpers\SelectOption::get('posisi_operasi');
        $opsiKeadaanUmum = \App\Helpers\SelectOption::get('keadaan_umum_pulih');
        $opsiAsa = \App\Helpers\SelectOption::get('asa_kelas');
        $opsiMobilisasi = \App\Helpers\SelectOption::get('mobilisasi_operasi');
        $opsiYaTidak = ['Ya', 'Tidak'];

        $panelPre = [
            'keluhan_utama' => 'Keluhan Utama',
            'riwayat_penyakit' => 'Riwayat Penyakit',
            'riwayat_operasi' => 'Riwayat Operasi',
            'riwayat_alergi' => 'Riwayat Alergi',
            'data_penunjang' => 'Data Penunjang (Laboratorium/Radiologi/dll.)',
        ];
        $panelPost = [
            'luka_operasi' => 'Kondisi Luka Operasi',
        ];
        $panelPerencanaan = [
            'rencana_keperawatan' => 'Rencana Keperawatan',
            'kebutuhan_nutrisi' => 'Kebutuhan Nutrisi',
            'edukasi_pasien' => 'Edukasi Pasien',
            'catatan_pengkajian' => 'Catatan Pengkajian',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Pengkajian Pre/Intra/Post Operasi"
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
                slug="pengkajian_operasi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS & FASE ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Pengkajian</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-xl">
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
                    <div>
                        <label class="block text-xs font-medium mb-1">Tahap Pengkajian <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-5">
                            @foreach ($opsiFase as $o)
                                <label class="inline-flex items-center gap-1.5">
                                    <input type="radio" name="fase" value="{{ $o }}" {{ $v('fase') === $o ? 'checked' : '' }}>
                                    <span>Fase {{ $o }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('fase') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= FASE PRE ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm" id="panel-pre">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-emerald-700 font-medium text-[15px]">Aspek Pre Operasi</span>
                    <span class="text-[11px] text-emerald-700 font-semibold uppercase">Fase Pre</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @foreach ($panelPre as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <textarea name="{{ $key }}" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($key) }}</textarea>
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Jenis Operasi <span class="text-red-500">*</span></label>
                            <select name="jenis_operasi" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Jenis Operasi --</option>
                                @foreach ($opsiJenisOperasi as $o)
                                    <option value="{{ $o['value'] }}" {{ $v('jenis_operasi') === $o['value'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                                @endforeach
                            </select>
                            @error('jenis_operasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Skala Cemas (Hamilton 0–4) <span class="text-red-500">*</span></label>
                            <input type="number" name="skala_cemas" min="0" max="4" value="{{ $v('skala_cemas') }}" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="0 – 4">
                            @error('skala_cemas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Status Emosi <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-5">
                            @foreach ($opsiStatusEmosi as $o)
                                <label class="inline-flex items-center gap-1.5">
                                    <input type="radio" name="status_emosi" value="{{ $o['value'] }}" {{ $v('status_emosi') === $o['value'] ? 'checked' : '' }}>
                                    <span>{{ $o['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('status_emosi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Persiapan Darah <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-5">
                            @foreach ($opsiYaTidak as $o)
                                <label class="inline-flex items-center gap-1.5">
                                    <input type="radio" name="persiapan_darah" value="{{ $o }}" {{ $v('persiapan_darah') === $o ? 'checked' : '' }}>
                                    <span>{{ $o }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('persiapan_darah') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= FASE INTRA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm" id="panel-intra">
                <div class="px-4 py-3 bg-indigo-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-indigo-700 font-medium text-[15px]">Aspek Intra Operasi</span>
                    <span class="text-[11px] text-indigo-700 font-semibold uppercase">Fase Intra</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Pemeriksaan Fisik <span class="text-red-500">*</span></label>
                        <textarea name="pemeriksaan_fisik_intra" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('pemeriksaan_fisik_intra') }}</textarea>
                        @error('pemeriksaan_fisik_intra') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Posisi Operasi <span class="text-red-500">*</span></label>
                            <select name="posisi_operasi_intra" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Posisi --</option>
                                @foreach ($opsiPosisiOperasi as $o)
                                    <option value="{{ $o['value'] }}" {{ $v('posisi_operasi_intra') === $o['value'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                                @endforeach
                            </select>
                            @error('posisi_operasi_intra') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Keadaan Umum <span class="text-red-500">*</span></label>
                            <select name="keadaan_umum_intra" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Keadaan Umum --</option>
                                @foreach ($opsiKeadaanUmum as $o)
                                    <option value="{{ $o['value'] }}" {{ $v('keadaan_umum_intra') === $o['value'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                                @endforeach
                            </select>
                            @error('keadaan_umum_intra') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kelas ASA <span class="text-red-500">*</span></label>
                            <select name="asa_intra" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih ASA --</option>
                                @foreach ($opsiAsa as $o)
                                    <option value="{{ $o['value'] }}" {{ $v('asa_intra') === $o['value'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                                @endforeach
                            </select>
                            @error('asa_intra') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Ruangan Tujuan Pemulihan <span class="text-red-500">*</span></label>
                            <x-select-ruang-perawatan name="ruangan_pulih" id="ruangan_pulih" :selected="$v('ruangan_pulih')" />
                            @error('ruangan_pulih') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= FASE POST ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm" id="panel-post">
                <div class="px-4 py-3 bg-purple-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-purple-700 font-medium text-[15px]">Aspek Post Operasi</span>
                    <span class="text-[11px] text-purple-700 font-semibold uppercase">Fase Post</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Nyeri <span class="text-red-500">*</span></label>
                            <div class="flex flex-wrap gap-5 pt-1">
                                @foreach ($opsiYaTidak as $o)
                                    <label class="inline-flex items-center gap-1.5">
                                        <input type="radio" name="nyeri_post" value="{{ $o }}" {{ $v('nyeri_post') === $o ? 'checked' : '' }}>
                                        <span>{{ $o }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('nyeri_post') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Skor Nyeri VAS (0–10) <span class="text-red-500">*</span></label>
                            <input type="number" name="skor_nyeri_post" min="0" max="10" value="{{ $v('skor_nyeri_post') }}" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="0 – 10">
                            @error('skor_nyeri_post') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">Mobilisasi <span class="text-red-500">*</span></label>
                        <select name="mobilisasi_post" class="w-full border rounded-sm px-3 py-2 text-sm max-w-md">
                            <option value="">-- Pilih Mobilisasi --</option>
                            @foreach ($opsiMobilisasi as $o)
                                <option value="{{ $o['value'] }}" {{ $v('mobilisasi_post') === $o['value'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                            @endforeach
                        </select>
                        @error('mobilisasi_post') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    @foreach ($panelPost as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <textarea name="{{ $key }}" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($key) }}</textarea>
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= PERENCANAAN & EDUKASI (selalu tampil) ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Perencanaan Keperawatan, Nutrisi &amp; Edukasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @foreach ($panelPerencanaan as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <textarea name="{{ $key }}" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($key) }}</textarea>
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function sel(name) {
                    var r = document.querySelector('input[name="' + name + '"]:checked');
                    return r ? r.value : '';
                }

                var panels = {
                    Pre: document.getElementById('panel-pre'),
                    Intra: document.getElementById('panel-intra'),
                    Post: document.getElementById('panel-post')
                };

                function syncFase() {
                    var fase = sel('fase') || 'Pre';
                    Object.keys(panels).forEach(function (k) {
                        if (panels[k]) {
                            panels[k].style.display = (k === fase) ? '' : 'none';
                        }
                    });
                }
                document.querySelectorAll('input[name="fase"]').forEach(function (r) {
                    r.addEventListener('change', syncFase);
                });
                syncFase();
            })();
        </script>
    </x-emr-split-layout>
@endsection
