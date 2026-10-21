@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Pesan Bedah / Cathlab / Endoscopy / ESWL';
        $subtitleForm = 'Permintaan penjadwalan tindakan penunjang invasif.';
        $routeName = null;
        $routeUrl = url('emr/form/pesan_penunjang');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'jenis_pesan' => 'Jenis Pesan',
            'tgl_rencana_tindakan' => 'Rencana Tindakan',
            'jenis_rawat_rencana' => 'Rencana Masuk',
        ];

        $sel = function ($name, $list) use ($v) {
            $html = '<select name="'.$name.'" class="w-full border rounded-sm px-2 py-1.5 text-sm"><option value="">-- Pilih --</option>';
            foreach ($list as $opt) {
                $attr = $v($name) === $opt ? ' selected' : '';
                $html .= '<option value="'.$opt.'"'.$attr.'>'.$opt.'</option>';
            }
            return $html.'</select>';
        };
        $selAssoc = function ($name, $list) use ($v) {
            $html = '<select name="'.$name.'" class="w-full border rounded-sm px-2 py-1.5 text-sm"><option value="">-- Pilih --</option>';
            foreach ($list as $opt) {
                $val = $opt['value'];
                $attr = $v($name) === $val ? ' selected' : '';
                $html .= '<option value="'.$val.'"'.$attr.'>'.($opt['label'] ?? $val).'</option>';
            }
            return $html.'</select>';
        };

        // Nilai tersimpan comma-separated; old() gagal validasi berupa array —
        // cast (string) pada array melempar "Array to string conversion" (form 94).
        $rawAlat = $v('alat_khusus');
        $alatTerpilih = is_array($rawAlat)
            ? $rawAlat
            : (trim((string) $rawAlat) === '' ? [] : explode(', ', (string) $rawAlat));

        $jawaban = ['Ya', 'Tidak'];
        $opsiRawat = ['RI', 'RJ'];
        $opsiPasangAlat = ['Tidak Ada', 'Ada'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Pesan Penunjang"
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
                slug="pesan_penunjang"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            @include('moduls.EMR.PartialForm.pasien_identitas_rujukan')

            {{-- ================= KELAS NASABAH (READ-ONLY) ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-slate-100 border-b border-slate-200">
                    <span class="text-slate-700 font-medium text-[15px]">Kelas Nasabah</span>
                </div>
                <div class="p-5 bg-white text-sm max-w-xl">
                    <label class="block text-xs font-medium mb-1 text-slate-500">Kelas (di-resolve dari nasabah pasien, tidak disimpan)</label>
                    <input type="text" readonly value="{{ $kelasNama ?? '—' }}" class="w-full border border-slate-200 bg-slate-50 rounded-sm px-3 py-2 text-sm text-slate-600">
                </div>
            </div>

            {{-- ================= PESAN PENUNJANG ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Data Pesan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Pesan <span class="text-red-500">*</span></label>
                        {!! $selAssoc('jenis_pesan', $opsiPesan) !!}
                        @error('jenis_pesan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">DPJP / Dokter Pengirim <span class="text-red-500">*</span></label>
                        <input type="text" name="dpjp_utama" value="{{ $v('dpjp_utama') }}" maxlength="200" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('dpjp_utama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <x-select_dokter name="dokter_operator_id" id="dokter_operator_id" label="Dokter Operator" :selected="$v('dokter_operator_id')" :required="true" />
                    </div>
                    <div>
                        <x-select-ruang-perawatan name="bagian_asal" id="bagian_asal" :selected="$v('bagian_asal')" />
                        @error('bagian_asal') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Unit Pelaksanaan Tindakan <span class="text-red-500">*</span></label>
                        <select name="unit_pelaksanaan_id" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                            <option value="">-- Pilih Unit --</option>
                            @foreach ($bagians as $b)
                                <option value="{{ $b->bagian_id }}" {{ (string) $v('unit_pelaksanaan_id') === (string) $b->bagian_id ? 'selected' : '' }}>
                                    {{ $b->nama_bagian }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_pelaksanaan_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Tindakan Penunjang <span class="text-red-500">*</span></label>
                        <select name="jenis_tindakan" class="w-full border rounded-sm px-2 py-1.5 text-sm">
                            <option value="">-- Pilih Tindakan --</option>
                            @foreach ($tindakans as $t)
                                <option value="{{ $t->tindakan_id }}" {{ (string) $v('jenis_tindakan') === (string) $t->tindakan_id ? 'selected' : '' }}>
                                    {{ $t->nama_tindakan }}
                                </option>
                            @endforeach
                        </select>
                        @error('jenis_tindakan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-medium mb-1">Diagnosa Pre Tindakan <span class="text-red-500">*</span></label>
                        <textarea name="diagnosa_medis" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('diagnosa_medis') }}</textarea>
                        @error('diagnosa_medis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">No. Handphone <span class="text-red-500">*</span></label>
                        <input type="text" name="handphone" value="{{ $v('handphone') }}" maxlength="13" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('handphone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">No. Handphone Keluarga <span class="text-red-500">*</span></label>
                        <input type="text" name="handphone_keluarga" value="{{ $v('handphone_keluarga') }}" maxlength="13" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('handphone_keluarga') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= JADWAL TINDAKAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Jadwal Rencana Tindakan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Rencana Tindakan <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_rencana_tindakan" value="{{ $v('tgl_rencana_tindakan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tgl_rencana_tindakan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jam Rencana Tindakan <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_rencana_tindakan" value="{{ $v('jam_rencana_tindakan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('jam_rencana_tindakan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jam Keputusan SC / Emergency <span class="text-red-500">*</span> <span class="font-normal text-slate-400">(bila SC/EC)</span></label>
                        <input type="time" name="jam_keputusan_sc" value="{{ $v('jam_keputusan_sc') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('jam_keputusan_sc') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Rencana Masuk Ruangan <span class="text-red-500">*</span></label>
                        {!! $sel('jenis_rawat_rencana', $opsiRawat) !!}
                        @error('jenis_rawat_rencana') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-rencana-ranap">
                        <label class="block text-xs font-medium mb-1">Tanggal Rencana Rawat Inap <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_rencana_ranap" value="{{ $v('tgl_rencana_ranap') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tgl_rencana_ranap') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Berat Badan (kg)</label>
                        <input type="number" name="bb" step="0.1" min="0" value="{{ $v('bb') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('bb') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Tinggi Badan (cm)</label>
                        <input type="number" name="tb" step="0.1" min="0" value="{{ $v('tb') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tb') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= STATUS SC / EC / IC & ALAT KHUSUS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Status SC / Emergency / IC & Alat Khusus</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">SC <span class="text-red-500">*</span></label>
                        {!! $sel('sc', $jawaban) !!}
                        @error('sc') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">SC Emergency <span class="text-red-500">*</span></label>
                        {!! $sel('sc_emergency', $jawaban) !!}
                        @error('sc_emergency') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">IC <span class="text-red-500">*</span></label>
                        {!! $sel('ic', $jawaban) !!}
                        @error('ic') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Alat Khusus Diperlukan <span class="text-red-500">*</span></label>
                        {!! $sel('pasang_alat', $opsiPasangAlat) !!}
                        @error('pasang_alat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-alat-khusus" class="md:col-span-2">
                        <label class="block text-xs font-medium mb-1">Pilih Alat <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-x-5 gap-y-2 pt-1">
                            @foreach ($opsiAlat as $opt)
                                <label class="inline-flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="alat_khusus[]" value="{{ $opt['value'] }}"
                                        {{ in_array($opt['value'], $alatTerpilih, true) ? 'checked' : '' }}>
                                    {{ $opt['label'] ?? $opt['value'] }}
                                </label>
                            @endforeach
                        </div>
                        @error('alat_khusus') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PRA TINDAKAN, ANESTESI & DOKTER OPERATOR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-indigo-50 border-b border-slate-200">
                    <span class="text-indigo-700 font-medium text-[15px]">Pra Tindakan & Anestesi</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Persiapan Pra Tindakan <span class="text-red-500">*</span></label>
                        {!! $selAssoc('pra_tindakan', $opsiPraTindakan) !!}
                        @error('pra_tindakan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div id="blok-anestesi">
                        <label class="block text-xs font-medium mb-1">Jenis Anestesi <span class="text-red-500">*</span> <span class="font-normal text-slate-400">(Endoscopy / ESWL)</span></label>
                        {!! $selAssoc('jenis_anestesi', $opsiAnestesi) !!}
                        @error('jenis_anestesi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function cek127Penunjang() {
                    var jenis = document.querySelector('select[name="jenis_pesan"]');
                    var jenisVal = jenis ? jenis.value : '';

                    // Jenis anestesi hanya relevan ENDOSCOPY / ESWL.
                    var blokAnestesi = document.getElementById('blok-anestesi');
                    if (blokAnestesi) {
                        blokAnestesi.style.display = (jenisVal === 'ENDOSCOPY' || jenisVal === 'ESWL') ? '' : 'none';
                    }

                    // Rencana rawat inap hanya bila jenis rawat rencana = RI.
                    var rawat = document.querySelector('select[name="jenis_rawat_rencana"]');
                    var blokRanap = document.getElementById('blok-rencana-ranap');
                    if (blokRanap) {
                        blokRanap.style.display = (rawat && rawat.value === 'RI') ? '' : 'none';
                    }

                    // Blok alat khusus hanya bila pasang_alat = Ada.
                    var pasang = document.querySelector('select[name="pasang_alat"]');
                    var blokAlat = document.getElementById('blok-alat-khusus');
                    if (blokAlat) {
                        blokAlat.style.display = (pasang && pasang.value === 'Ada') ? '' : 'none';
                    }
                }

                ['select[name="jenis_pesan"]', 'select[name="jenis_rawat_rencana"]', 'select[name="pasang_alat"]']
                    .forEach(function (sel) {
                        var el = document.querySelector(sel);
                        if (el) { el.addEventListener('change', cek127Penunjang); }
                    });
                cek127Penunjang();
            })();
        </script>
    </x-emr-split-layout>
@endsection
