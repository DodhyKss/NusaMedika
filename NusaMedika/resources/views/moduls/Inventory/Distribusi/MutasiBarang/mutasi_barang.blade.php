@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Mutasi Barang</h1>
        <p class="text-sm text-slate-500 mt-1">Mutasi / distribusi barang antar bagian, atau pemakaian & pengeluaran stok.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('mutasi_barang.create') }}" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Buat Mutasi
        </a>
    </div>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 1000px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">No.</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. Mutasi</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Bagian Asal</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Bagian Tujuan</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Jumlah Item</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Keterangan</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                @forelse ($mutasiList as $i => $m)
                    @php
                        $noUrut = ($mutasiList->firstItem() ?? 0) + $i;
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="px-3 py-3 text-center text-slate-500">{{ $noUrut }}</td>
                        <td class="px-3 py-3 font-semibold text-slate-800">{{ $m->no_mutasi }}</td>
                        <td class="px-3 py-3 text-slate-600 whitespace-nowrap">{{ \Carbon\Carbon::parse($m->tanggal_mutasi)->format('d-m-Y') }}</td>
                        <td class="px-3 py-3 text-slate-700">{{ $m->bagianAsal->nama_bagian ?? '-' }}</td>
                        <td class="px-3 py-3">
                            @if ($m->bagianTujuan)
                                <span class="text-slate-700">{{ $m->bagianTujuan->nama_bagian }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-100 text-violet-700">Pemakaian</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center text-slate-600">{{ $m->details->count() }}</td>
                        <td class="px-3 py-3 text-slate-500 max-w-[260px] truncate" title="{{ $m->keterangan }}">{{ $m->keterangan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                <p class="text-sm font-medium">Belum ada data mutasi barang.</p>
                                <p class="text-xs">Klik tombol "Buat Mutasi" untuk mencatat mutasi pertama.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
        {{ $mutasiList->withQueryString()->links('components.pagination') }}
    </div>
</div>
@endsection