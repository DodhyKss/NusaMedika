@extends('layouts.app')

@section('content')
@php
    $saya = (int) auth()->id();
    $tipeClass = match ($topik->tipe) {
        'PROFESI' => 'bg-indigo-100 text-indigo-600',
        'BAGIAN' => 'bg-emerald-100 text-emerald-600',
        default => 'bg-blue-100 text-blue-600',
    };
@endphp

<!-- Page Header -->
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('forum.index') }}" class="inline-flex items-center justify-center w-9 h-9 bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded-lg transition-colors flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
    </a>
    <div class="min-w-0">
        <h1 class="text-[20px] font-bold text-slate-900 tracking-tight">{{ $topik->judul }}</h1>
        <div class="flex items-center gap-2 mt-1 flex-wrap">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $tipeClass }}">{{ $topik->targetLabel() }}</span>
            <span class="text-[11px] text-slate-400">
                oleh {{ $topik->pembuat->nama_pegawai ?? $topik->pembuat->user_name ?? 'Pengguna dihapus' }}
                · {{ \Illuminate\Support\Carbon::parse($topik->input_time)->diffForHumans() }}
            </span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <!-- Isi topik -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $topik->isi }}</p>

            @if ((int) $topik->user_id === $saya)
                <div class="flex items-center justify-end gap-2.5 mt-5 pt-4 border-t border-slate-100">
                    <form action="{{ route('forum.destroy', $topik->topik_id) }}" method="POST"
                          data-confirm-message="Yakin ingin menghapus topik ini? Seluruh balasan di dalamnya ikut terhapus." data-confirm-danger="true" data-confirm-title="Hapus Topik">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                            Hapus Topik
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Balasan -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Balasan</h2>
                <span class="text-xs text-slate-400 font-medium">{{ $balasan->count() }} balasan</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($balasan as $b)
                    <div class="px-5 py-4 flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($b->penulis->nama_pegawai ?? $b->penulis->user_name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-slate-800">{{ $b->penulis->nama_pegawai ?? $b->penulis->user_name ?? 'Pengguna dihapus' }}</p>
                                @if ((int) $b->user_id === $saya)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-700">Anda</span>
                                @endif
                                <span class="text-[11px] text-slate-400">
                                    · {{ \Illuminate\Support\Carbon::parse($b->input_time)->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-700 mt-1.5 leading-relaxed whitespace-pre-line">{{ $b->isi }}</p>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <p class="text-sm text-slate-400 font-medium">Belum ada balasan.</p>
                        <p class="text-xs text-slate-400 mt-1">Jadilah yang pertama menjawab.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Form balasan -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-slate-700 mb-4">Tulis Balasan</h2>

            <form action="{{ route('forum.balas', $topik->topik_id) }}" method="POST">
                @csrf
                <label for="isi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Balasan <span class="text-red-500">*</span></label>
                <textarea id="isi" name="isi" rows="5" required maxlength="2000" placeholder="Tulis balasan Anda..."
                          class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 @error('isi') border-red-400 @enderror">{{ old('isi') }}</textarea>
                @error('isi')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror

                <button type="submit" class="mt-4 w-full px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    Kirim Balasan
                </button>
            </form>
        </div>

        <div class="px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-xs text-slate-600 flex items-start gap-2">
            <svg class="w-4 h-4 flex-shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Diskusi antar tenaga kesehatan profesional. Jangan menuliskan identitas pasien.
        </div>
    </div>
</div>
@endsection
