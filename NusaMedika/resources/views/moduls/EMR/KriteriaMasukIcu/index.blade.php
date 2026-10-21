@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Kriteria Masuk ICU';
        $subtitleForm = 'Dokumen keputusan transfer ke ICU — 18 kriteria klinis, GCS, informed consent.';
        $routeName = null;
        $routeUrl = url('emr/form/kriteria_masuk_icu');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_kriteria' => 'Tanggal',
            'waktu_kriteria' => 'Waktu',
            'prioritas_icu' => 'Prioritas',
        ];

        $grupKriteria = [
            'A. Respirasi' => [
                'resp_1' => ['label' => 'Dewasa/Geriatrik: RR < 10 atau > 30/mnt', 'tipe' => 'number', 'min' => 0, 'max' => 120],
                'resp_2' => ['label' => 'Anak 1 bln–1 th: < 25 atau > 60/mnt', 'tipe' => 'number', 'min' => 0, 'max' => 120],
                'resp_3' => ['label' => 'Anak 1–3 th: < 15 atau > 40/mnt', 'tipe' => 'number', 'min' => 0, 'max' => 120],
                'resp_4' => ['label' => 'Anak 3–12 th: < 15 atau > 40/mnt', 'tipe' => 'number', 'min' => 0, 'max' => 120],
                'resp_5' => ['label' => 'Anak 12–17 th: < 12 atau > 30/mnt', 'tipe' => 'number', 'min' => 0, 'max' => 120],
                'resp_6' => ['label' => 'SpO₂ < 90%', 'tipe' => 'number', 'min' => 0, 'max' => 100],
                'resp_7' => ['label' => 'Gagal nafas (respiratory failure)', 'tipe' => 'text', 'min' => null, 'max' => null],
            ],
            'B. Tekanan Darah' => [
                'td' => ['label' => 'Sistole < 90 mmHg', 'tipe' => 'number', 'min' => 30, 'max' => 300],
            ],
            'C. Nadi' => [
                'nadi_1' => ['label' => 'Dewasa: < 50 (dengan gejala irama sinus) atau > 120/mnt', 'tipe' => 'number', 'min' => 0, 'max' => 250],
                'nadi_2' => ['label' => 'Geriatrik: < 50 simptomatik atau > 100/mnt', 'tipe' => 'number', 'min' => 0, 'max' => 250],
                'nadi_3' => ['label' => 'Anak 1 bln–1 th: < 70 atau > 180/mnt', 'tipe' => 'number', 'min' => 0, 'max' => 250],
                'nadi_4' => ['label' => 'Anak > 1–3 th: < 70 atau > 180/mnt', 'tipe' => 'number', 'min' => 0, 'max' => 250],
                'nadi_5' => ['label' => 'Anak > 3–12 th: < 60 atau > 160/mnt', 'tipe' => 'number', 'min' => 0, 'max' => 250],
                'nadi_6' => ['label' => 'Anak > 12–17 th: < 50 atau > 140/mnt', 'tipe' => 'number', 'min' => 0, 'max' => 250],
            ],
            'D. Gambaran EKG' => [
                'ekg_1' => ['label' => 'Aritmia (VT, VF, TdP, AF Rapid, PVC, SVT, PAC, AV Blok)', 'tipe' => 'text', 'min' => null, 'max' => null],
                'ekg_2' => ['label' => 'PJK: IMA, iskemik luas', 'tipe' => 'text', 'min' => null, 'max' => null],
            ],
            'E. Pasca Bedah' => [
                'pasca_bedah' => ['label' => 'Bedah besar/khusus, lama operasi > 6 jam', 'tipe' => 'text', 'min' => null, 'max' => null],
            ],
            'F. Pasien Obat' => [
                'obat' => ['label' => 'Pemberian obat yang perlu pemantauan khusus', 'tipe' => 'text', 'min' => null, 'max' => null],
            ],
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Kriteria Masuk ICU"
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
                slug="kriteria_masuk_icu"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Pengkajian</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_kriteria" value="{{ $v('tanggal_kriteria') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_kriteria') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Waktu <span class="text-red-500">*</span></label>
                            <input type="time" name="waktu_kriteria" value="{{ $v('waktu_kriteria') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('waktu_kriteria') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">DPJP ICU <span class="text-red-500">*</span></label>
                            <select name="dpjp_icu" class="w-full border rounded-sm px-3 py-2 text-sm select2">
                                <option value="">-- Pilih Dokter --</option>
                                @foreach ($dpjpIcus as $dokter)
                                    <option value="{{ $dokter->pegawai_id }}" {{ (string) $v('dpjp_icu') === (string) $dokter->pegawai_id ? 'selected' : '' }}>
                                        {{ $dokter->nama_pegawai }}
                                    </option>
                                @endforeach
                            </select>
                            @error('dpjp_icu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Diagnosa <span class="text-red-500">*</span></label>
                        <textarea name="diagnosa" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('diagnosa') }}</textarea>
                        @error('diagnosa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= KRITERIA A–F ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Kriteria Klinis A–F</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-5">
                    @foreach ($grupKriteria as $namaGrup => $items)
                        <div>
                            <p class="text-xs font-semibold text-slate-700 mb-2">{{ $namaGrup }}</p>
                            <table class="w-full text-xs border border-slate-200">
                                <thead>
                                    <tr class="bg-slate-50">
                                        <th class="px-3 py-2 text-left border-b border-slate-200 w-1/2">Kriteria</th>
                                        <th class="px-3 py-2 text-left border-b border-slate-200 w-1/4">Nilai</th>
                                        <th class="px-3 py-2 text-center border-b border-slate-200 w-1/4">Terpenuhi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $key => $item)
                                        <tr class="border-b border-slate-100 last:border-b-0">
                                            <td class="px-3 py-2">{{ $item['label'] }}</td>
                                            <td class="px-3 py-2">
                                                @if ($item['tipe'] === 'number')
                                                    <input type="number" name="{{ $key }}_nilai" value="{{ $v($key.'_nilai') }}" min="{{ $item['min'] }}" max="{{ $item['max'] }}" class="w-full border rounded-sm px-2 py-1">
                                                @else
                                                    <input type="text" name="{{ $key }}_nilai" value="{{ $v($key.'_nilai') }}" maxlength="200" class="w-full border rounded-sm px-2 py-1">
                                                @endif
                                                @error($key.'_nilai') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="flex justify-center gap-3">
                                                    <label class="inline-flex items-center gap-1">
                                                        <input type="radio" name="{{ $key }}_status" value="YA" {{ $v($key.'_status') === 'YA' ? 'checked' : '' }}> YA
                                                    </label>
                                                    <label class="inline-flex items-center gap-1">
                                                        <input type="radio" name="{{ $key }}_status" value="TIDAK" {{ $v($key.'_status') === 'TIDAK' ? 'checked' : '' }}> TIDAK
                                                    </label>
                                                </div>
                                                @error($key.'_status') <p class="text-[11px] text-red-600 mt-1 text-center">{{ $message }}</p> @enderror
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach

                    {{-- Jumlah kriteria (turunan server) --}}
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-medium">Jumlah Kriteria Terpenuhi</label>
                        <input type="number" name="jumlah_kriteria_terpenuhi" value="{{ $v('jumlah_kriteria_terpenuhi') }}" readonly class="w-20 border rounded-sm px-2 py-1 text-sm bg-slate-50" style="display:none">
                        <span id="badge-jumlah" class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-slate-200 text-slate-700">—</span>
                    </div>
                </div>
            </div>

            {{-- ================= GCS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Glasgow Coma Scale</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-lg">
                        <div>
                            <label class="block text-xs font-medium mb-1">Eye (E) <span class="text-red-500">*</span></label>
                            <select name="gcs_e" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">--</option>
                                @for ($i = 1; $i <= 4; $i++)
                                    <option value="{{ $i }}" {{ (string) $v('gcs_e') === (string) $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('gcs_e') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Motorik (M) <span class="text-red-500">*</span></label>
                            <select name="gcs_m" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">--</option>
                                @for ($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ (string) $v('gcs_m') === (string) $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('gcs_m') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Verbal (V) <span class="text-red-500">*</span></label>
                            <select name="gcs_v" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">--</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}" {{ (string) $v('gcs_v') === (string) $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            @error('gcs_v') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jumlah</label>
                            <input type="text" id="gcs-jumlah" value="{{ $v('gcs_jumlah') }}" readonly class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PENUTUP ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Keputusan Masuk ICU</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-2">Informed Consent Tindakan Invasif <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2"><input type="radio" name="informed_consent" value="YA" {{ $v('informed_consent') === 'YA' ? 'checked' : '' }}> YA</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="informed_consent" value="TIDAK" {{ $v('informed_consent') === 'TIDAK' ? 'checked' : '' }}> TIDAK</label>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1">Oksigen, kateter, NGT, intubasi</p>
                            @error('informed_consent') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-2">Prioritas Masuk ICU <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2"><input type="radio" name="prioritas_icu" value="I" {{ $v('prioritas_icu') === 'I' ? 'checked' : '' }}> I</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="prioritas_icu" value="II" {{ $v('prioritas_icu') === 'II' ? 'checked' : '' }}> II</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="prioritas_icu" value="III" {{ $v('prioritas_icu') === 'III' ? 'checked' : '' }}> III</label>
                            </div>
                            @error('prioritas_icu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-2">Kontra Indikasi Masuk ICU <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="inline-flex items-center gap-2"><input type="radio" name="kontra_indikasi" value="Ada" {{ $v('kontra_indikasi') === 'Ada' ? 'checked' : '' }}> Ada</label>
                                <label class="inline-flex items-center gap-2"><input type="radio" name="kontra_indikasi" value="Tidak" {{ $v('kontra_indikasi') === 'Tidak' ? 'checked' : '' }}> Tidak</label>
                            </div>
                            @error('kontra_indikasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Catatan</label>
                        <textarea name="catatan" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('catatan') }}</textarea>
                        @error('catatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                var jumlahEl = document.getElementById('badge-jumlah');
                var gcsEl = document.getElementById('gcs-jumlah');
                var statusNames = [];
                @foreach ($grupKriteria as $items)
                    @foreach ($items as $k => $item)
                        statusNames.push('{{ $k }}_status');
                    @endforeach
                @endforeach

                function hitung() {
                    var terpenuhi = 0;
                    var lengkap = true;
                    statusNames.forEach(function (name) {
                        var checked = document.querySelector('input[name="' + name + '"]:checked');
                        if (!checked) {
                            lengkap = false;
                        } else if (checked.value === 'YA') {
                            terpenuhi++;
                        }
                    });
                    jumlahEl.textContent = lengkap ? terpenuhi : '—';
                    jumlahEl.className = 'inline-block px-2 py-0.5 rounded text-xs font-semibold ' +
                        (lengkap && terpenuhi > 0 ? 'bg-red-100 text-red-700' : 'bg-slate-200 text-slate-700');
                }
                function hitungGcs() {
                    var e = parseInt(document.querySelector('[name="gcs_e"]').value || 0, 10);
                    var m = parseInt(document.querySelector('[name="gcs_m"]').value || 0, 10);
                    var v = parseInt(document.querySelector('[name="gcs_v"]').value || 0, 10);
                    gcsEl.value = (e && m && v) ? (e + m + v) : '';
                }
                document.querySelectorAll('input[type="radio"]').forEach(function (r) { r.addEventListener('change', hitung); });
                document.querySelectorAll('[name="gcs_e"],[name="gcs_m"],[name="gcs_v"]').forEach(function (s) { s.addEventListener('change', hitungGcs); });
                hitung();
                hitungGcs();
            })();
        </script>
    </x-emr-split-layout>
@endsection
