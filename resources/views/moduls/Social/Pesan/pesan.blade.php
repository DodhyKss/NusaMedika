@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Pesan</h1>
    <p class="text-sm text-slate-500 mt-1">Kirim pesan pribadi antar pengguna.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Daftar percakapan -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-700">Percakapan</h2>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($percakapans as $percakapan)
                    @php
                        $lawan = $percakapan->lawan((int) auth()->id());
                        $jumlah = (int) ($belumDibaca[$percakapan->percakapan_id] ?? 0);
                    @endphp
                    <a href="{{ route('pesan.show', $percakapan->percakapan_id) }}"
                       class="flex items-center gap-3.5 px-5 py-4 hover:bg-slate-50 transition-colors">
                        <div class="w-10 h-10 rounded-full {{ $jumlah ? 'bg-blue-600' : 'bg-slate-200' }} {{ $jumlah ? 'text-white' : 'text-slate-600' }} flex items-center justify-center text-sm font-bold flex-shrink-0">
                            {{ strtoupper(substr($lawan->nama_pegawai ?? $lawan->user_name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $lawan->nama_pegawai ?? $lawan->user_name ?? 'Pengguna dihapus' }}</p>
                                @if ($jumlah)
                                    <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1.5 rounded-full bg-red-500 text-white text-[10px] font-bold">{{ $jumlah }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5 truncate">
                                {{ $percakapan->pesan_terakhir ?? 'Belum ada pesan' }}
                            </p>
                        </div>
                        <span class="text-[11px] text-slate-400 flex-shrink-0">
                            {{ $percakapan->pesan_terakhir_at ? \Illuminate\Support\Carbon::parse($percakapan->pesan_terakhir_at)->format('d/m H:i') : '' }}
                        </span>
                    </a>
                @empty
                    <div class="py-14 text-center">
                        <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10.5h8M8 14h5m-4 5.5L19 18V7a2 2 0 00-2-2H7a2 2 0 00-2 2v11a2 2 0 002 2h1"></path></svg>
                        <p class="text-sm text-slate-500 mt-3 font-medium">Belum ada percakapan.</p>
                        <p class="text-xs text-slate-400 mt-1">Mulai percakapan baru lewat form di samping.</p>
                    </div>
                @endforelse
            </div>

            @if ($percakapans->hasPages())
                <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
                    {{ $percakapans->links('components.pagination') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Mulai percakapan baru -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Mulai Percakapan</h2>

            <form action="{{ route('pesan.store') }}" method="POST">
                @csrf

                <label for="user_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pilih Pengguna <span class="text-red-500">*</span></label>
                <select id="user_id" name="user_id" required
                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 @error('user_id') border-red-400 @enderror">
                    <option value="">-- Pilih Pengguna --</option>
                    @foreach ($teman as $u)
                        <option value="{{ $u->user_id }}" @selected((string) old('user_id') === (string) $u->user_id)>{{ $u->nama_pegawai }} ({{ $u->user_name }})</option>
                    @endforeach
                </select>
                @error('user_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror

                <button type="submit" class="mt-4 w-full px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Mulai Percakapan
                </button>
            </form>
        </div>

        <div class="px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-start gap-2">
            <svg class="w-4 h-4 flex-shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Percakapan bersifat pribadi antar pengguna. Jangan menuliskan data medis pasien di sini.
        </div>
    </div>
</div>
@endsection
