@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Transfer Pasien Antar Ruangan';
        $subtitleForm = 'Formulir serah terima pasien antar ruang: identitas, diagnosis, terapi, tanda vital, dokumen pendukung & penerimaan.';
        $routeName = null;
        $routeUrl = url('emr/form/transfer_pasien');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn ($key) => old($key, $emr_data[$key] ?? '');

        $headers = [
            'tanggal_transfer' => 'Tanggal',
            'jam_transfer' => 'Jam',
            'kategori_transfer' => 'Kategori',
        ];

        $opsiKategori = ['Rawat Inap', 'Rawat Jalan', 'IGD'];
        $opsiKesadaran = \App\Helpers\SelectOption::get('kesadaran');
        $dokumenOpsi = ['Pengantar IGD', 'General Consent', 'Hasil Laboratorium', 'Hasil Radiologi', 'Konvensional', 'CT Scan'];
        // old('dokumen_pendukung') bisa berupa ARRAY (checkbox dokumen_pendukung[]
        // saat validasi gagal) — cast `(string)` pada array melempar "Array to
        // string conversion" dan merontokkan seluruh view.
        $dokumenRaw = $v('dokumen_pendukung');
        $dokumenTerpilih = array_filter(is_array($dokumenRaw) ? $dokumenRaw : explode(', ', (string) $dokumenRaw));
        $dokumenTerpilih = old('dokumen_pendukung', $dokumenTerpilih);

        $panelDiagnosis = [
            'diagnosa_medis' => 'Diagnosis Medis',
            'diagnosa_keperawatan' => 'Diagnosis Keperawatan',
            'masalah_keperawatan' => 'Masalah Keperawatan',
        ];
        $panelKebutuhan = [
            'kebutuhan_khusus' => 'Kebutuhan Khusus',
            'kewaspadaan' => 'Kewaspadaan',
        ];
        $panelTerapi = [
            'terapi_infus' => 'Terapi Infus',
            'oksigen' => 'Oksigen',
            'gangguan_indra' => 'Gangguan Indra',
            'prosedur_transfer' => 'Prosedur Transfer',
            'peralatan' => 'Peralatan',
            'perlu_alat_bantu' => 'Perlu Alat Bantu',
        ];
        $panelPenunjang = [
            'hasil_lab' => 'Hasil Laboratorium',
            'hasil_usg' => 'Hasil USG',
            'hasil_radiologi' => 'Hasil Radiologi',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Transfer Pasien"
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
                slug="transfer_pasien"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS TRANSFER ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Transfer</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Transfer <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_transfer" value="{{ $v('tanggal_transfer') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_transfer') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Transfer <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_transfer" value="{{ $v('jam_transfer') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_transfer') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Ruangan Asal <span class="text-red-500">*</span></label>
                            <x-select-ruang-perawatan name="ruangan_asal" id="ruangan_asal" :selected="$v('ruangan_asal')" />
                            @error('ruangan_asal') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Ruangan Tujuan <span class="text-red-500">*</span></label>
                            <x-select-ruang-perawatan name="ruangan_tujuan" id="ruangan_tujuan" :selected="$v('ruangan_tujuan')" />
                            @error('ruangan_tujuan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kategori Transfer <span class="text-red-500">*</span></label>
                            <select name="kategori_transfer" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($opsiKategori as $o)
                                    <option value="{{ $o }}" {{ $v('kategori_transfer') === $o ? 'selected' : '' }}>{{ $o }}</option>
                                @endforeach
                            </select>
                            @error('kategori_transfer') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= IDENTITAS PASIEN & TIM PENGANTAR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-emerald-50 border-b border-slate-200">
                    <span class="text-emerald-700 font-medium text-[15px]">Identitas Pasien &amp; Tim Pengantar</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Identitas Pasien <span class="text-red-500">*</span></label>
                        <textarea name="identitas_pasien" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('identitas_pasien') }}</textarea>
                        @error('identitas_pasien') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">DPJP Utama <span class="text-red-500">*</span></label>
                            <input type="text" name="dpjp_utama" value="{{ $v('dpjp_utama') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Nama DPJP utama...">
                            @error('dpjp_utama') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Dokter Pengantar <span class="text-red-500">*</span></label>
                            <x-select_dokter
                                name="dokter_pengantar"
                                id="dokter_pengantar"
                                label=""
                                placeholder="-- Pilih Dokter --"
                                :selected="$v('dokter_pengantar')"
                                :required="true" />
                            @error('dokter_pengantar') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        @for ($i = 1; $i <= 5; $i++)
                            <div>
                                <label class="block text-xs font-medium mb-1">Dokter Penanggung Rawat {{ $i }} <span class="text-red-500">*</span></label>
                                <x-select_dokter
                                    name="dokter_rawat_{{ $i }}"
                                    id="dokter_rawat_{{ $i }}"
                                    label=""
                                    placeholder="-- Pilih Dokter --"
                                    :selected="$v('dokter_rawat_'.$i)"
                                    :required="true" />
                                @error('dokter_rawat_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endfor
                        <div>
                            <label class="block text-xs font-medium mb-1">Perawat Ruangan <span class="text-red-500">*</span></label>
                            <input type="text" name="perawat_ruangan" value="{{ $v('perawat_ruangan') }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Nama perawat ruangan...">
                            @error('perawat_ruangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= DIAGNOSIS & KEBUTUHAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-indigo-50 border-b border-slate-200">
                    <span class="text-indigo-700 font-medium text-[15px]">Diagnosis, Masalah &amp; Kebutuhan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @foreach ($panelDiagnosis as $key => $label)
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                            <textarea name="{{ $key }}" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($key) }}</textarea>
                            @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($panelKebutuhan as $key => $label)
                            <div>
                                <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                                <textarea name="{{ $key }}" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($key) }}</textarea>
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= TERAPI, PENGLENGKAPAN & ALERGI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-purple-50 border-b border-slate-200">
                    <span class="text-purple-700 font-medium text-[15px]">Terapi, Peralatan Transfer &amp; Alergi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Nama Terapi / Obat <span class="text-red-500">*</span></label>
                        <textarea name="nama_terapi_obat" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('nama_terapi_obat') }}</textarea>
                        @error('nama_terapi_obat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($panelTerapi as $key => $label)
                            <div>
                                <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                                <input type="text" name="{{ $key }}" value="{{ $v($key) }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Riwayat Alergi <span class="text-red-500">*</span></label>
                        <textarea name="riwayat_alergi" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('riwayat_alergi') }}</textarea>
                        @error('riwayat_alergi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= TANDA VITAL & GCS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanda Vital, Kesadaran &amp; GCS</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Sistolik <span class="text-red-500">*</span></label>
                            <input type="number" name="sistolik" id="tv-sistolik" min="0" value="{{ $v('sistolik') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('sistolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Diastolik <span class="text-red-500">*</span></label>
                            <input type="number" name="diastolik" id="tv-diastolik" min="0" value="{{ $v('diastolik') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('diastolik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Nadi <span class="text-red-500">*</span></label>
                            <input type="number" name="nadi" id="tv-nadi" min="0" value="{{ $v('nadi') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('nadi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Pernapasan <span class="text-red-500">*</span></label>
                            <input type="number" name="pernapasan" id="tv-pernapasan" min="0" value="{{ $v('pernapasan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('pernapasan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Suhu (°C) <span class="text-red-500">*</span></label>
                            <input type="number" step="any" name="suhu" id="tv-suhu" min="0" value="{{ $v('suhu') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('suhu') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Kesadaran <span class="text-red-500">*</span></label>
                            <select name="kesadaran" class="w-full border rounded-sm px-3 py-2 text-sm">
                                <option value="">-- Pilih --</option>
                                @foreach ($opsiKesadaran as $o)
                                    <option value="{{ $o['value'] }}" {{ $v('kesadaran') === $o['value'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
                                @endforeach
                            </select>
                            @error('kesadaran') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div id="badge-tv" style="display:none" class="px-3 py-2 rounded-sm text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-300">
                        Salah satu tanda vital di luar rentang wajar — periksa ulang pengukuran.
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">GCS Eye (1–4) <span class="text-red-500">*</span></label>
                            <input type="number" name="gcs_eye" min="1" max="4" value="{{ $v('gcs_eye') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('gcs_eye') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">GCS Verbal (1–5) <span class="text-red-500">*</span></label>
                            <input type="number" name="gcs_verbal" min="1" max="5" value="{{ $v('gcs_verbal') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('gcs_verbal') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">GCS Motorik (1–6) <span class="text-red-500">*</span></label>
                            <input type="number" name="gcs_motorik" min="1" max="6" value="{{ $v('gcs_motorik') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('gcs_motorik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">GCS Score (3–15) <span class="text-red-500">*</span></label>
                            <input type="number" name="gcs_score" min="3" max="15" value="{{ $v('gcs_score') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('gcs_score') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= DOKUMEN PENDUKUNG ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-amber-50 border-b border-slate-200">
                    <span class="text-amber-700 font-medium text-[15px]">Dokumen Pendukung <span class="text-red-500">*</span></span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach ($dokumenOpsi as $item)
                            <label class="inline-flex items-center gap-2 border border-slate-200 rounded-sm px-3 py-2">
                                <input type="checkbox" name="dokumen_pendukung[]" value="{{ $item }}"
                                    {{ in_array($item, (array) $dokumenTerpilih) ? 'checked' : '' }}>
                                <span>{{ $item }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('dokumen_pendukung') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($panelPenunjang as $key => $label)
                            <div>
                                <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-red-500">*</span></label>
                                <input type="text" name="{{ $key }}" value="{{ $v($key) }}" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error($key) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Konsultasi <span class="text-red-500">*</span></label>
                        <textarea name="konsultasi" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('konsultasi') }}</textarea>
                        @error('konsultasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Catatan Transfer <span class="text-red-500">*</span></label>
                        <textarea name="catatan_transfer" rows="2" maxlength="2000" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('catatan_transfer') }}</textarea>
                        @error('catatan_transfer') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= PENERIMA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Penerimaan Pasien</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl">
                        <div>
                            <label class="block text-xs font-medium mb-1">Nama Penerima <span class="text-red-500">*</span></label>
                            <x-select_pegawai
                                name="nama_penerima"
                                id="nama_penerima"
                                label=""
                                placeholder="-- Pilih Penerima --"
                                :selected="$v('nama_penerima')"
                                :required="true" />
                            @error('nama_penerima') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal Penerimaan <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_penerimaan" value="{{ $v('tanggal_penerimaan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('tanggal_penerimaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam Penerimaan <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_penerimaan" value="{{ $v('jam_penerimaan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('jam_penerimaan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function num(id) {
                    var el = document.getElementById(id);
                    if (!el || el.value === '') return null;
                    return parseFloat(el.value);
                }

                // Badge tanda vital di luar rentang (bukan blocking).
                function syncBadgeTv() {
                    var s = num('tv-sistolik');
                    var d = num('tv-diastolik');
                    var n = num('tv-nadi');
                    var p = num('tv-pernapasan');
                    var t = num('tv-suhu');
                    var luar =
                        (s !== null && (s > 180 || s < 90)) ||
                        (d !== null && (d > 110 || d < 50)) ||
                        (n !== null && (n > 120 || n < 50)) ||
                        (p !== null && (p > 30 || p < 10)) ||
                        (t !== null && (t > 38 || t < 35));
                    var badge = document.getElementById('badge-tv');
                    if (badge) badge.style.display = luar ? '' : 'none';
                }
                ['tv-sistolik', 'tv-diastolik', 'tv-nadi', 'tv-pernapasan', 'tv-suhu'].forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el) el.addEventListener('input', syncBadgeTv);
                });
                syncBadgeTv();
            })();
        </script>
    </x-emr-split-layout>
@endsection
