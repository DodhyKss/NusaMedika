@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Form SBAR';
        $subtitleForm = 'Komunikasi terstruktur antar petugas: Situation, Background, Assessment, Recommendation.';
        $routeName = null;
        $routeUrl = url('emr/form/sbar');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;

        // Nilai awal textarea S & B: hasil prefill dari EMR lain (mode buat
        // baru) atau nilai tersimpan (mode edit). `old()` selalu menang.
        $defaultS = $emr_data['s_situation'] ?? '';
        $defaultS = $defaultS !== '' ? $defaultS : ($ringkasan['prefill_s'] ?? '');
        $defaultB = $emr_data['b_background'] ?? '';
        $defaultB = $defaultB !== '' ? $defaultB : ($ringkasan['prefill_b'] ?? '');

        $alergiTerisi = (string) old('alergi', $emr_data['alergi'] ?? '');
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat SBAR"
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
                slug="sbar"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="['tanggal_sbar' => 'Tanggal', 'shift' => 'Shift', 'urgensi' => 'Urgensi', 's_situation' => 'Situation', 'r_recommendation' => 'Rekomendasi']"
                :badges="[
                    'urgensi' => [
                        'Mendesak' => ['label' => 'Mendesak', 'class' => 'bg-red-50 text-red-700 border-red-200'],
                        'Segera' => ['label' => 'Segera', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
                        'Biasa' => ['label' => 'Biasa', 'class' => 'bg-slate-50 text-slate-600 border-slate-200'],
                    ],
                ]"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= Ringkasan Klinis (read-only, auto-fill) ================= --}}
            @if ($ringkasan['pernah_ada'])
                <div class="border border-slate-200 rounded-sm shadow-sm bg-slate-50">
                    <div class="px-4 py-3 bg-slate-100 border-b border-slate-200 flex items-center justify-between gap-3">
                        <span class="text-slate-600 font-medium text-[15px]">Ringkasan Klinis (dari EMR)</span>
                        <span class="text-[11px] text-slate-500">Acuan otomatis — tetap bisa diedit di bawah</span>
                    </div>
                    <div class="p-4 bg-white space-y-3 text-xs">
                        @if ($ringkasan['pasien']['identitas'])
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-slate-700">
                                @foreach ($ringkasan['pasien']['identitas'] as $ident)
                                    <span class="font-medium">{{ $ident }}</span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Vital. Sistolik & diastolik digabung jadi satu chip
                             "TD 150/95" supaya tidak tampil dua chip terpisah. --}}
                        @if ($ringkasan['vital'])
                            <div class="flex flex-wrap gap-2">
                                @if (! empty($ringkasan['vital']['td_sistolik']))
                                    <span class="inline-block bg-slate-100 text-slate-700 border border-slate-200 rounded px-2 py-0.5 font-medium">
                                        TD: {{ $ringkasan['vital']['td_sistolik'] }}{{ ! empty($ringkasan['vital']['td_diastolik']) ? '/'.$ringkasan['vital']['td_diastolik'] : '' }}
                                    </span>
                                @endif
                                @foreach (['nadi' => 'Nadi', 'suhu' => 'Suhu', 'pernapasan' => 'RR', 'saturasi' => 'SpO2', 'kesadaran' => 'Kesadaran', 'total_ews' => 'EWS', 'kategori_ews' => 'Kategori EWS'] as $kVital => $lblVital)
                                    @if (! empty($ringkasan['vital'][$kVital]))
                                        <span class="inline-block bg-slate-100 text-slate-700 border border-slate-200 rounded px-2 py-0.5 font-medium">
                                            {{ $lblVital }}: {{ $ringkasan['vital'][$kVital] }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        @if ($ringkasan['alergi'])
                            <div class="text-red-700">
                                <span class="font-semibold">Alergi:</span> {{ $ringkasan['alergi'] }}
                            </div>
                        @endif

                        @if (! empty($ringkasan['terapi']))
                            <div class="text-slate-700"><span class="font-semibold">Intervensi sebelumnya:</span> {{ $ringkasan['terapi'] }}</div>
                        @endif

                        @if (! empty($ringkasan['harian']))
                            <div class="text-slate-700"><span class="font-semibold">Keluhan harian:</span> {{ $ringkasan['harian'] }}</div>
                        @endif

                        @if (! empty($ringkasan['soap']))
                            <div class="text-slate-700">
                                <span class="font-semibold">SOAP terakhir:</span>
                                <ul class="mt-1 space-y-0.5 list-disc list-inside">
                                    @foreach (['subjective' => 'S', 'objective' => 'O', 'assessment' => 'A', 'planning' => 'P', 'instruksi' => 'I'] as $sk => $slabel)
                                        @if (! empty($ringkasan['soap'][$sk]))
                                            <li>{{ $slabel }}: {{ \Illuminate\Support\Str::limit($ringkasan['soap'][$sk], 120) }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- ================= Identitas SBAR ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas SBAR</span>
                </div>
                <div class="p-5 bg-white space-y-5 text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="tanggal_sbar" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Tanggal SBAR <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_sbar" name="tanggal_sbar"
                                   value="{{ old('tanggal_sbar', $emr_data['tanggal_sbar'] ?? '') }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('tanggal_sbar')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="waktu_sbar" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Jam SBAR <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="waktu_sbar" name="waktu_sbar" step="60"
                                   value="{{ old('waktu_sbar', substr((string) ($emr_data['waktu_sbar'] ?? ''), 0, 5)) }}"
                                   class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @error('waktu_sbar')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="shift" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Shift</label>
                            <select id="shift" name="shift"
                                    class="select2 w-full text-sm border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('shift', old('shift', $emr_data['shift'] ?? null), '-- Pilih Shift --') !!}
                            </select>
                            @error('shift')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="urgensi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Urgensi <span class="text-red-500">*</span>
                            </label>
                            <select id="urgensi" name="urgensi"
                                    class="select2 w-full text-sm border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('urgensi_sbar', old('urgensi', $emr_data['urgensi'] ?? null), '-- Pilih Urgensi --') !!}
                            </select>
                            @error('urgensi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="cara_komunikasi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                                Cara Komunikasi <span class="text-red-500">*</span>
                            </label>
                            <select id="cara_komunikasi" name="cara_komunikasi"
                                    class="select2 w-full text-sm border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                                {!! \App\Helpers\SelectOption::render('cara_komunikasi', old('cara_komunikasi', $emr_data['cara_komunikasi'] ?? null), '-- Pilih Cara --') !!}
                            </select>
                            @error('cara_komunikasi')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-select_pegawai
                            :selected="old('penerima_id', $emr_data['penerima_id'] ?? '')"
                            name="penerima_id"
                            id="penerima_id"
                            label="Penerima Informasi"
                            placeholder="-- Pilih Penerima --"
                            required
                        />
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pelapor</label>
                            <div class="px-3 py-2.5 bg-slate-100 border border-slate-200 rounded text-sm font-semibold text-slate-700">
                                {{ auth()->user()?->nama_pegawai ?? '-' }}
                            </div>
                            <p class="mt-1.5 text-xs text-slate-400">Otomatis dari user yang sedang login.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= 4 Unsur SBAR ================= --}}
            @php
                // Kartu 4 unsur: huruf, warna header, judul, keterangan, contoh isi.
                $unsur = [
                    ['nama' => 's_situation', 'huruf' => 'S', 'warna' => 'blue', 'judul' => 'Situation',
                        'ket' => 'Siapa pasien, di mana, dan masalah utama saat ini.',
                        'contoh' => "Contoh: Tn. A, 45 tahun, No. MR 00-01-02-03, di Ruang Melati 2, baru masuk pukul 08.00.\nKeluhan: sesak napas sejak semalam, suhu 38,5."],
                    ['nama' => 'b_background', 'huruf' => 'B', 'warna' => 'slate', 'judul' => 'Background',
                        'ket' => 'Konteks yang perlu diketahui penerima informasi.',
                        'contoh' => "Contoh: Hipertensi sejak 5 tahun, Diabetes tipe 2 sejak 2021.\nVital terakhir: TD 130/80, nadi 88, RR 22, SpO2 96%, suhu 38,5.\nSudah dapat oksigen nasal 2 L/menit."],
                    ['nama' => 'a_assessment', 'huruf' => 'A', 'warna' => 'amber', 'judul' => 'Assessment',
                        'ket' => 'Penilaian klinis petugas terhadap kondisi pasien saat ini.',
                        'contoh' => "Contoh: Pasien gelisah, takikardia, ronki basal. Penilaian sementara: pneumonia, perlu ETH duktus.\nEWS 6 sehingga risiko sedang."],
                    ['nama' => 'r_recommendation', 'huruf' => 'R', 'warna' => 'emerald', 'judul' => 'Recommendation',
                        'ket' => 'Apa yang diminta, siapa melakukan, dan kapan.',
                        'contoh' => "Contoh: Mohon foto toraks dan pesan dokter untuk visitation jam 10.00.\nBerhenti oksigen bila SpO2 > 94% dan Assessment ulang tiap 2 jam."],
                ];
            @endphp

            @foreach ($unsur as $u)
                <div class="border border-slate-200 rounded-sm shadow-sm">
                    <div class="px-4 py-3 bg-{{ $u['warna'] }}-50 border-b border-slate-200 flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-{{ $u['warna'] }}-600 text-white text-xs font-bold">{{ $u['huruf'] }}</span>
                        <span class="text-{{ $u['warna'] }}-700 font-medium text-[15px]">{{ $u['judul'] }}</span>
                    </div>
                    <div class="p-5 bg-white text-sm">
                        <textarea id="{{ $u['nama'] }}" name="{{ $u['nama'] }}" rows="5"
                                  placeholder="{{ $u['contoh'] }}"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old($u['nama'], $u['nama'] === 's_situation' ? $defaultS : ($u['nama'] === 'b_background' ? $defaultB : ($emr_data[$u['nama']] ?? ''))) }}</textarea>
                        <p class="mt-1.5 text-xs text-slate-400">{{ $u['ket'] }}</p>
                        @error($u['nama'])
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endforeach

            {{-- ================= Alergi & Catatan ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-yellow-50 border-b border-slate-200">
                    <span class="text-yellow-700 font-medium text-[15px]">Alergi &amp; Catatan Tambahan</span>
                </div>
                <div class="p-5 bg-white space-y-4 text-sm">
                    <div>
                        <label for="alergi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Alergi</label>
                        <input type="text" id="alergi" name="alergi"
                               value="{{ old('alergi', $emr_data['alergi'] ?? '') }}"
                               placeholder="Contoh: Penisilin, sulfa — atau tulis Tidak ada"
                               class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
                        <p class="mt-1.5 text-xs text-slate-400">Bila sudah tercantum di Ringkasan Klinis, boleh dikosongkan di sini.</p>
                        @error('alergi')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="catatan_tambahan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Catatan Tambahan</label>
                        <textarea id="catatan_tambahan" name="catatan_tambahan" rows="2"
                                  placeholder="Catatan lain bila diperlukan"
                                  class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('catatan_tambahan', $emr_data['catatan_tambahan'] ?? '') }}</textarea>
                        @error('catatan_tambahan')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

        </fieldset>

    </x-emr-split-layout>

@endsection()