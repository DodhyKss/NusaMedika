@extends('layouts.app')

@section('content')
@php
    $saya = (int) auth()->id();
    $namaLawan = $lawan->nama_pegawai ?? $lawan->user_name ?? 'Pengguna dihapus';
@endphp

<!-- Page Header -->
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('pesan.index') }}" class="inline-flex items-center justify-center w-9 h-9 bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
    </a>
    <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold flex-shrink-0">
        {{ strtoupper(substr($namaLawan, 0, 1)) }}
    </div>
    <div class="min-w-0">
        <h1 class="text-[20px] font-bold text-slate-900 tracking-tight truncate">{{ $namaLawan }}</h1>
        @if ($lawan->nama_pegawai ?? null)
            <p class="text-xs text-slate-400">{{ $lawan->user_name }}</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Ruang obrolan -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col" style="min-height: 60vh;">
            <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-semibold text-slate-700">Riwayat Pesan</h2>
            </div>

            <div id="pesanScroll" class="flex-1 p-5 space-y-3 overflow-y-auto" style="max-height: 55vh;">
                @forelse ($pesan as $p)
                    @php
                        $sayaKirim = (int) $p->pengirim_id === $saya;
                    @endphp
                    <div class="flex {{ $sayaKirim ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[75%]">
                            <div class="px-3.5 py-2.5 rounded-2xl text-sm leading-relaxed
                                {{ $sayaKirim
                                    ? 'bg-blue-600 text-white rounded-br-md'
                                    : 'bg-slate-100 text-slate-800 rounded-bl-md' }}">
                                {{ $p->isi }}
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400 {{ $sayaKirim ? 'text-right' : '' }}">
                                {{ $p->input_time ? \Illuminate\Support\Carbon::parse($p->input_time)->format('d/m/Y H:i') : '' }}
                                @if ($sayaKirim)
                                    · {{ $p->dibaca_at ? 'Dibaca' : 'Terkirim' }}
                                @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="py-14 text-center">
                        <p class="text-sm text-slate-400 font-medium">Belum ada pesan dalam percakapan ini.</p>
                    </div>
                @endforelse
            </div>

            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                <form action="{{ route('pesan.kirim', $percakapan->percakapan_id) }}" method="POST" class="flex items-end gap-2.5">
                    @csrf
                    <div class="flex-1">
                        <label for="isi" class="sr-only">Isi Pesan</label>
                        <textarea id="isi" name="isi" rows="2" required maxlength="1000" placeholder="Tulis pesan Anda..."
                                  class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 @error('isi') border-red-400 @enderror">{{ old('isi') }}</textarea>
                        @error('isi')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all flex items-center gap-1.5 flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Kirim
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Info Maca -->
    <div class="px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-start gap-2 h-fit">
        <svg class="w-4 h-4 flex-shrink-0 mt-px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Membuka percakapan ini menandai pesan masuk sebagai sudah dibaca.
    </div>
</div>

@push('scripts')
<script>
(function () {
    const scroll = document.getElementById('pesanScroll');
    if (scroll) {
        scroll.scrollTop = scroll.scrollHeight;
    }
})();
</script>
@endpush
@endsection
