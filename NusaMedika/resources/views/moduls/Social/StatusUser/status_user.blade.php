@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Status User</h1>
        <p class="text-sm text-slate-500 mt-1">Lihat status seluruh pengguna. Status dibuat dari menu <span class="font-medium text-slate-600">Status</span>.</p>
    </div>
    <a href="{{ route('status.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Kelola Status Saya
    </a>
</div>

<!-- Filter -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
    <form action="{{ route('status_user.index') }}" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div class="col-span-1 md:col-span-3">
                <label for="search" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Cari Pengguna</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" id="search" name="search" value="{{ old('search', $search) }}" placeholder="Cari nama / username..."
                           class="w-full text-sm border border-slate-200 rounded-lg pl-9 pr-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('status_user.index') }}" class="inline-flex items-center justify-center bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-slate-700 text-sm font-semibold py-2.5 px-4 rounded-lg shadow-sm transition-colors">
                    Reset
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 px-6 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    Cari
                </button>
            </div>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Daftar user + status terbaru -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Status Terbaru per Pengguna</h2>
                <span class="text-xs text-slate-400 font-medium">{{ $users->count() }} pengguna</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($users as $u)
                    @php
                        $status = $statusTerbaru[$u->user_id] ?? null;
                        $jumlah = (int) ($jumlahAktif[$u->user_id] ?? 0);
                        $saya = (int) $u->user_id === (int) $saya;
                    @endphp
                    <div class="px-5 py-4 flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-full {{ $status ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center text-sm font-bold flex-shrink-0">
                            {{ strtoupper(substr($u->nama_pegawai ?? $u->user_name ?? '?', 0, 1)) }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-slate-800">{{ $u->nama_pegawai ?? $u->user_name }}</p>
                                <span class="text-[11px] text-slate-400 font-medium">({{ $u->user_name }})</span>
                                @if ($saya)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-700">Anda</span>
                                @endif
                                @if ($jumlah > 1)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">{{ $jumlah }} status aktif</span>
                                @endif
                            </div>

                            @if ($status)
                                <p class="text-sm text-slate-700 mt-1.5 leading-relaxed whitespace-pre-line">{{ $status->isi }}</p>
                                <p class="text-[11px] text-slate-400 mt-1">
                                    Diposting {{ \Illuminate\Support\Carbon::parse($status->input_time)->diffForHumans() }}
                                    @if ($status->tanggal_kadaraluarsa)
                                        · berlaku sampai {{ \Illuminate\Support\Carbon::parse($status->tanggal_kadaraluarsa)->format('d/m H:i') }}
                                    @else
                                        · tanpa batas
                                    @endif
                                </p>
                            @else
                                <p class="text-sm text-slate-400 mt-1 italic">Belum ada status aktif.</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-14 text-center">
                        <p class="text-sm text-slate-500 font-medium">Pengguna tidak ditemukan.</p>
                        <p class="text-xs text-slate-400 mt-1">Coba kata kunci lain atau tekan Reset.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Riwayat status user lain -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-700">Riwayat Status User Lain</h2>
            </div>

            <div class="p-5 space-y-4 max-h-[32rem] overflow-y-auto">
                @forelse ($riwayat as $s)
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($namaPengguna[$s->user_id] ?? '?', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-700">{{ $namaPengguna[$s->user_id] ?? 'Pengguna dihapus' }}</p>
                            <p class="text-sm text-slate-600 mt-0.5 leading-relaxed">{{ $s->isi }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                {{ \Illuminate\Support\Carbon::parse($s->input_time)->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-6 italic">Belum ada status dari user lain.</p>
                @endforelse
            </div>
        </div>

        <div class="px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-xs text-slate-600 flex items-start gap-2">
            <svg class="w-4 h-4 flex-shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Halaman ini hanya untuk melihat. Status yang sudah melewati masa berlaku tidak ditampilkan.
        </div>
    </div>
</div>
@endsection
