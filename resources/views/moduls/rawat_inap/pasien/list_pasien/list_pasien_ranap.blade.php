@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">List Pasien Rawat Inap</h1>
        <p class="text-sm text-slate-500 mt-1">Daftar pasien yang sedang dalam perawatan (Rawat Inap).</p>
    </div>
</div>

<!-- Filter Card -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        <h2 class="text-sm font-semibold text-slate-700">Filter Pencarian</h2>
    </div>
    <form action="{{ route('list_pasien_ranap.index') }}" method="GET" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
            {{-- Hidden input for filter --}}
            <input type="hidden" name="filter" value="true">
            <!-- Filter Ruangan -->
            <x-select-ruang-perawatan :selected="$ruanganId ?? ''" />

            <!-- Action Buttons -->
            <div class="flex justify-end gap-2 mt-2 md:mt-0">
                <a href="#" title="Reset Filter" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
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
        <table class="w-full text-left" style="min-width: 1100px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Kamar / Bed</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. RM</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Pasien</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ruangan</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Penjamin</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">LOS / Kapasitas</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Status</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                @forelse($listPasien as $row)
                    @php
                        $tglMasuk = !empty($row->tgl_masuk) ? \Carbon\Carbon::parse($row->tgl_masuk) : null;
                        $jk = ($row->jenis_kelamin == 'L' || $row->jenis_kelamin == 1) ? 'Laki-laki' : (($row->jenis_kelamin == 'P' || $row->jenis_kelamin == 2) ? 'Perempuan' : $row->jenis_kelamin);
                        $umur = !empty($row->tgl_lahir) ? \Carbon\Carbon::parse($row->tgl_lahir)->age . ' Tahun' : '';
                        $infoPasien = trim($jk . ($jk && $umur ? ', ' : '') . $umur);
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">
                                {{ $row->no_kamar ?? '-' }} / {{ $row->nama_bed ?? '-' }}
                            </span>
                            @if(!empty($row->namakelas))
                                <span class="block text-[10px] text-slate-400 mt-1">{{ $row->namakelas }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 font-semibold text-blue-600">
                            {{ $row->no_mr ?? '-' }}
                        </td>
                        <td class="px-3 py-3 font-semibold text-slate-800">
                            {{ $row->nama_pasien ?? '-' }}
                            @if(!empty($infoPasien))
                                <span class="block text-[10px] text-slate-400 font-normal mt-0.5">{{ $infoPasien }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 font-medium text-slate-700">
                            {{ $row->nama_bagian ?? '-' }}
                        </td>
                        <td class="px-3 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">
                                {{ $row->nama_nasabah ?? '-' }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            @php
                                $losColor = 'bg-blue-50 text-blue-700 border-blue-200';
                                if (($row->kapasitas ?? '') === 'LOS <=3') {
                                    $losColor = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                } elseif (($row->kapasitas ?? '') === 'LOS >3 & <=5' || ($row->kapasitas ?? '') === 'LOS >5 & <=7') {
                                    $losColor = 'bg-amber-50 text-amber-700 border-amber-200';
                                } elseif (($row->kapasitas ?? '') === 'LOS >7 & <=10' || ($row->kapasitas ?? '') === 'LOS >10') {
                                    $losColor = 'bg-rose-50 text-rose-700 border-rose-200';
                                }
                                $losText = '-';
                                if (!empty($row->los)) {
                                    $find = ["days", "mons", "years", "day", "mon", "year"];
                                    $replace = ["Hari", "Bulan", "Tahun", "Hari", "Bulan", "Tahun"];
                                    $losText = trim(str_replace($find, $replace, $row->los));
                                }
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold border {{ $losColor }}">
                                {{ $row->kapasitas ?? '-' }}
                            </span>
                            <span class="block text-[11px] font-semibold text-slate-600 mt-1">
                                {{ $losText }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if(($row->status_perawatan ?? 'DPJP') === 'DPJP')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-200 text-amber-900 border border-amber-300 shadow-sm">
                                    DPJP
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-200 text-emerald-900 border border-emerald-300 shadow-sm">
                                    {{ $row->status_perawatan }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if(!empty($row->registrasi_detail_id) && Route::has('dashboard_pasien.index'))
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
                                <p class="text-sm font-medium text-slate-500">Belum Ada Data Pasien Rawat Inap</p>
                                <p class="text-xs text-slate-400">Silakan pilih filter ruangan dan klik tombol "Tampilkan"</p>
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
