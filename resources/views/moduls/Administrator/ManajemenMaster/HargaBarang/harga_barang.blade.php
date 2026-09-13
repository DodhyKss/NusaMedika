@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Harga Barang</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola harga beli & jual per no. batch untuk tiap barang.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.harga_barang.create') }}" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Harga
        </a>
    </div>
</div>

@if (session('success'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Filter -->
    <div class="px-4 py-3 border-b border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row gap-3">
        <form method="GET" action="{{ route('admin.harga_barang.index') }}" class="flex flex-col sm:flex-row gap-3 flex-1">
            <select name="barang_id" class="w-full sm:w-72 text-sm border border-slate-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700">
                <option value="">Semua Barang</option>
                @foreach ($barangList as $b)
                    <option value="{{ $b->barang_id }}" @selected((string) $barangId === (string) $b->barang_id)>
                        {{ $b->kode_barang }} — {{ $b->nama_barang }}
                    </option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode / nama barang…"
                   class="flex-1 text-sm border border-slate-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-700 placeholder-slate-400">
            <button type="submit" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 transition-colors">
                Filter
            </button>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center w-10">No</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">Kode</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">Nama Barang</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">Satuan</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-left">No. Batch</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Harga Beli</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Harga Jual</th>
                    <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($hargaList as $i => $h)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-3 py-3 text-center text-slate-400">{{ $hargaList->firstItem() + $i }}</td>
                        <td class="px-3 py-3 font-medium text-slate-500">{{ $h->barang->kode_barang }}</td>
                        <td class="px-3 py-3 font-semibold text-slate-800">{{ $h->barang->nama_barang }}</td>
                        <td class="px-3 py-3 text-slate-500">{{ $h->barang->satuan->nama_satuan ?? '-' }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-xs font-semibold">{{ $h->no_batch }}</span>
                        </td>
                        <td class="px-3 py-3 text-right text-slate-700 tabular-nums">{{ number_format((float) $h->harga_beli, 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right text-slate-700 tabular-nums">{{ $h->harga_jual !== null ? number_format((float) $h->harga_jual, 0, ',', '.') : '-' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.harga_barang.edit', $h->harga_id) }}"
                                   class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Ubah">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('admin.harga_barang.destroy', $h->harga_id) }}" method="POST"
                                      onsubmit="return confirm('Hapus harga barang ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11 4 0 2 2 0 01-4 0z"></path></svg>
                                <p class="text-sm">Belum ada data harga barang.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($hargaList->hasPages())
        <div class="px-4 py-3 border-t border-slate-200">
            {{ $hargaList->links() }}
        </div>
    @endif
</div>
@endsection