@php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = $judulPanel;
        $subtitleForm = 'Pemeriksaan spesialis pada episode Medical Check Up. Kolom uraian hanya aktif bila dipilih "Ada".';
        $routeName = null;
        $routeUrl = url('emr/form/'.$slug ?? '');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $headers = ['diagnosa' => 'Diagnosa'];
@endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat {{ $judulPanel }}"
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
                :slug="$slug"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= KELUHAN SAAT INI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Keluhan Saat Ini</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <textarea name="keluhan_saat_ini" rows="2"
                        class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keluhan_saat_ini') }}</textarea>
                    @error('keluhan_saat_ini') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                    @foreach (($anamnesisEkstra ?? []) as $ekstraKey => $ekstraLabel)
                        <div class="mt-4">
                            <label class="block text-xs font-medium mb-1">{{ $ekstraLabel }}</label>
                            <textarea name="{{ $ekstraKey }}" rows="2"
                                class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v($ekstraKey) }}</textarea>
                            @error($ekstraKey) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ================= PARAMETER PER ORGAN ================= --}}
            @include('moduls.EMR.PartialForm.mcu_spesialistik', [
                'judulPanel' => $judulPanel,
                'organ' => $organ,
                'emr_data' => $emr_data,
                'isView' => $isView,
            ])

            {{-- ================= RINGKASAN ORGAN LAIN (read-only) ================= --}}
            @if (! empty($ringkasan))
                <div class="border border-slate-200 rounded-sm shadow-sm">
                    <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                        <span class="font-medium text-[15px]">Ringkasan Pemeriksaan MCU Lainnya</span>
                    </div>
                    <div class="p-5 bg-white text-sm">
                        <table class="w-full text-xs border-collapse">
                            <thead><tr class="bg-slate-100 text-left">
                                <th class="px-2 py-2 border">Organ</th>
                                <th class="px-2 py-2 border">Temuan</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($ringkasan as $b)
                                    <tr>
                                        <td class="px-2 py-1 border">{{ $b['organ'] }}</td>
                                        <td class="px-2 py-1 border">
                                            @if ($b['terisi'] && $b['temuan'] !== '')
                                                {{ $b['temuan'] }}
                                            @else
                                                <span class="text-slate-400">Belum diperiksa</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ================= DIAGNOSA & REKOMENDASI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Diagnosa &amp; Rekomendasi</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Diagnosa <span class="text-red-500">*</span></label>
                        <textarea name="diagnosa" rows="3"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('diagnosa') border-red-500 @enderror" required>{{ $v('diagnosa') }}</textarea>
                        @error('diagnosa') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Rekomendasi <span class="text-red-500">*</span></label>
                        <textarea name="rekomendasi" rows="3"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('rekomendasi') border-red-500 @enderror" required>{{ $v('rekomendasi') }}</textarea>
                        @error('rekomendasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>
