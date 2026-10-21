@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Notifikasi</h1>
        <p class="text-sm text-slate-500 mt-1">Kirim notifikasi ke user. Notifikasi muncul di lonceng navbar sampai penerima menutupnya.</p>
    </div>
    <a href="{{ route('admin.notifikasi.create') }}" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Kirim Notifikasi
    </a>
</div>

<!-- Filter -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
    <form action="{{ route('admin.notifikasi.index') }}" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="col-span-1 md:col-span-3">
                <label for="search" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="search" name="search" value="{{ old('search', $search) }}" placeholder="Cari judul / isi notifikasi..."
                           class="w-full text-sm border border-slate-200 rounded-lg pl-9 pr-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('admin.notifikasi.index') }}" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
                    Reset
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-6 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    Cari Data
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Riwayat -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 900px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">No.</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Judul</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Target</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Prioritas</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Penerima</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pengirim</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                @forelse ($notifikasis as $i => $n)
                    @php
                        $noUrut = ($notifikasis->firstItem() ?? 0) + $i;
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="px-3 py-3 text-center text-slate-500">{{ $noUrut }}</td>
                        <td class="px-3 py-3">
                            <p class="font-semibold text-slate-800">{{ $n->judul }}</p>
                            <p class="text-slate-500 mt-0.5 line-clamp-2">{{ $n->pesan }}</p>
                            <p class="text-[11px] text-slate-400 mt-1">
                                {{ $n->input_time ? \Illuminate\Support\Carbon::parse($n->input_time)->format('d/m/Y H:i') : '-' }}
                            </p>
                        </td>
                        <td class="px-3 py-3 text-slate-600">{{ $n->targetLabel() }}</td>
                        <td class="px-3 py-3 text-center">
                            @php
                                $prioritasClass = match ($n->prioritas) {
                                    'URGENT' => 'bg-red-100 text-red-600',
                                    'PENTING' => 'bg-amber-100 text-amber-600',
                                    default => 'bg-slate-100 text-slate-600',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $prioritasClass }}">{{ $n->prioritas }}</span>
                        </td>
                        <td class="px-3 py-3 text-center font-semibold text-slate-700">{{ $n->penerima_count }}</td>
                        <td class="px-3 py-3 text-slate-600">{{ $n->pengirim->nama_pegawai ?? $n->pengirim->user_name ?? '-' }}</td>
                        <td class="px-3 py-3 text-center">
                            <form action="{{ route('admin.notifikasi.destroy', $n->notifikasi_id) }}" method="POST" data-confirm-message="Yakin ingin menghapus notifikasi ini? Notifikasi akan hilang dari lonceng semua penerima." data-confirm-danger="true" data-confirm-title="Hapus Notifikasi" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cursor-pointer p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600 rounded-md transition-colors" title="Hapus Data">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A16 16 0 0114 18H6a16 16 0 01-1.868-1.879L3 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-12 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                <p class="text-sm font-medium">Belum ada notifikasi yang dikirim.</p>
                                <p class="text-xs">Klik tombol "Kirim Notifikasi" untuk mengirim yang pertama.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
        {{ $notifikasis->withQueryString()->links('components.pagination') }}
    </div>
</div>
@endsection
