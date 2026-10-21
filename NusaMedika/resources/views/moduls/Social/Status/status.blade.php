@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Status Saya</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola status Anda sendiri. Status bisa diberi masa berlaku.</p>
    </div>
    <a href="{{ route('status_user.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors inline-flex items-center gap-2 flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        Lihat Status User Lain
    </a>
</div>

<!-- Form Buat Status -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
    <h2 class="text-sm font-semibold text-slate-700 mb-4">Buat Status</h2>

    <form action="{{ route('status.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
            <div class="md:col-span-3">
                <label for="isi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Isi Status <span class="text-red-500">*</span></label>
                <textarea id="isi" name="isi" rows="3" maxlength="500" required
                          placeholder="Contoh: Ada rapat koordinasi di Poli paru pada 14.00-16.00 sore ini."
                          class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 @error('isi') border-red-400 @enderror">{{ old('isi') }}</textarea>
                @error('isi')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="masa_berlaku" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Masa Berlaku</label>
                <select id="masa_berlaku" name="masa_berlaku"
                        class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 @error('masa_berlaku') border-red-400 @enderror">
                    <option value="">Tanpa Batas</option>
                    @foreach (\App\Models\StatusSosial::MASA_BERLAKU as $value => $label)
                        <option value="{{ $value }}" @selected((string) old('masa_berlaku') === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('masa_berlaku')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-2.5 mt-4">
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Publikasikan Status
            </button>
        </div>
    </form>
</div>

<!-- Feed status sendiri -->
<div class="space-y-3">
    @forelse ($statuses as $status)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->nama_pegawai ?? auth()->user()->user_name ?? '?', 0, 1)) }}
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="text-sm font-semibold text-slate-800">Status Saya</p>
                    <span class="text-[11px] text-slate-400">
                        · {{ $status->input_time ? \Illuminate\Support\Carbon::parse($status->input_time)->diffForHumans() : '' }}
                    </span>
                    @if ($status->tanggal_kadaraluarsa)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-600">
                            Berlaku s/d {{ \Illuminate\Support\Carbon::parse($status->tanggal_kadaraluarsa)->format('d/m H:i') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">
                            Tanpa Batas
                        </span>
                    @endif
                </div>
                <p class="text-sm text-slate-700 mt-1.5 leading-relaxed whitespace-pre-line">{{ $status->isi }}</p>
            </div>

            <form action="{{ route('status.destroy', $status->status_sosial_id) }}" method="POST" class="flex-shrink-0"
                  data-confirm-message="Yakin ingin menghapus status ini?" data-confirm-danger="true" data-confirm-title="Hapus Status">
                @csrf
                @method('DELETE')
                <button type="submit" title="Hapus Status" class="p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600 rounded-md transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A16 16 0 0114 18H6a16 16 0 01-1.868-1.879L3 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </form>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm py-16 text-center">
            <svg class="w-11 h-11 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            <p class="text-sm text-slate-500 mt-3 font-medium">Belum ada status Anda.</p>
            <p class="text-xs text-slate-400 mt-1">Tulis kabaran pertama Anda lewat form di atas.</p>
        </div>
    @endforelse
</div>

@if ($statuses->hasPages())
    <div class="mt-5">
        {{ $statuses->links('components.pagination') }}
    </div>
@endif
@endsection
