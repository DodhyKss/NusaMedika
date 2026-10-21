@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Skrining MDPK/MPP';
        $subtitleForm = 'Multidisiplin Pathways of Care: skrining risiko, asesmen per aspek, dan implementasi beserta evaluasi.';
        $routeName = null;
        $routeUrl = url('emr/form/skrining_mpp');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');
        $cek = fn($key) => $v($key) === '1';

        $headers = ['tanggal_skrining' => 'Tgl Skrining', 'paraf_skrining_1' => 'Paraf 1'];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Skrining MDPK/MPP"
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
                slug="skrining_mpp"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ============ IDENTITAS SKRINING ============ --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Skrining</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Skrining <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_skrining" value="{{ $v('tanggal_skrining') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('tanggal_skrining') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Jam Skrining <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_skrining" value="{{ $v('jam_skrining') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('jam_skrining') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ============ PANEL 1 — FORM SKRINING MPP ============ --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Form Skrining MPP</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-5">
                    @foreach ($kategoriRisiko as $grup => $opsi)
                        <div class="border border-slate-100 rounded-sm p-4">
                            <p class="text-xs font-semibold text-slate-700 mb-2">
                                {{ ['risiko_tinggi' => 'Risiko Tinggi', 'risiko_pembiayaan' => 'Risiko Pembiayaan', 'kasus_kompleks' => 'Kasus Kompleks', 'risiko_lama_rawat' => 'Risiko Lama Rawat', 'risiko_psikososial' => 'Risiko Psikososial'][$grup] }}
                                <span class="text-red-500">*</span>
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach ($opsi as $i => $label)
                                    @php $idx = $i + 1; @endphp
                                    <label class="inline-flex items-center gap-2">
                                        <input type="hidden" name="{{ $grup }}_{{ $idx }}" value="0">
                                        <input type="checkbox" name="{{ $grup }}_{{ $idx }}" value="1" class="mpp-{{ $grup }}"
                                            {{ $cek($grup.'_'.$idx) ? 'checked' : '' }}> {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            @error($grup.'_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                            {{-- Kolom "Lainnya" per grup --}}
                            @if ($grup === 'risiko_lama_rawat')
                                <div class="mt-3" id="blok-{{ $grup }}-waktu" style="{{ $cek('risiko_lama_rawat_2') ? '' : 'display:none' }}">
                                    <label class="block text-xs font-medium mb-1">Waktu lebih (jam/hari)</label>
                                    <input type="text" name="risiko_lama_rawat_waktu_lebih" value="{{ $v('risiko_lama_rawat_waktu_lebih') }}"
                                        class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="mis. 12 jam">
                                    @error('risiko_lama_rawat_waktu_lebih') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @else
                                <div class="mt-3" id="blok-{{ $grup }}-lainnya" style="{{ $cek($grup.'_4') ? '' : 'display:none' }}">
                                    <label class="block text-xs font-medium mb-1">Keterangan Lainnya</label>
                                    <input type="text" name="{{ $grup }}_lainnya" value="{{ $v($grup.'_lainnya') }}"
                                        class="w-full border rounded-sm px-3 py-2 text-sm" placeholder="Isi bila Lainnya tercentang">
                                    @error($grup.'_lainnya') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                        @for ($i = 1; $i <= 5; $i++)
                            <div>
                                <label class="block text-xs font-medium mb-1">Paraf Skrining {{ $i }} <span class="text-red-500">*</span></label>
                                <input type="text" name="paraf_skrining_{{ $i }}" value="{{ $v('paraf_skrining_'.$i) }}"
                                    maxlength="100" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('paraf_skrining_'.$i) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- ============ PANEL 2 — FORM A ASESMEN MPP ============ --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Form A — Asesmen MPP</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    @foreach ($aspekFormA as $i => $aspek)
                        @php $idx = $i + 1; @endphp
                        <div class="border border-slate-100 rounded-sm p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="md:col-span-3">
                                <label class="block text-xs font-medium mb-1">{{ $idx }}. {{ $aspek }} — Plan <span class="text-red-500">*</span></label>
                                <textarea name="forma_plan_{{ $idx }}" rows="2" maxlength="2000"
                                    class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('forma_plan_'.$idx) }}</textarea>
                                @error('forma_plan_'.$idx) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">Paraf <span class="text-red-500">*</span></label>
                                <input type="text" name="forma_paraf_{{ $idx }}" value="{{ $v('forma_paraf_'.$idx) }}"
                                    maxlength="100" class="w-full border rounded-sm px-3 py-2 text-sm">
                                @error('forma_paraf_'.$idx) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ============ PANEL 3 — FORM B IMPLEMENTASI MPP ============ --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Form B — Implementasi MPP</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-3">
                    <p class="text-xs text-slate-500">Isi minimal satu baris (waktu + implementasi + evaluasi + paraf). Baris kosong akan dibuang saat disimpan.</p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border border-slate-200">
                            <thead>
                                <tr class="bg-slate-50 text-xs">
                                    <th class="border border-slate-200 px-2 py-2 w-40">Waktu</th>
                                    <th class="border border-slate-200 px-2 py-2">Implementasi</th>
                                    <th class="border border-slate-200 px-2 py-2">Evaluasi</th>
                                    <th class="border border-slate-200 px-2 py-2 w-32">Paraf</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 1; $i <= $maksBarisB; $i++)
                                    <tr>
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            <input type="date" name="formb_waktu_{{ $i }}" value="{{ $v('formb_waktu_'.$i) }}"
                                                class="w-full border rounded-sm px-2 py-1 text-xs">
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            <textarea name="formb_implementasi_{{ $i }}" rows="2" maxlength="2000"
                                                class="w-full border rounded-sm px-2 py-1 text-xs">{{ $v('formb_implementasi_'.$i) }}</textarea>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            <textarea name="formb_evaluasi_{{ $i }}" rows="2" maxlength="2000"
                                                class="w-full border rounded-sm px-2 py-1 text-xs">{{ $v('formb_evaluasi_'.$i) }}</textarea>
                                        </td>
                                        <td class="border border-slate-200 px-2 py-2 align-top">
                                            <input type="text" name="formb_paraf_{{ $i }}" value="{{ $v('formb_paraf_'.$i) }}"
                                                maxlength="100" class="w-full border rounded-sm px-2 py-1 text-xs">
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                    @error('formb_waktu_1') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

        </fieldset>

        <script>
            (function () {
                function syncLainnya(grup, idx, blokId) {
                    var cb = document.querySelector('input[name="' + grup + '_' + idx + '"]');
                    var blok = document.getElementById(blokId);
                    if (!cb || !blok) return;
                    blok.style.display = cb.checked ? '' : 'none';
                }
                ['risiko_tinggi', 'risiko_pembiayaan', 'kasus_kompleks', 'risiko_psikososial'].forEach(function (g) {
                    var cb = document.querySelector('input[name="' + g + '_4"]');
                    if (cb) cb.addEventListener('change', function () { syncLainnya(g, 4, 'blok-' + g + '-lainnya'); });
                    syncLainnya(g, 4, 'blok-' + g + '-lainnya');
                });
                var cbWaktu = document.querySelector('input[name="risiko_lama_rawat_2"]');
                if (cbWaktu) cbWaktu.addEventListener('change', function () { syncLainnya('risiko_lama_rawat', 2, 'blok-risiko_lama_rawat-waktu'); });
                syncLainnya('risiko_lama_rawat', 2, 'blok-risiko_lama_rawat-waktu');
            })();
        </script>
    </x-emr-split-layout>
@endsection
