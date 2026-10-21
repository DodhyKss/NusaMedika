@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Daftar Pasien Rehabilitasi Medik</h1>
        <p class="text-sm text-slate-500 mt-1">Pasien yang dikonsultasikan ke Rehabilitasi Medik (dari form EMR Konsultasi).</p>
    </div>
</div>

<!-- Pilih Bagian -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-5">
    <form action="{{ route('daftar_pasien_rehabilitasi_medik.pilih_bagian') }}" method="POST" class="flex flex-col sm:flex-row sm:items-end gap-3">
        @csrf
        <div class="flex-1">
            <label for="bagian_tujuan_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Bagian Rehabilitasi Medik Aktif</label>
            <select id="bagian_tujuan_id" name="bagian_tujuan_id" required
                    class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                <option value="">-- Pilih Bagian --</option>
                @foreach ($bagianList as $b)
                    <option value="{{ $b->bagian_id }}" @selected($bagianTujuanId === (int) $b->bagian_id)>{{ $b->nama_bagian }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            Pilih Bagian
        </button>
    </form>
    @if ($bagianTujuanId)
        <p class="mt-2 text-xs text-emerald-600 font-semibold">
            Bagian aktif: {{ $bagianList->firstWhere('bagian_id', $bagianTujuanId)?->nama_bagian ?? '-' }}
        </p>
    @else
        <p class="mt-2 text-xs text-slate-400">Pilih bagian rehabilitasi medik terlebih dahulu sebelum melihat daftar pasien.</p>
    @endif
</div>

<!-- Filter Pencarian -->
<form method="GET" action="{{ route('daftar_pasien_rehabilitasi_medik.index') }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-5">
    <div class="flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        <h2 class="text-sm font-semibold text-slate-700">Filter Pencarian</h2>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal Konsultasi</label>
            <input type="date" name="tgl_daftar" value="{{ $tglDaftar }}"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Keyword</label>
            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="No. MR / Nama Pasien..."
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
        </div>
    </div>
    <div class="flex items-center gap-2 mt-4">
        <a href="{{ route('daftar_pasien_rehabilitasi_medik.index') }}" title="Reset Filter" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
            Reset
        </a>
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            Cari
        </button>
    </div>
</form>

@if($listPasien !== null)
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 900px;">
            <thead>
                <tr class="bg-slate-50/60 border-b border-slate-100">
                    <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. Rekam Medis</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Pasien</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tgl. Konsultasi</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Jenis Konsultasi</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Indikasi</th>
                    <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[13px] divide-y divide-slate-100">
                @forelse ($listPasien as $p)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-5 py-3">
                            <span class="font-semibold text-slate-800">{{ $p->no_mr }}</span>
                        </td>
                        <td class="px-3 py-3 text-slate-700">
                            {{ $p->nama_pasien }}
                            <span class="block text-[10px] text-slate-400">{{ $p->tgl_lahir ? \Carbon\Carbon::parse($p->tgl_lahir)->translatedFormat('d M Y') : '' }}</span>
                        </td>
                        <td class="px-3 py-3 text-slate-600 tabular-nums">{{ \Carbon\Carbon::parse($p->tgl_daftar ?? $p->tgl_masuk)->format('d-m-Y H:i') }}</td>
                        <td class="px-3 py-3">
                            @if ($p->jenis_konsultasi)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-100 text-teal-700">{{ $p->jenis_konsultasi }}</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-slate-600 max-w-[280px] truncate" title="{{ $p->indikasi_konsultasi ?? '' }}">{{ $p->indikasi_konsultasi ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a target="_blank" href="{{ route('dashboard_pasien.index', $p->registrasi_detail_id) }}" title="Buka EMR Pasien" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    EMR
                                </a>
                                <a href="{{ route('daftar_pasien_rehabilitasi_medik.detail', $p->registrasi_detail_id) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-slate-600 hover:bg-slate-700 rounded-lg transition-colors">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-400">
                            Tidak ada pasien rehabilitasi medik ditemukan untuk filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $listPasien->links('components.pagination') }}
</div>
@else
<div class="bg-white rounded-xl border border-slate-200 shadow-sm">
    <div class="flex flex-col items-center gap-3 py-16 text-slate-400">
        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <p class="text-sm font-medium">Gunakan filter pencarian di atas untuk menampilkan daftar pasien rehabilitasi medik.</p>
    </div>
</div>
@endif
@endsection