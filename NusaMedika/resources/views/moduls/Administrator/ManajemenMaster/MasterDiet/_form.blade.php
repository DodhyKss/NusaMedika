@php
    $isEdit = $isEdit ?? false;
    /** @var \App\Models\MasterDiet|null $diet */
    $diet = $diet ?? null;

    $val = function ($field) use ($diet) {
        return old($field, $diet ? $diet->{$field} : '');
    };
@endphp

<form action="{{ $actionUrl }}" method="POST" id="formMasterDiet">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
        {{-- Kode Diet --}}
        <div>
            <label for="kode_diet" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kode Diet <span class="text-red-500">*</span></label>
            <input type="text" id="kode_diet" name="kode_diet" value="{{ $val('kode_diet') }}" placeholder="Contoh: DIET-001"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 uppercase">
            @error('kode_diet')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Nama Diet --}}
        <div>
            <label for="nama_diet" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Diet <span class="text-red-500">*</span></label>
            <input type="text" id="nama_diet" name="nama_diet" value="{{ $val('nama_diet') }}" placeholder="Contoh: Diet Diabetes Mellitus"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('nama_diet')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Keterangan --}}
        <div class="md:col-span-2">
            <label for="keterangan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="2" placeholder="Contoh: Kontrol karbohidrat, jadwal makan teratur"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">{{ $val('keterangan') }}</textarea>
            @error('keterangan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-5 flex items-start gap-2 px-4 py-3 bg-amber-50 border border-amber-200 rounded-lg">
        <svg class="w-4 h-4 shrink-0 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.268 17c-.77 1.333.192 3 1.732 3z"></path></svg>
        <p class="text-xs text-amber-800">
            <strong>Nama diet adalah nilai yang tersimpan di EMR.</strong> Form <strong>Diet Pasien</strong> (form 43)
            memvalidasinya dengan <code class="text-[11px]">exists:master_diet,nama_diet</code> — mengubah
            <em>Nama Diet</em> membuat riwayat diet lama tidak lagi cocok dengan master. Gunakan kolom
            <strong>Keterangan</strong> untuk mengubah deskripsi tanpa memutus riwayat.
        </p>
    </div>

    <hr class="my-6 border-slate-200">

    {{-- Buttons --}}
    <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
        <a href="{{ route('admin.master_diet.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
            Batal
        </a>
        <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Diet' }}
        </button>
    </div>
</form>