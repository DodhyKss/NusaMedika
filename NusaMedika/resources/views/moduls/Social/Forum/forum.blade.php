@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Forum</h1>
    <p class="text-sm text-slate-500 mt-1">Diskusi antar sesama tenaga kesehatan rumah sakit.</p>
</div>

<!-- Filter -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
    <form action="{{ route('forum.index') }}" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div class="col-span-1 md:col-span-3">
                <label for="search" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pencarian</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="search" name="search" value="{{ old('search', $search) }}" placeholder="Cari judul / isi topik..."
                           class="w-full text-sm border border-slate-200 rounded-lg pl-9 pr-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>
            </div>

            <div>
                <label for="tipe" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Cakupan</label>
                <select id="tipe" name="tipe" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                    <option value="">Semua</option>
                    <option value="PUBLIK" @selected($filterTipe === 'PUBLIK')>Semua User</option>
                    <option value="PROFESI" @selected($filterTipe === 'PROFESI')>Profesi Saya</option>
                    <option value="BAGIAN" @selected($filterTipe === 'BAGIAN')>Bagian Saya</option>
                </select>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('forum.index') }}" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
                    Reset
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-6 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    Cari
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Daftar topik -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="divide-y divide-slate-100">
        @forelse ($topiks as $topik)
            @php
                $tipeClass = match ($topik->tipe) {
                    'PROFESI' => 'bg-indigo-100 text-indigo-600',
                    'BAGIAN' => 'bg-emerald-100 text-emerald-600',
                    default => 'bg-blue-100 text-blue-600',
                };
                $saya = (int) $topik->user_id === (int) auth()->id();
            @endphp
            <div class="px-5 py-4 hover:bg-slate-50/60 transition-colors">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold flex-shrink-0">
                        {{ strtoupper(substr($topik->pembuat->nama_pegawai ?? $topik->pembuat->user_name ?? '?', 0, 1)) }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('forum.show', $topik->topik_id) }}" class="text-sm font-semibold text-slate-800 hover:text-blue-600 transition-colors">
                                {{ $topik->judul }}
                            </a>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $tipeClass }}">{{ $topik->targetLabel() }}</span>
                        </div>

                        <p class="text-sm text-slate-600 mt-1 line-clamp-2">{{ $topik->isi }}</p>

                        <div class="flex items-center gap-3 mt-2 text-[11px] text-slate-400 flex-wrap">
                            <span>{{ $topik->pembuat->nama_pegawai ?? $topik->pembuat->user_name ?? 'Pengguna dihapus' }}</span>
                            <span>· {{ \Illuminate\Support\Carbon::parse($topik->input_time)->diffForHumans() }}</span>
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10.5h8M8 14h5m-4 5.5L19 18V7a2 2 0 00-2-2H7a2 2 0 00-2 2v11a2 2 0 002 2h1"></path></svg>
                                {{ $topik->balasan_count }} balasan
                            </span>
                        </div>
                    </div>

                    @if ($saya)
                        <form action="{{ route('forum.destroy', $topik->topik_id) }}" method="POST" class="flex-shrink-0"
                              data-confirm-message="Yakin ingin menghapus topik ini? Seluruh balasan di dalamnya ikut terhapus." data-confirm-danger="true" data-confirm-title="Hapus Topik">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Hapus Topik" class="p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600 rounded-md transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A16 16 0 0114 18H6a16 16 0 01-1.868-1.879L3 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-16 text-center">
                <svg class="w-11 h-11 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p class="text-sm text-slate-500 mt-3 font-medium">Belum ada topik.</p>
                <p class="text-xs text-slate-400 mt-1">Topik baru dibuat dari menu "Buat Topik".</p>
            </div>
        @endforelse
    </div>

    @if ($topiks->hasPages())
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
            {{ $topiks->links('components.pagination') }}
        </div>
    @endif
</div>

<div class="mt-6 px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-xs text-slate-600 flex items-start gap-2">
    <svg class="w-4 h-4 flex-shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    Anda hanya melihat topik publik, topik untuk profesi Anda, dan topik untuk bagian Anda. Topik hanya bisa dihapus oleh pembuatnya.
</div>
@endsection
