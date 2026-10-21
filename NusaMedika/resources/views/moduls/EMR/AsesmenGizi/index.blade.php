@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Asesmen Gizi';
        $subtitleForm = 'Pengkajian gizi awal oleh tenaga gizi. IMT, status gizi, dan penurunan berat badan dihitung ulang di server — bukan dari input browser.';
        $routeName = null;
        $routeUrl = url('emr/form/asesmen_gizi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        // Nilai turunan dihitung ulang untuk ditampilkan (baca saja, TIDAK disimpan).
        $imtTampil = \App\Helpers\GiziHelper::imt($emr_data['berat_badan'] ?? null, $emr_data['tinggi_badan'] ?? null);
        $statusGizi = \App\Helpers\GiziHelper::statusGizi($imtTampil, $usia);
        $penurunanBb = \App\Helpers\GiziHelper::penurunanBbPersen($emr_data['berat_badan'] ?? null, $emr_data['bb_biasanya'] ?? null);
        $isAnak = $usia !== null && $usia < 18;

        $fkg = $fkg ?? \App\Http\Controllers\EMR\AsesmenGizi\AsesmenGiziController::FKG;
        $fkgLabel = $fkgLabel ?? \App\Http\Controllers\EMR\AsesmenGizi\AsesmenGiziController::FKG_LABEL;
        $opsiMenelan = $opsiMenelan ?? \App\Http\Controllers\EMR\AsesmenGizi\AsesmenGiziController::OPSI_MENELAN;

        $headers = [
            'jenis_asesmen' => 'Jenis Asesmen',
            'status_gizi_tampil' => 'Status Gizi',
            'rekomendasi' => 'Rekomendasi',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Asesmen Gizi"
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
                slug="asesmen_gizi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= TANGGAL & WAKTU ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanggal &amp; Waktu Asesmen</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Asesmen <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_asesmen" value="{{ $v('tanggal_asesmen') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('tanggal_asesmen') border-red-500 @enderror" required>
                        @error('tanggal_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Waktu Asesmen <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_asesmen" value="{{ $v('waktu_asesmen') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('waktu_asesmen') border-red-500 @enderror" required>
                        @error('waktu_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Asesmen <span class="text-red-500">*</span></label>
                        <select name="jenis_asesmen" class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            <option value="">-- Pilih --</option>
                            @foreach (['Perlu Asuhan Gizi', 'Re-Asesmen'] as $o)
                                <option value="{{ $o }}" {{ $v('jenis_asesmen') === $o ? 'selected' : '' }}>{{ $o }}</option>
                            @endforeach
                        </select>
                        @error('jenis_asesmen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Umur</label>
                        <input type="text" readonly value="{{ $usia !== null ? $usia.' tahun' : 'Tidak diketahui' }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50">
                    </div>
                </div>
            </div>

            {{-- ================= RIWAYAT KLIEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Klien</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Riwayat Penyakit Sebelumnya <span class="text-red-500">*</span></label>
                        <textarea name="riwayat_penyakit" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm @error('riwayat_penyakit') border-red-500 @enderror" required>{{ $v('riwayat_penyakit') }}</textarea>
                        @error('riwayat_penyakit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Riwayat Penyakit Keluarga</label>
                        <textarea name="riwayat_penyakit_keluarga" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('riwayat_penyakit_keluarga') }}</textarea>
                        @error('riwayat_penyakit_keluarga') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Diagnosa Medis <span class="text-red-500">*</span></label>
                        <textarea name="diagnosa_medis" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm @error('diagnosa_medis') border-red-500 @enderror" required>{{ $v('diagnosa_medis') }}</textarea>
                        @error('diagnosa_medis') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Alergi Makanan</label>
                            <textarea name="alergi" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('alergi') }}</textarea>
                            @error('alergi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Ketidaksukaan Makanan</label>
                            <input type="text" name="ketidaksukaan_makanan" value="{{ $v('ketidaksukaan_makanan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('ketidaksukaan_makanan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Pantangan Makan</label>
                            <input type="text" name="pantangan_makan" value="{{ $v('pantangan_makan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('pantangan_makan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= RIWAYAT DIET ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Riwayat Diet</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Pengalaman Diet / Konsul Sebelumnya <span class="text-red-500">*</span></label>
                        <div class="flex gap-4">
                            @foreach (['Tidak', 'Ada'] as $o)
                                <label class="flex items-center gap-2">
                                    <input type="radio" name="pengalaman_diit" value="{{ $o }}" class="form-radio"
                                        {{ $v('pengalaman_diit') === $o ? 'checked' : '' }} required>
                                    <span class="text-sm">{{ $o }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('pengalaman_diit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Asupan Makanan SMRS</label>
                        <textarea name="asupan_smrs" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('asupan_smrs') }}</textarea>
                        @error('asupan_smrs') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= ANTROPOMETRI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Antropometri</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Berat Badan (kg) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="berat_badan" value="{{ $v('berat_badan') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm @error('berat_badan') border-red-500 @enderror" required>
                            @error('berat_badan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Tinggi Badan (cm) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.1" name="tinggi_badan" value="{{ $v('tinggi_badan') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm @error('tinggi_badan') border-red-500 @enderror" required>
                            @error('tinggi_badan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">IMT (kg/m²)</label>
                            <input type="text" readonly id="imt-tampil" value="{{ $imtTampil ?? '—' }}" class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50 font-semibold">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Status Gizi</label>
                            <input type="text" readonly value="{{ $statusGizi ?? ($isAnak ? 'Diisi via Z-score' : '—') }}" class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Z-Score {{ $isAnak ? '(anak)' : '' }}</label>
                            <input type="number" step="0.01" name="z_score" value="{{ $v('z_score') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm @error('z_score') border-red-500 @enderror">
                            @error('z_score') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Lingkar Lengan Atas (cm)</label>
                            <input type="number" step="0.1" name="lingkar_lengan" value="{{ $v('lingkar_lengan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('lingkar_lengan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Riwayat Penurunan Berat Badan</label>
                            <input type="text" name="bb_biasanya" value="{{ $v('bb_biasanya') }}"
                                placeholder="mis. 70 kg selama 3 bulan" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('bb_biasanya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">% Penurunan BB</label>
                            <input type="text" readonly id="penurunan-tampil" value="{{ $penurunanBb !== null ? $penurunanBb.' %' : '—' }}" class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50 font-semibold">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= BIOKIMIA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Biokimia Terkait Gizi</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <textarea name="biokimia" rows="3" placeholder="Albumin, prealbumin, Hb, iron, dll."
                        class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('biokimia') }}</textarea>
                    @error('biokimia') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ================= FISIK KLINIS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Fisik Klinis</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Nafsu Makan <span class="text-red-500">*</span></label>
                            <select name="nafsu_makan" class="w-full border rounded-sm px-3 py-2 text-sm" required>
                                <option value="">-- Pilih --</option>
                                @foreach (['Baik', 'Kurang', 'Tidak Ada'] as $o)
                                    <option value="{{ $o }}" {{ $v('nafsu_makan') === $o ? 'selected' : '' }}>{{ $o }}</option>
                                @endforeach
                            </select>
                            @error('nafsu_makan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Gigi Geligi <span class="text-red-500">*</span></label>
                            <select name="gigi_geligi" class="w-full border rounded-sm px-3 py-2 text-sm" required>
                                <option value="">-- Pilih --</option>
                                @foreach (['Lengkap', 'Tidak Lengkap'] as $o)
                                    <option value="{{ $o }}" {{ $v('gigi_geligi') === $o ? 'selected' : '' }}>{{ $o }}</option>
                                @endforeach
                            </select>
                            @error('gigi_geligi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-2">Gangguan Menelan / Menghisap / Mengunyah <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-4">
                            @foreach ($opsiMenelan as $variabel => $label)
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" name="{{ $variabel }}" value="1" class="form-checkbox"
                                        {{ ! empty($emr_data[$variabel]) ? 'checked' : '' }}>
                                    <span class="text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('gangguan_menelan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-2">Keluhan Fisik Klinis Gizi <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            @foreach ($fkg as $variabel)
                                <div class="border rounded-sm px-3 py-2">
                                    <span class="text-xs text-slate-600 block mb-1">{{ $fkgLabel[$variabel] }}</span>
                                    <div class="flex gap-3">
                                        @foreach (['Ada', 'Tidak'] as $o)
                                            <label class="flex items-center gap-1">
                                                <input type="radio" name="{{ $variabel }}" value="{{ $o }}" class="form-radio"
                                                    {{ $v($variabel) === $o ? 'checked' : '' }} required>
                                                <span class="text-xs">{{ $o }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error($variabel) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= TANDA VITAL ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tanda Vital</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-2 md:grid-cols-6 gap-4">
                    @foreach ([
                        ['td_sistolik', 'Tekanan Darah Sistolik', 'mmHg'],
                        ['td_diastolik', 'Tekanan Darah Diastolik', 'mmHg'],
                        ['nadi', 'Nadi', '/menit'],
                        ['suhu', 'Suhu', '°C'],
                        ['pernapasan', 'Pernapasan', '/menit'],
                        ['saturasi', 'Saturasi Oksigen', '%'],
                    ] as [$name, $label, $satuan])
                        <div>
                            <label class="block text-xs font-medium mb-1">{{ $label }} <span class="text-slate-400">({{ $satuan }})</span></label>
                            <input type="number" step="0.1" name="{{ $name }}" value="{{ $v($name) }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error($name) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= REKOMENDASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Rekomendasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Diagnosa Gizi <span class="text-red-500">*</span></label>
                        <textarea name="diagnosa_gizi" rows="2" class="w-full border rounded-sm px-3 py-2 text-sm @error('diagnosa_gizi') border-red-500 @enderror" required>{{ $v('diagnosa_gizi') }}</textarea>
                        @error('diagnosa_gizi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Kebutuhan Energi Pasien <span class="text-red-500">*</span></label>
                            <input type="text" name="kebutuhan_energi" value="{{ $v('kebutuhan_energi') }}" placeholder="mis. 2000 kkal/hari"
                                class="w-full border rounded-sm px-3 py-2 text-sm @error('kebutuhan_energi') border-red-500 @enderror" required>
                            @error('kebutuhan_energi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Sumber Rekomendasi <span class="text-red-500">*</span></label>
                            <div class="flex gap-4 pt-2">
                                @foreach (['DPJP', 'Ahli Gizi'] as $o)
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="rekomendasi" value="{{ $o }}" class="form-radio"
                                            {{ $v('rekomendasi') === $o ? 'checked' : '' }} required>
                                        <span class="text-sm">{{ $o }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('rekomendasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Intervensi Gizi <span class="text-red-500">*</span></label>
                        <textarea name="intervensi_gizi" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm @error('intervensi_gizi') border-red-500 @enderror" required>{{ $v('intervensi_gizi') }}</textarea>
                        @error('intervensi_gizi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Monitoring &amp; Evaluasi (MONEV) <span class="text-red-500">*</span></label>
                        <textarea name="monev" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm @error('monev') border-red-500 @enderror" required>{{ $v('monev') }}</textarea>
                        @error('monev') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>

    @push('scripts')
        <script>
            // Hitung ulang IMT & % penurunan BB di sisi browser agar petugas
            // langsung melihat hasilnya. INGAT: angka yang tersimpan tetap
            // dihitung ulang di server (AsesmenGiziController::filteredData),
            // nilai di sini hanya tampilan.
            function hitungTurunan() {
                const bb = parseFloat(document.querySelector('[name="berat_badan"]').value);
                const tb = parseFloat(document.querySelector('[name="tinggi_badan"]').value);
                const imtField = document.getElementById('imt-tampil');
                if (isFinite(bb) && isFinite(tb) && bb > 0 && tb > 0) {
                    imtField.value = (bb / Math.pow(tb / 100, 2)).toFixed(1);
                } else {
                    imtField.value = '—';
                }

                const biasa = (document.querySelector('[name="bb_biasanya"]').value || '').match(/(\d+(?:[.,]\d+)?)/);
                const turunField = document.getElementById('penurunan-tampil');
                if (isFinite(bb) && biasa) {
                    const bbBiasanya = parseFloat(biasa[1].replace(',', '.'));
                    if (bbBiasanya > 0) {
                        turunField.value = (((bbBiasanya - bb) / bbBiasanya) * 100).toFixed(1) + ' %';
                    } else {
                        turunField.value = '—';
                    }
                } else {
                    turunField.value = '—';
                }
            }
            document.querySelectorAll('[name="berat_badan"], [name="tinggi_badan"], [name="bb_biasanya"]')
                .forEach(function (el) { el.addEventListener('input', hitungTurunan); });
            hitungTurunan();
        </script>
    @endpush
@endsection