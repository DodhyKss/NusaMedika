@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Daftar Nasabah Pasien</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola data jaminan kesehatan dan asuransi pasien.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('nasabah_pasien.create') }}" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Nasabah
        </a>
    </div>
</div>

<!-- Filter Card -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        <h2 class="text-sm font-semibold text-slate-700">Filter Pencarian</h2>
    </div>
    <form action="{{ route('nasabah_pasien.index') }}" method="GET" id="filterForm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Pencarian -->
            <div class="col-span-1 md:col-span-3">
                <label for="search" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="search" name="search" value="{{ old('search', $search) }}" placeholder="Cari Nama / No. RM / No. Kartu..." 
                           class="w-full text-sm border border-slate-200 rounded-lg pl-9 pr-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-2 mt-2 md:mt-0">
                <a href="{{ route('nasabah_pasien.index') }}" title="Reset Filter" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
                    Reset
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-6 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Cari Data
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
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">No.</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. RM</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama Pasien</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nasabah</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">No. Kartu / BPJS</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Hak Kelas</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                @forelse ($nasabahPasiens as $i => $pn)
                    @php
                        $noUrut = ($nasabahPasiens->firstItem() ?? 0) + $i;
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="px-3 py-3 text-center text-slate-500">{{ $noUrut }}</td>
                        <td class="px-3 py-3 font-semibold text-blue-600">{{ $pn->pasien?->no_mr ?? '-' }}</td>
                        <td class="px-3 py-3 font-semibold text-slate-800">{{ $pn->pasien?->nama_pasien ?? '-' }}</td>
                        <td class="px-3 py-3 text-slate-600">{{ $pn->nasabah?->nama_nasabah ?? 'Umum / Mandiri' }}</td>
                        <td class="px-3 py-3 text-slate-600 font-mono text-[11px]">
                            {{ $pn->no_peserta ?? '-' }}
                        </td>
                        <td class="px-3 py-3 text-slate-600">
                            {{ $kelasMap[$pn->hak_kelas_id] ?? ($pn->hak_kelas_id ?: '-') }}
                        </td>
                        <td class="px-3 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('nasabah_pasien.edit', $pn->pasien_nasabah_id) }}" class="cursor-pointer p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-600 rounded-md transition-colors" title="Edit Data">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('nasabah_pasien.destroy', $pn->pasien_nasabah_id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data nasabah pasien ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="cursor-pointer p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600 rounded-md transition-colors" title="Hapus Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <p class="text-sm font-medium">Belum ada data nasabah pasien.</p>
                                <p class="text-xs">Klik tombol "Tambah Nasabah" untuk menambahkan data pertama.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
        {{ $nasabahPasiens->withQueryString()->links('components.pagination') }}
    </div>
</div>
@endsection
