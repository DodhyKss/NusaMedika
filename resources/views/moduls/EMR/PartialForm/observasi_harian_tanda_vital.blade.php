@php
    // Partial: Tanda Vital (form `tanda_vital`, dashboard Catatan Keperawatan
    // -> Observasi Harian -> Tanda Vital).
    //
    // Semua input di sini punya `id` = nama variabel karena `document.getElementById()`
    // dipakai script EWS untuk membaca 7 parameter dan melompat ke inputnya.
    // JANGAN pernah memberi id duplikat di halaman ini.
    //
    // `$ews` = hasil App\Helpers\EwsHelper::hitung() di server, dipakai untuk
    // merender rekap EVM di bawah tabel tanda vital.
@endphp

<x-emr-accordion id="acc-observasi-tanda-vital" title="A. Tanda Vital" bgClass="bg-slate-100" :isOpen="true">
    <div class="text-sm space-y-5">

        {{-- Tanggal & jam observasi: wajib diisi manual, tanpa default --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="tanggal_observasi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                    Tanggal Observasi <span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal_observasi" name="tanggal_observasi"
                       value="{{ old('tanggal_observasi', $emr_data['tanggal_observasi'] ?? '') }}"
                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                @error('tanggal_observasi')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="waktu_observasi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                    Waktu Observasi <span class="text-red-500">*</span>
                </label>
                {{-- Dipotong 5 karakter: nilai tersimpan bisa berupa "HH:MM:SS". --}}
                <input type="time" id="waktu_observasi" name="waktu_observasi" step="60"
                       value="{{ old('waktu_observasi', substr((string) ($emr_data['waktu_observasi'] ?? ''), 0, 5)) }}"
                       class="w-full border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                @error('waktu_observasi')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Tanda vital. `pernapasan`, `saturasi`, `td_sistolik`, `nadi`, dan
             `suhu` juga jadi parameter EVM, jadi dikaitkan ke script rekap EVM
             di bawah. --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $vital = [
                    ['id' => 'td_sistolik', 'label' => 'Sistolik (mmHg)', 'step' => '1', 'placeholder' => '120', 'min' => 0, 'max' => 300],
                    ['id' => 'td_diastolik', 'label' => 'Diastolik (mmHg)', 'step' => '1', 'placeholder' => '80', 'min' => 0, 'max' => 200],
                    ['id' => 'nadi', 'label' => 'Nadi (/menit)', 'step' => '1', 'placeholder' => '80', 'min' => 0, 'max' => 300],
                    ['id' => 'pernapasan', 'label' => 'Pernapasan (/menit)', 'step' => '1', 'placeholder' => '20', 'min' => 0, 'max' => 80],
                    ['id' => 'suhu', 'label' => 'Suhu (&#176;C)', 'step' => '0.1', 'placeholder' => '36.5', 'min' => 25, 'max' => 45],
                    ['id' => 'saturasi', 'label' => 'SpO2 (%)', 'step' => '1', 'placeholder' => '98', 'min' => 0, 'max' => 100],
                    ['id' => 'berat_badan', 'label' => 'Berat Badan (kg)', 'step' => '0.1', 'placeholder' => '60', 'min' => 0, 'max' => 500],
                    ['id' => 'tinggi_badan', 'label' => 'Tinggi Badan (cm)', 'step' => '0.1', 'placeholder' => '165', 'min' => 0, 'max' => 300],
                ];
            @endphp

            @foreach ($vital as $v)
                <div>
                    <label for="{{ $v['id'] }}" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                        {{ $v['label'] }}
                        @if (in_array($v['id'], ['td_sistolik', 'td_diastolik', 'nadi', 'pernapasan', 'suhu', 'saturasi'], true))
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <input type="number" step="{{ $v['step'] }}" min="{{ $v['min'] }}" max="{{ $v['max'] }}"
                           id="{{ $v['id'] }}" name="{{ $v['id'] }}"
                           value="{{ old($v['id'], $emr_data[$v['id']] ?? '') }}" placeholder="{{ $v['placeholder'] }}"
                           class="w-full text-sm text-right border border-slate-300 rounded px-3 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                    @error($v['id'])
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>

        {{-- BMI: TURUNAN, dihitung ulang di server (filteredData).
             Kolom ini hanya tampilan, TIDAK punya input name. --}}
        <div class="flex items-center gap-3 px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg">
            <span class="text-[11px] uppercase tracking-wider text-slate-500">BMI</span>
            <span id="preview_bmi" class="text-sm font-bold text-slate-700">
                {{ old('bmi', $emr_data['bmi'] ?? '') !== '' ? old('bmi', $emr_data['bmi'] ?? '') : '-' }}
            </span>
            <span id="preview_bmi_kategori" class="text-xs text-slate-500"></span>
            <p class="ml-auto text-xs text-slate-400">Dihitung otomatis dari berat &amp; tinggi badan.</p>
        </div>

        <div>
            <label for="keterangan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                Keterangan
            </label>
            <textarea id="keterangan" name="keterangan" rows="2"
                      placeholder="Contoh: Vital dalam batas normal, tidak ada keluhan baru"
                      class="w-full border border-slate-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">{{ old('keterangan', $emr_data['keterangan'] ?? '') }}</textarea>
            @error('keterangan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- ================= Rekap EVM =================
             RINGKASAN: tidak ada input angka di sini. Lima parameter sudah
             diisi di panel ini (pernapasan, SpO2, sistolik, nadi, suhu), sedangkan
             oksigen & kesadaran diisi di partial "Kesadaran dan Pemberian O2" —
             karena itu tiap baris punya tombol "Isi" yang melompat ke inputnya. --}}
        <div class="border border-slate-200 rounded-sm overflow-hidden">
            <div class="px-4 py-3 bg-red-50 border-b border-slate-200 flex items-center justify-between gap-3">
                <span class="text-red-600 font-medium text-[15px]">Rekap EVM (Early Warning Score)</span>
                <span id="ews_status" class="text-xs text-right {{ $ews['lengkap'] ? 'text-emerald-600' : 'text-slate-500' }}">
                    @if ($ews['lengkap'])
                        Lengkap{{ $ews['na'] ? ' · '.$ews['terukur'].' terukur, '.count($ews['na']).' tidak diukur' : '' }}
                    @else
                        Belum lengkap ({{ $ews['terisi'] }}/{{ count(\App\Helpers\EwsHelper::PARAMETER) }})
                    @endif
                </span>
            </div>
            <div class="p-4 bg-white space-y-4">

                {{-- Parameter yang belum "diselesaikan": masih kosong dan tidak
                     ditandai tidak diukur. Dirender dari server supaya tetap
                     informatif sebelum JS berjalan. --}}
                <div id="ews_peringatan" class="px-3 py-2.5 rounded-lg border text-xs {{ $ews['lengkap'] ? 'hidden' : 'bg-amber-50 border-amber-200 text-amber-800' }}">
                    @if (! $ews['lengkap'])
                        <span class="font-semibold">Parameter belum diisi:</span>
                        {{ collect($ews['detail'])->filter(fn ($b) => $b['skor'] === null && ! $b['na'])->pluck('label')->join(', ') ?: '-' }}.
                        Klik <strong>Isi</strong> untuk melompat ke inputnya, atau centang
                        <strong>Tidak Diukur</strong> bila tidak bisa diukur.
                    @endif
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-left" style="min-width: 680px;">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Parameter</th>
                                @unless ($isView)
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:80px;">Isi</th>
                                @endunless
                                <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:110px;">Nilai</th>
                                <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:90px;">Skor</th>
                                @unless ($isView)
                                    <th class="px-3 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center" style="width:120px;">Tidak Diukur</th>
                                @endunless
                            </tr>
                        </thead>
                        {{-- Skor & total di-render dari hasil hitung SERVER supaya tampilan benar
                             sejak halaman dibuka (sebelum JS jalan). JS lalu menghitung
                             ulang saat input berubah.

                             Total SELALU ditampilkan, bahkan saat parameter belum lengkap:
                             memakai jumlah berjalan (running total) dari parameter yang
                             sudah terisi. --}}
                        <tbody id="ews_body" class="divide-y divide-slate-100">
                            @foreach ($ews['detail'] as $baris)
                                <tr data-ews-param="{{ $baris['parameter'] }}" class="{{ $baris['na'] ? 'bg-slate-50' : '' }}">
                                    <td class="px-3 py-2 text-slate-700">
                                        {{ $baris['label'] }}
                                        @unless ($isView)
                                            <span class="block text-[10px] text-slate-400 font-normal normal-case">
                                                @if (in_array($baris['parameter'], ['oksigen', 'kesadaran'], true))
                                                    diisi di panel Kesadaran &amp; Oksigen
                                                @else
                                                    diisi di panel Tanda Vital
                                                @endif
                                            </span>
                                        @endunless
                                    </td>
                                    @unless ($isView)
                                        {{-- Tombol lompat: scroll ke input parameter lalu
                                             sorot, jadi petugas tidak perlu mencari
                                             sendirian di panel lain. --}}
                                        <td class="px-3 py-2 text-center">
                                            <button type="button" data-ews-lompat="{{ $baris['parameter'] }}"
                                                    class="ews-lompat inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded transition-colors"
                                                    title="Lompat ke input {{ $baris['label'] }}">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                                                Isi
                                            </button>
                                        </td>
                                    @endunless
                                    <td class="px-3 py-2 text-center text-slate-600" data-ews-nilai>{{ $baris['nilai'] }}</td>
                                    <td data-ews-skor class="{{ $baris['na']
                                        ? 'px-3 py-2 text-center text-slate-400 bg-slate-100 rounded'
                                        : ($baris['skor'] === null
                                            ? 'px-3 py-2 text-center text-slate-300'
                                            : 'px-3 py-2 text-center font-bold border rounded '.\App\Helpers\EwsHelper::badgeSkor((int) $baris['skor'])) }}">
                                        {{ $baris['skor'] ?? \App\Helpers\EwsHelper::LABEL_TIDAK_DIUKUR }}
                                    </td>
                                    @unless ($isView)
                                        <td class="px-3 py-2 text-center">
                                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                                <input type="checkbox" name="ews_na[]" value="{{ $baris['parameter'] }}"
                                                       class="ews-na rounded text-amber-600 focus:ring-amber-500 w-3.5 h-3.5"
                                                       @checked(in_array($baris['parameter'], $ews['na'], true))>
                                                <span class="text-[11px] text-slate-500">Tidak diukur</span>
                                            </label>
                                        </td>
                                    @endunless
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 border-t border-slate-200">
                            <tr>
                                <th class="px-3 py-2.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Total Skor</th>
                                @unless ($isView)
                                    <td></td>
                                @endunless
                                <td class="px-3 py-2.5 text-center text-lg font-bold text-slate-800" id="ews_total">
                                    {{ $ews['total'] }}
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span id="ews_kategori" class="inline-block border rounded px-2 py-0.5 text-[11px] font-semibold {{ $ews['lengkap'] ? \App\Helpers\EwsHelper::badgeClass($ews['kategori']) : 'bg-slate-50 text-slate-400 border-slate-200' }}">
                                        {{ $ews['lengkap'] ? 'Risiko '.$ews['kategori'] : 'Belum lengkap' }}
                                        @if ($ews['lengkap'] && $ews['na'])
                                            <span class="font-normal">({{ $ews['terukur'] }}/{{ count(\App\Helpers\EwsHelper::PARAMETER) }} terukur)</span>
                                        @endif
                                    </span>
                                </td>
                                @unless ($isView)
                                    <td class="px-3 py-2.5 text-center text-[11px] text-slate-500" id="ews_na_info">
                                        {{ $ews['na'] ? count($ews['na']).' parameter tidak diukur' : '' }}
                                    </td>
                                @endunless
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <p class="text-xs text-slate-400">
                    Skor 0&ndash;3 = risiko rendah, 4&ndash;6 = sedang, &ge; 7 = tinggi.
                    Parameter yang tidak bisa diukur (mis. tidak ada alat) centang
                    <strong>Tidak Diukur</strong> &mdash; parameter itu dihitung selesai dengan kontribusi 0,
                    dan namanya ikut tersimpan di riwayat. Angka final selalu dihitung ulang di server.
                </p>
            </div>
        </div>
    </div>
</x-emr-accordion>