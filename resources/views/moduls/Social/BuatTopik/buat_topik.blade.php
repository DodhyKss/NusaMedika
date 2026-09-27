@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('forum.index') }}" class="inline-flex items-center justify-center w-9 h-9 bg-white border border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50 rounded-lg transition-colors flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
    </a>
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Buat Topik Forum</h1>
        <p class="text-sm text-slate-500 mt-0.5">Mulai diskusi baru. Cakupan menentukan siapa saja yang dapat membacanya.</p>
    </div>
</div>

<form action="{{ route('buat_topik.store') }}" method="POST">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-slate-700 mb-4">Isi Topik</h2>

                <div class="space-y-4">
                    <div>
                        <label for="judul" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Judul <span class="text-red-500">*</span></label>
                        <input type="text" id="judul" name="judul" required maxlength="200" value="{{ old('judul') }}"
                               placeholder="Contoh: Standar alur monitoring untuk pasien post-partum"
                               class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 @error('judul') border-red-400 @enderror">
                        @error('judul')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="isi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Isi Diskusi <span class="text-red-500">*</span></label>
                        <textarea id="isi" name="isi" rows="8" required placeholder="Tuliskan pertanyaan atau topik yang ingin dibahas..."
                                  class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 @error('isi') border-red-400 @enderror">{{ old('isi') }}</textarea>
                        @error('isi')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-slate-700 mb-4">Cakupan Diskusi</h2>

                <div class="space-y-4">
                    <div>
                        <label for="tipe" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tipe <span class="text-red-500">*</span></label>
                        <select id="tipe" name="tipe" required
                                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 @error('tipe') border-red-400 @enderror">
                            <option value="PUBLIK" @selected(old('tipe', 'PUBLIK') === 'PUBLIK')>Semua User</option>
                            <option value="PROFESI" @selected(old('tipe') === 'PROFESI')>Profesi Tertentu</option>
                            <option value="BAGIAN" @selected(old('tipe') === 'BAGIAN')>Bagian Tertentu</option>
                        </select>
                        @error('tipe')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div id="wrap-profesi" class="hidden">
                        <label for="target_profesi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pilih Profesi</label>
                        <select id="target_profesi" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            <option value="">-- Pilih Profesi --</option>
                            @foreach ($profesi as $p)
                                <option value="{{ $p->profesi_id }}" @selected((string) old('target_id') === (string) $p->profesi_id)>{{ $p->nama_profesi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="wrap-bagian" class="hidden">
                        <label for="target_bagian" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pilih Bagian</label>
                        <select id="target_bagian" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            <option value="">-- Pilih Bagian --</option>
                            @foreach ($bagian as $b)
                                <option value="{{ $b->bagian_id }}" @selected((string) old('target_id') === (string) $b->bagian_id)>{{ $b->nama_bagian }}</option>
                            @endforeach
                        </select>
                    </div>

                    <p class="text-xs text-slate-400 leading-relaxed">
                        Hanya <strong class="font-semibold text-slate-500">satu</strong> target yang tersimpan. Pilih "Semua User" bila diskusi boleh dibaca seluruh pegawai.
                    </p>
                    @error('target_id')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5">
                <a href="{{ route('forum.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Terbitkan Topik
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    // Satu input tersembunyi (target_id) dipakai bersama: hanya dropdown yang cocok
    // dengan tipe terpilih yang enabled, agar tidak ada dua nilai terkirim.
    const tipe = document.getElementById('tipe');
    const wrapProfesi = document.getElementById('wrap-profesi');
    const wrapBagian = document.getElementById('wrap-bagian');
    const alvoProfesi = document.getElementById('target_profesi');
    const alvoBagian = document.getElementById('target_bagian');
    const target = document.createElement('input');
    target.type = 'hidden';
    target.name = 'target_id';
    target.value = '{{ old('target_id') }}';
    document.getElementById('tipe').form.appendChild(target);

    if (!tipe) { return; }

    function sync() {
        const isProfesi = tipe.value === 'PROFESI';
        const isBagian = tipe.value === 'BAGIAN';
        wrapProfesi.classList.toggle('hidden', !isProfesi);
        wrapBagian.classList.toggle('hidden', !isBagian);
        alvoProfesi.disabled = !isProfesi;
        alvoBagian.disabled = !isBagian;
        target.value = isProfesi ? alvoProfesi.value : (isBagian ? alvoBagian.value : '');
    }

    alvoProfesi.addEventListener('change', sync);
    alvoBagian.addEventListener('change', sync);
    tipe.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
@endsection
