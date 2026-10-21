@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Diet Pasien';
        $subtitleForm = 'Jenis diet yang dipakai pasien beserta faktor penyakit penyertanya. Pilihan diambil dari master diet.';
        $routeName = null;
        $routeUrl = url('emr/form/diet_pasien');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        // Nilai `jenis_diagnosa_diet` disimpan comma-separated; dipecah lagi di
        // sini agar checkbox bisa dicentang sesuai nilai lama.
        $terpilih = array_filter(array_map(
            'trim',
            explode(',', (string) ($emr_data['jenis_diagnosa_diet'] ?? ''))
        ));

        $headers = [
            'jenis_diet' => 'Jenis Diet',
            'jenis_diagnosa_diet' => 'Faktor Penyerta',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Diet Pasien"
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
                slug="diet_pasien"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= JENIS DIET ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Jenis Diet</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Jenis Diet Pasien <span class="text-red-500">*</span></label>
                        <select name="jenis_diet" class="w-full border rounded-sm px-3 py-2 text-sm @error('jenis_diet') border-red-500 @enderror" required>
                            <option value="">-- Pilih jenis diet --</option>
                            @foreach ($daftarDiet as $d)
                                <option value="{{ $d->nama_diet }}" {{ $v('jenis_diet') === $d->nama_diet ? 'selected' : '' }}
                                    @if ($d->keterangan) title="{{ $d->keterangan }}" @endif>
                                    {{ $d->nama_diet }}
                                </option>
                            @endforeach
                        </select>
                        @error('jenis_diet') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium mb-1">
                            Keterangan Diet
                            @if ($v('jenis_diet'))
                                <span class="text-slate-500 font-normal">
                                    — {{ $daftarDiet->firstWhere('nama_diet', $v('jenis_diet'))->keterangan ?? 'tidak ada keterangan' }}
                                </span>
                            @endif
                        </label>
                        <input type="text" name="keterangan" value="{{ $v('keterangan') }}" class="w-full border rounded-sm px-3 py-2 text-sm">
                        @error('keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= FAKTOR PENYERTA ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Faktor Penyakit Penyerta</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <p class="text-xs text-slate-500 mb-3">
                        Pilih satu atau lebih faktor penyakit yang mendasari diet di atas. Minimal satu.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach ($daftarDiagnosa as $d)
                            <label class="flex items-start gap-2 border rounded-sm px-3 py-2">
                                <input type="checkbox" name="jenis_diagnosa_diet[]" value="{{ $d->nama_diagnosa_diet }}"
                                    class="form-checkbox mt-0.5"
                                    {{ in_array($d->nama_diagnosa_diet, old('jenis_diagnosa_diet', $terpilih), true) ? 'checked' : '' }}>
                                <span class="text-sm">{{ $d->nama_diagnosa_diet }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('jenis_diagnosa_diet') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
                    @error('jenis_diagnosa_diet.*') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>
@endsection