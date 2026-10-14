@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Penilaian Bayi dengan APGAR Score';
        $subtitleForm = 'Penilaian APGAR neonatal pada menit 1, 5, 10, 15, 20. Menit 1 & 5 wajib — menit 10/15/20 opsional.';
        $routeName = null;
        $routeUrl = url('emr/form/penilaian_bayi_dengan_apgar_score');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $headers = ['apgar_total_1' => 'APGAR 1', 'apgar_total_5' => 'APGAR 5'];

        // Label nilai 0/1/2 per tanda (tabel legacy).
        $opsiTanda = [
            'frekuensi_jantung' => [0 => 'Tidak ada', 1 => '< 100', 2 => '> 100'],
            'usaha_napas' => [0 => 'Tidak ada', 1 => 'Lambat', 2 => 'Menangis kuat'],
            'tonus_otot' => [0 => 'Lumpuh', 1 => 'Ekstremitas fleksi sedikit', 2 => 'Gerak aktif'],
            'refleks' => [0 => 'Tidak bereaksi', 1 => 'Gerak sedikit', 2 => 'Reaksi melawan'],
            'warna' => [0 => 'Biru/Pucat', 1 => 'Tubuh kemerahan, tangan & kaki biru', 2 => 'Kemerahan'],
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Penilaian APGAR"
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
                slug="penilaian_bayi_dengan_apgar_score"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-cyan-600 font-medium text-[15px]">Tabel Penilaian APGAR</span>
                    <span class="text-xs text-slate-500">Menit 1 &amp; 5 wajib; 10/15/20 opsional</span>
                </div>
                <div class="p-5 bg-white overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-50">
                                <th class="border border-slate-200 px-3 py-2 text-left">Tanda</th>
                                @foreach ($menitApgar as $menit)
                                    <th class="border border-slate-200 px-3 py-2 text-center">
                                        Menit {{ $menit }}
                                        @if (in_array($menit, [1, 5], true))
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tandaApgar as $tanda => $label)
                                <tr>
                                    <td class="border border-slate-200 px-3 py-2 font-medium">{{ $label }}</td>
                                    @foreach ($menitApgar as $menit)
                                        @php $key = 'apgar_'.$tanda.'_'.$menit; @endphp
                                        <td class="border border-slate-200 px-2 py-2">
                                            <select name="{{ $key }}"
                                                data-tanda="{{ $tanda }}" data-menit="{{ $menit }}"
                                                class="apgar-select w-full border rounded-sm px-2 py-1 text-sm text-center">
                                                <option value="">-</option>
                                                @foreach ($opsiTanda[$tanda] as $nilai => $teks)
                                                    <option value="{{ $nilai }}" {{ (string) $v($key) === (string) $nilai ? 'selected' : '' }}>
                                                        {{ $nilai }} — {{ $teks }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            <tr class="bg-slate-50 font-semibold">
                                <td class="border border-slate-200 px-3 py-2">TOTAL SKOR</td>
                                @foreach ($menitApgar as $menit)
                                    <td class="border border-slate-200 px-2 py-2 text-center">
                                        <span class="apgar-total inline-block min-w-[2rem]" data-menit="{{ $menit }}">-</span>
                                        <input type="hidden" name="apgar_total_{{ $menit }}"
                                            value="{{ $v('apgar_total_'.$menit) }}" class="apgar-total-input">
                                        <span class="apgar-badge block text-xs mt-1" data-menit="{{ $menit }}"></span>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-xs text-slate-500 mt-3">
                        Interpretasi otomatis: <strong>Normal</strong> (7–10) ·
                        <strong>Perlu Perhatian</strong> (4–6) ·
                        <strong>Asfiksia Ringan</strong> (≤ 3). Skor dihitung ulang di server.
                    </p>
                </div>
            </div>
        </fieldset>

        <script>
            (function () {
                var warna = @json($warnaApgar);

                function hitungMenit(menit) {
                    var total = 0, lengkap = true;
                    document.querySelectorAll('.apgar-select[data-menit="' + menit + '"]').forEach(function (sel) {
                        if (sel.value === '') { lengkap = false; }
                        else { total += parseInt(sel.value, 10); }
                    });
                    var span = document.querySelector('.apgar-total[data-menit="' + menit + '"]');
                    var badge = document.querySelector('.apgar-badge[data-menit="' + menit + '"]');
                    var input = document.querySelector('.apgar-total-input[name="apgar_total_' + menit + '"]');
                    if (! span) { return; }
                    if (! lengkap) {
                        span.textContent = '-';
                        if (badge) { badge.textContent = ''; badge.className = 'apgar-badge block text-xs mt-1'; }
                        if (input) { input.value = ''; }
                        return;
                    }
                    span.textContent = total;
                    if (input) { input.value = total; }
                    var label = total >= 8 ? 'Normal' : (total >= 6 ? 'Perlu Perhatian' : 'Asfiksia Ringan');
                    if (badge) {
                        badge.textContent = label;
                        badge.className = 'apgar-badge block text-xs mt-1 px-1 rounded ' + (warna[label] ? warna[label].kelas : '');
                    }
                }

                document.querySelectorAll('.apgar-select').forEach(function (sel) {
                    sel.addEventListener('change', function () {
                        hitungMenit(this.getAttribute('data-menit'));
                    });
                });
                // Hitung ulang saat load (edit).
                document.querySelectorAll('.apgar-select').forEach(function (sel) {
                    hitungMenit(sel.getAttribute('data-menit'));
                });
            })();
        </script>
    </x-emr-split-layout>
@endsection
