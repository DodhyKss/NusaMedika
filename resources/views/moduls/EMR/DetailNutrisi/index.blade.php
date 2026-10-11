@extends('layouts.iframe')

@section('content')
    @php
        use App\Helpers\GiziHelper;

        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Detail Nutrisi';
        $subtitleForm = 'Skor skrining gizi. Instrumen dipilih manual (override) tetapi punya default berdasarkan usia. Skor & kesimpulan selalu dihitung ulang di server.';
        $routeName = null;
        $routeUrl = url('emr/form/detail_nutrisi');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $label = \App\Http\Controllers\EMR\DetailNutrisi\DetailNutrisiController::LABEL_INSTRUMEN;
        $ket = \App\Http\Controllers\EMR\DetailNutrisi\DetailNutrisiController::KETERANGAN_INSTRUMEN;
        $labelSga = \App\Http\Controllers\EMR\DetailNutrisi\DetailNutrisiController::labelSga();
        $labelMna = \App\Http\Controllers\EMR\DetailNutrisi\DetailNutrisiController::labelMna();
        $labelMst = \App\Http\Controllers\EMR\DetailNutrisi\DetailNutrisiController::labelMst();
        $labelMstak = \App\Http\Controllers\EMR\DetailNutrisi\DetailNutrisiController::labelMstak();

        $instrumenAktif = $emr_data['instrumen'] ?? $instrumenDefault ?? 'SGA';
        $butir = GiziHelper::butirInstrumen($instrumenAktif);
        $diag = GiziHelper::diagnosisKhusus($instrumenAktif);

        // Nilai turunan untuk tampilan saja (TIDAK disimpan — server hitung ulang).
        $hasil = GiziHelper::hitung($instrumenAktif, is_array($emr_data) ? (array) $emr_data : []);

        $headers = [
            'instrumen' => 'Instrumen',
            'skor' => 'Skor',
            'kesimpulan' => 'Kesimpulan',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Detail Nutrisi"
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
                slug="detail_nutrisi"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Penilaian</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Evaluasi Ahli Gizi <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_evaluasi" value="{{ $v('tanggal_evaluasi') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('tanggal_evaluasi') border-red-500 @enderror" required>
                        @error('tanggal_evaluasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Instrumen Skrining <span class="text-red-500">*</span></label>
                        <select name="instrumen" id="instrumen" class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @foreach (GiziHelper::INSTRUMEN as $o)
                                <option value="{{ $o }}" data-keterangan="{{ $ket[$o] }}" {{ $instrumenAktif === $o ? 'selected' : '' }}>{{ $label[$o] }}</option>
                            @endforeach
                        </select>
                        @error('instrumen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Umia Pasien</label>
                        <input type="text" readonly value="{{ $usia !== null ? $usia.' tahun' : 'Tidak diketahui' }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Ahli Gizi</label>
                        <select name="ahli_gizi_id" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <option value="">-- Pilih --</option>
                            @foreach (\App\Models\Pegawai::aktif()->where('profesi_id', 10)->get() as $p)
                                <option value="{{ $p->pegawai_id }}" {{ (string) $emr_data['ahli_gizi_id'] === (string) $p->pegawai_id ? 'selected' : '' }}>
                                    {{ $p->nama_pegawai }}
                                </option>
                            @endforeach
                        </select>
                        @error('ahli_gizi_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-5 pb-4 bg-white text-xs text-slate-500" id="keterangan-instrumen">
                    {{ $ket[$instrumenAktif] }}
                </div>
            </div>

            {{-- ================= HASIL SKOR (read-only) ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Hasil Skrining</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Skor</label>
                        <input type="text" readonly id="skor-tampil" value="{{ $hasil['skor'] ?? '—' }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50 font-semibold text-lg">
                        <p class="text-xs text-slate-500 mt-1">Dihitung di server. Skor parsial tidak disimpan.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Kesimpulan</label>
                        <input type="text" readonly id="kesimpulan-tampil" value="{{ $hasil['kesimpulan'] ?? '—' }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm bg-slate-50 font-semibold">
                    </div>
                </div>
            </div>

            {{-- ================= BUTIR INSTRUMEN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Butir Instrumen — {{ $label[$instrumenAktif] }}</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4" id="panel-butir">
                    @php
                        $labelAktif = match ($instrumenAktif) {
                            'SGA' => $labelSga,
                            'MNA-SF' => $labelMna,
                            'MST' => $labelMst,
                            default => $labelMstak,
                        };
                    @endphp
                    @foreach ($butir as $variabel => $opsi)
                        <div class="border rounded-sm px-3 py-2">
                            <span class="text-xs font-medium text-slate-600 block mb-2">{{ $labelAktif[$variabel] ?? $variabel }}</span>
                            <div class="flex flex-wrap gap-4">
                                @foreach ($opsi as $nilai => $teks)
                                    <label class="flex items-center gap-2">
                                        <input type="radio" name="{{ $variabel }}" value="{{ $nilai }}" class="form-radio"
                                            {{ (string) $v($variabel) === (string) $nilai ? 'checked' : '' }}>
                                        <span class="text-sm">{{ $teks }} <span class="text-slate-400">({{ $nilai }})</span></span>
                                    </label>
                                @endforeach
                            </div>
                            @error($variabel) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                    @if ($diag !== [])
                        <div class="border rounded-sm px-3 py-3 bg-slate-50">
                            <span class="text-xs font-medium text-slate-600 block mb-2">
                                Diagnosis Khusus
                                @if ($instrumenAktif === 'MST-Anak')
                                    <span class="text-slate-400">(salah satu tercentang menambah skor +3)</span>
                                @elseif ($instrumenAktif === 'MNA-SF')
                                    <span class="text-slate-400">(tercentang bila ada diagnosis khusus — isi butir D)</span>
                                @endif
                            </span>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach ($diag as $variabel => $teks)
                                    <label class="flex items-start gap-2">
                                        <input type="checkbox" name="{{ $variabel }}" value="1" class="form-checkbox mt-0.5"
                                            {{ ! empty($emr_data[$variabel]) ? 'checked' : '' }}>
                                        <span class="text-xs">{{ $teks }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>

    @push('scripts')
        <script>
            // Panel butir berganti mengikuti instrumen. Butir dari instrumen
            // LAIN sengaja TIDAK dihapus dari DOM hanya dengan display:none:
            // nilainya akan dibuang server (filteredData) sehingga tidak
            // memengaruhi skor, dan baris emr_detail tetap lengkap.
            const panelButir = document.getElementById('panel-butir');
            const selectInstrumen = document.getElementById('instrumen');
            const ketInstrumen = document.getElementById('keterangan-instrumen');

            function terapkanInstrumen() {
                const opt = selectInstrumen.options[selectInstrumen.selectedIndex];
                if (ketInstrumen && opt) {
                    ketInstrumen.textContent = opt.dataset.keterangan || '';
                }
                hitungSkorTampil();
            }
            selectInstrumen.addEventListener('change', terapkanInstrumen);

            // Hitung skor di browser untuk UMUM PREVIEW saja. Angka yang
            // tersimpan tetap dihitung server (DetailNutrisiController).
            const SKOR = {
                'SGA': { items: ['sga_1','sga_2','sga_3','sga_4','sga_5'], min: 1 },
                'MNA-SF': { items: ['mna_1','mna_2','mna_3','mna_4'], min: null },
                'MST': { items: ['mst_a','mst_b','mst_c','mst_d','mst_e'], min: 0, f: true },
                'MST-Anak': { items: ['mstak_1','mstak_2','mstak_3','mstak_4'], min: 0, diagBonus: 3 }
            };

            function hitungSkorTampil() {
                const instrumen = selectInstrumen.value;
                const def = SKOR[instrumen];
                const skorField = document.getElementById('skor-tampil');
                const ketField = document.getElementById('kesimpulan-tampil');
                if (!def) { skorField.value = '—'; ketField.value = '—'; return; }

                let total = 0;
                let lengkap = true;

                function ambil(nama) {
                    const el = document.querySelector('[name="' + nama + '"]:checked');
                    return el ? parseInt(el.value, 10) : null;
                }

                def.items.forEach(function (nama) {
                    const nilai = ambil(nama);
                    if (nilai === null) lengkap = false; else total += nilai;
                });

                // MST: F1 (IMT) dan F2 (lingkar betis) saling eksklusif —
                // hanya salah satu yang boleh terisi.
                if (def.f) {
                    const f1 = ambil('mst_f1');
                    const f2 = ambil('mst_f2');
                    if (f1 === null && f2 === null) lengkap = false;
                    else if (f1 !== null) total += f1;
                    else total += f2;
                }

                // MST-Anak: satu diagnosis khusus tercentang menambah +3.
                if (def.diagBonus) {
                    const ada = Array.prototype.some.call(
                        document.querySelectorAll('[name^="mstak_diag_"]:checked'), function (el) { return el.checked; }
                    );
                    if (ada) total += def.diagBonus;
                }

                if (!lengkap) {
                    skorField.value = '—';
                    ketField.value = 'Lengkapi semua butir';
                    return;
                }
                skorField.value = total;

                if (instrumen === 'SGA') {
                    ketField.value = total <= 8 ? 'Gizi Baik (Normal)'
                        : total <= 12 ? 'Gizi Kurang'
                        : total <= 16 ? 'Gizi Buruk' : 'Gizi Buruk Sekali';
                } else if (instrumen === 'MNA-SF') {
                    ketField.value = total >= 2 ? 'Perlu Asesmen Lanjut oleh Ahli Gizi' : 'Gizi Baik (Normal)';
                } else if (instrumen === 'MST') {
                    ketField.value = total >= 12 ? 'Normal' : total >= 8 ? 'Berisiko Malnutrisi' : 'Malnutrisi';
                } else {
                    ketField.value = total === 0 ? 'Risiko Rendah' : total <= 3 ? 'Risiko Sedang' : 'Risiko Tinggi';
                }
            }

            document.getElementById('panel-butir').addEventListener('change', hitungSkorTampil);
            terapkanInstrumen();
        </script>
    @endpush
@endsection