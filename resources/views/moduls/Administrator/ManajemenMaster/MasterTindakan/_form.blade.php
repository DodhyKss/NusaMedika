@php
    $isEdit = $isEdit ?? false;
    /** @var \App\Models\Tindakan|null $t */
    $t = $tindakan ?? null;

    $val = function ($field) use ($t) {
        return old($field, $t ? $t->{$field} : '');
    };
@endphp

<form action="{{ $actionUrl }}" method="POST" id="formTindakan">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
        <!-- Kode Tindakan -->
        <div>
            <label for="kode_tindakan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kode Tindakan <span class="text-red-500">*</span></label>
            <input type="text" id="kode_tindakan" name="kode_tindakan" value="{{ $val('kode_tindakan') }}" placeholder="Contoh: LAB-0001 / RAD-0001"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('kode_tindakan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Nama Tindakan -->
        <div>
            <label for="nama_tindakan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Tindakan <span class="text-red-500">*</span></label>
            <input type="text" id="nama_tindakan" name="nama_tindakan" value="{{ $val('nama_tindakan') }}" placeholder="Contoh: Darah Rutin, Rontgen Thorax"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('nama_tindakan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Kategori -->
        <div>
            <label for="kategori_tindakan_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kategori <span class="text-red-500">*</span></label>
            <select id="kategori_tindakan_id" name="kategori_tindakan_id" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($kategoriList as $k)
                    <option value="{{ $k->kategori_tindakan_id }}" @selected((int) old('kategori_tindakan_id', $t ? $t->kategori_tindakan_id : '') === (int) $k->kategori_tindakan_id)>{{ $k->nama_kategori_tindakan }}</option>
                @endforeach
            </select>
            @error('kategori_tindakan_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="mt-5 flex items-start gap-2 px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg">
        <svg class="w-4 h-4 shrink-0 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-xs text-slate-500">
            Unit penunjang, satuan &amp; nilai normal hasil diatur saat order, dan
            <strong>tarif setiap tindakan diatur di menu Master Tarif</strong>.
        </p>
    </div>

    <hr class="my-6 border-slate-200">

    <!-- Buttons -->
    <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
        <a href="{{ route('admin.master_tindakan.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
            Batal
        </a>
        <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Tindakan' }}
        </button>
    </div>
</form>
