@extends('layouts.app')

@section('content')
<!-- Page Header -->
<x-page-header
    title="List Pasien Rawat Jalan"
    subtitle="Silahkan lakukan pencarian terlebih dahulu untuk menampilkan data."
></x-page-header>

{{-- Validation Errors --}}
<x-validation-error></x-validation-error>

<div class="bg-white rounded-xl border-slate-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        <h2 class="text-sm font-semibold text-slate-700">Filter Pencarian</h2>
    </div>
    <form action="{{ route('list_pasien_rajal.index') }}" method="GET" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div>
                <label for="tanggal_kunjungan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal Kunjungan</label>
                <input type="date" id="tanggal_kunjungan" name="tanggal_kunjungan" value="{{ $tanggalKunjungan ?? date('Y-m-d') }}"
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 @error('tanggal_kunjungan') border-red-400 @enderror">
            </div>
            <div>
                <x-select_poliklinik :selected="$poliklinikId ?? ''" />
            </div>
            <div>
                <x-select_dokter name="dokter_id" id="dokter_id" :selected="$dokter ?? ''" label="Dokter" />
            </div>
            <div class="flex justify-end gap-2 mt-2 md:mt-0">
                <a href="{{ route('list_pasien_rajal.index') }}" title="Reset Filter" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
                    Reset
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-6 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Tampilkan
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 1000px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">No</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. RM</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Pasien</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Poliklinik</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Prioritas</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Urutan</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Penjamin</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                @forelse($listPasien as $idx => $row)
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">
                                {{ isset($listPasien) ? ($listPasien->currentPage() - 1) * $listPasien->perPage() + $idx + 1 : $idx + 1 }}
                            </span>
                        </td>
                        <td class="px-3 py-3 font-semibold text-blue-600">
                            {{ $row->no_mr ?? '-' }}
                        </td>
                        <td class="px-3 py-3 font-semibold text-slate-800">
                            {{ $row->nama_pasien ?? '-' }}
                            <span class="block text-[10px] text-slate-400 font-normal mt-0.5">
                                @if($row->tgl_lahir && $row->tgl_lahir != '0000-00-00')
                                    {{ $row->tgl_lahir ? \Carbon\Carbon::parse($row->tgl_lahir)->age . ' Tahun' : '-' }}
                                @else
                                    -
                                @endif
                            </span>
                        </td>
                        <td class="px-3 py-3 font-medium text-slate-700">
                            {{ $row->nama_bagian ?? '-' }}
                        </td>
                        <td>
                            @if($row->prioritas == 'Berjalan Sendiri')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                    {{ $row->prioritas ?? '-' }}
                                </span>
                            @elseif($row->prioritas == 'Dengan Kursi Roda')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-orange-100 text-orange-700">
                                    {{ $row->prioritas ?? '-' }}
                                </span>
                            @elseif ($row->prioritas == 'Dengan Brankar')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-red-100 text-red-700">
                                    {{ $row->prioritas ?? '-' }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                    -
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            {{ $row->urutan }}
                        </td>
                        <td class="px-3 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                {{ $row->nama_nasabah ?? '-' }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            @php
                                $forms = $row->emr_forms ?? [];
                            @endphp

                            @if(in_array(\App\Helpers\EmrHelper::formIdBySlug('soap'), $forms))
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Dokter Sudah Mengisi Soap
                                </span>
                            @elseif(in_array(\App\Helpers\EmrHelper::formIdBySlug('pengkajian_awal_keperawatan'), $forms) || in_array(\App\Helpers\EmrHelper::formIdBySlug('pengkajian_harian_keperawatan'), $forms))
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                    Telah Mendapatkan Pelayanan Medis
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-red-100 text-red-700 border border-red-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Belum Mendapatkan Pelayanan Medis
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">

                            @if($row->tgl_masuk > date('Y-m-d'))
                                <a target="_blank" href="{{ route('dashboard_pasien.index', $row->registrasi_detail_id) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                    </svg>
                                    Medis
                                </a>
                            @else
                                <a href="#" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-500 bg-slate-100 border border-slate-200 rounded-lg cursor-not-allowed">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                    </svg>
                                    Medis
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-sm font-medium">Tidak ada data antrean pasien untuk filter yang dipilih</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(isset($listPasien) && $listPasien instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
            {{ $listPasien->withQueryString()->links('components.pagination') }}
        </div>
    @endif
</div>
@endsection
