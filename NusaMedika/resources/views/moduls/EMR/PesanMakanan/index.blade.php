@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];
        $titleForm = 'Form Pesan Makanan & Monitoring Asupan';
        $subtitleForm = 'Jadwal distribusi makan pagi, siang, dan sore beserta petugas yang mendistribusikannya. Satu tanggal + satu jenis pesanan hanya boleh satu baris.';
        $routeName = null;
        $routeUrl = url('emr/form/pesan_makanan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
        $v = fn($key) => old($key, $emr_data[$key] ?? '');

        $opsiJenis = \App\Http\Controllers\EMR\PesanMakanan\PesanMakananController::OPSI_JENIS;

        $headers = [
            'tanggal_pesan' => 'Tanggal',
            'jenis_pesanan' => 'Jenis Pesanan',
            'waktu_pagi' => 'Pagi',
            'waktu_siang' => 'Siang',
            'waktu_sore' => 'Sore',
        ];
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Pesan Makanan"
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
                slug="pesan_makanan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="$headers"
            />
        </x-slot>

        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>

            {{-- ================= IDENTITAS PESANAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Identitas Pesanan</span>
                </div>
                <div class="p-5 bg-white text-sm grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Tanggal Pesan <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_pesan" value="{{ $v('tanggal_pesan') }}"
                            class="w-full border rounded-sm px-3 py-2 text-sm @error('tanggal_pesan') border-red-500 @enderror" required>
                        @error('tanggal_pesan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium mb-1">Jenis Pesanan <span class="text-red-500">*</span></label>
                        <select name="jenis_pesanan" class="w-full border rounded-sm px-3 py-2 text-sm @error('jenis_pesanan') border-red-500 @enderror" required>
                            <option value="">-- Pilih --</option>
                            @foreach ($opsiJenis as $o)
                                <option value="{{ $o }}" {{ $v('jenis_pesanan') === $o ? 'selected' : '' }}>{{ $o }}</option>
                            @endforeach
                        </select>
                        @error('jenis_pesanan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- ================= JADWAL DISTRIBUSI ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Jadwal Distribusi Makan</span>
                </div>
                <div class="p-5 bg-white text-sm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach ([
                            ['waktu_pagi', 'petugas_pagi', 'Pagi'],
                            ['waktu_siang', 'petugas_siang', 'Siang'],
                            ['waktu_sore', 'petugas_sore', 'Sore'],
                        ] as [$waktu, $petugas, $label])
                            <div class="border rounded-sm p-4 space-y-3">
                                <span class="block text-sm font-semibold text-slate-700 border-b border-slate-200 pb-2">{{ $label }}</span>
                                <div>
                                    <label class="block text-xs font-medium mb-1">Waktu <span class="text-red-500">*</span></label>
                                    <input type="time" name="{{ $waktu }}" value="{{ $v($waktu) }}"
                                        class="w-full border rounded-sm px-3 py-2 text-sm @error($waktu) border-red-500 @enderror" required>
                                    @error($waktu) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1">Petugas <span class="text-red-500">*</span></label>
                                    <input type="text" name="{{ $petugas }}" value="{{ $v($petugas) }}"
                                        class="w-full border rounded-sm px-3 py-2 text-sm @error($petugas) border-red-500 @enderror" required>
                                    @error($petugas) <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= TAMBAHAN ================= --}}
            <div class="border border-slate-200 rounded-sm shadow-sm">
                <div class="px-4 py-3 bg-cyan-50 border-b border-slate-200">
                    <span class="text-cyan-600 font-medium text-[15px]">Tambahan</span>
                </div>
                <div class="p-5 bg-white text-sm space-y-4">
                    <div>
                        <label class="block text-xs font-medium mb-1">Prioritas</label>
                        <select name="prioritas" class="w-full border rounded-sm px-3 py-2 text-sm">
                            <option value="">-- Pilih --</option>
                            @foreach (['BIASA', 'CITO'] as $o)
                                <option value="{{ $o }}" {{ $v('prioritas') === $o ? 'selected' : '' }}>{{ $o }}</option>
                            @endforeach
                        </select>
                        @error('prioritas') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">Keterangan</label>
                        <textarea name="keterangan" rows="3" class="w-full border rounded-sm px-3 py-2 text-sm">{{ $v('keterangan') }}</textarea>
                        @error('keterangan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        </fieldset>
    </x-emr-split-layout>
@endsection