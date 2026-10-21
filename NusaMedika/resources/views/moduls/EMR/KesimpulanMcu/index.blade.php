@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Kesimpulan MCU';
        $subtitleForm = 'Penutup episode Medical Check Up. Ringkasan temuan seluruh pemeriksaan spesialis pada kunjungan ini.';
        $routeName = null;
        $routeUrl = url('emr/form/kesimpulan_mcu');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        // Prefill interpretasi hanya teks; EMR tersimpan menang, old() menang atas keduanya.
        if ($v('interpretasi') === '' && ! $isEdit) {
            $nilaiInterpretasi = $prefillInterpretasi;
        } else {
            $nilaiInterpretasi = $v('interpretasi');
        }

        $headers = [
            'kesimpulan_kategori' => 'Kesimpulan',
            'rekomendasi' => 'Rekomendasi',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Kesimpulan MCU"
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
                slug="kesimpulan_mcu"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= SOFT-GATE: ORGAN BELUM DIPERIKSA ================= --}}
            @if (count($organBelum) > 0)
                <div class="border border-amber-300 bg-amber-50 rounded-sm px-4 py-3 text-sm text-amber-800">
                    <strong>{{ count($organBelum) }} dari {{ count($ringkasan) }} pemeriksaan belum diperiksa:</strong>
                    {{ implode(', ', $organBelum) }}. Kesimpulan tetap bisa disimpan, namun pastikan seluruh
                    pemeriksaan spesialis sudah diisi sebelum menerbitkan surat keterangan.
                </div>
            @endif

            @if ($jumlahTidakNormal >= 3)
                <div class="border border-red-300 bg-red-50 rounded-sm px-4 py-3 text-sm text-red-700">
                    Terdapat {{ $jumlahTidakNormal }} temuan tidak normal — pertimbangkan kategori TIDAK SEHAT.
                </div>
            @endif

            {{-- ================= RINGKASAN TEMUAN (read-only) ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Ringkasan Temuan per Pemeriksaan</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <table class="w-full text-xs border-collapse">
                        <thead><tr class="bg-slate-100 text-left">
                            <th class="px-2 py-2 border">Pemeriksaan</th>
                            <th class="px-2 py-2 border">Temuan</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($ringkasan as $baris)
                                <tr>
                                    <td class="px-2 py-1 border">{{ $baris['organ'] }}</td>
                                    <td class="px-2 py-1 border">
                                        @if ($baris['terisi'] && $baris['temuan'] !== '')
                                            {{ $baris['temuan'] }}
                                        @else
                                            <span class="inline-block px-2 py-0.5 rounded-sm bg-slate-100 text-slate-500">Belum diperiksa</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ================= INTERPRETASI & KESIMPULAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Interpretasi &amp; Kesimpulan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium mb-1">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_kesimpulan" value="{{ $v('tanggal_kesimpulan') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @error('tanggal_kesimpulan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Jam <span class="text-red-500">*</span></label>
                            <input type="time" name="jam_kesimpulan" value="{{ $v('jam_kesimpulan') }}"
                                class="w-full border rounded-sm px-3 py-2 text-sm" required>
                            @error('jam_kesimpulan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1">Dokter Pemeriksa</label>
                            <input type="number" name="dokter_pemeriksa_id" value="{{ $v('dokter_pemeriksa_id') }}"
                                placeholder="ID pegawai (opsional — default petugas login)"
                                class="w-full border rounded-sm px-3 py-2 text-sm">
                            @error('dokter_pemeriksa_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Interpretasi Hasil MCU <span class="text-red-500">*</span></label>
                        <textarea name="interpretasi" rows="4"
                            class="w-full border rounded-sm px-3 py-2 text-sm">{{ $nilaiInterpretasi }}</textarea>
                        <p class="text-xs text-slate-500 mt-1">Teks awal diisi otomatis dari ringkasan temuan — boleh disunting.</p>
                        @error('interpretasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-2">Kesimpulan <span class="text-red-500">*</span></label>
                        <div class="space-y-1">
                            @foreach ($kategoriKesimpulan as $nilai => $label)
                                <label class="flex items-start gap-2 text-sm">
                                    <input type="radio" name="kesimpulan_kategori" value="{{ $nilai }}" class="mt-0.5"
                                        {{ (string) $v('kesimpulan_kategori') === (string) $nilai ? 'checked' : '' }}>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('kesimpulan_kategori') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Catatan Kesimpulan</label>
                        <textarea name="kesimpulan_catatan" rows="2" placeholder="Wajib diisi bila kategori 2, 3, atau 4"
                            class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('kesimpulan_catatan') }}</textarea>
                        @error('kesimpulan_catatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Rekomendasi <span class="text-red-500">*</span></label>
                        <textarea name="rekomendasi" rows="3"
                            class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('rekomendasi') }}</textarea>
                        @error('rekomendasi') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>
@endsection
