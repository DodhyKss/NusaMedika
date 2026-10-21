@php
    $isEdit = $isEdit ?? false;
    /** @var \App\Models\Implementasi|null $impl */
    $impl = $implementasi ?? null;

    $val = function ($field) use ($impl) {
        return old($field, $impl ? $impl->{$field} : '');
    };
@endphp

<form action="{{ $actionUrl }}" method="POST" id="formImplementasi">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
        <!-- Kode Implementasi -->
        <div>
            <label for="kode_implementasi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kode Implementasi <span class="text-red-500">*</span></label>
            <input type="text" id="kode_implementasi" name="kode_implementasi" value="{{ $val('kode_implementasi') }}" placeholder="Contoh: I-001"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 uppercase">
            @error('kode_implementasi')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Implementasi -->
        <div>
            <label for="nama_implementasi" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Implementasi <span class="text-red-500">*</span></label>
            <input type="text" id="nama_implementasi" name="nama_implementasi" value="{{ $val('nama_implementasi') }}" placeholder="Contoh: Perawatan Luka (Irigasi dan Balans)"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('nama_implementasi')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-5 flex items-start gap-2 px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg">
        <svg class="w-4 h-4 shrink-0 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-xs text-slate-500">
            Daftar ini menjadi pilihan pada form <strong>Implementasi Keperawatan</strong> di Dashboard Pasien.
        </p>
    </div>

    <hr class="my-6 border-slate-200">

    <!-- Buttons -->
    <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
        <a href="{{ route('admin.master_implementasi.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
            Batal
        </a>
        <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Implementasi' }}
        </button>
    </div>
</form>