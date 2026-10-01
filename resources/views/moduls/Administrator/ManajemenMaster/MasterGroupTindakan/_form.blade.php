@php
    $isEdit = $isEdit ?? false;
    $selectedTindakanIds = array_map('strval', $selectedTindakanIds ?? []);
@endphp

<form action="{{ $actionUrl }}" method="POST" id="formGroupTindakan">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
        <!-- Nama Group -->
        <div>
            <label for="nama_group_tindakan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Group <span class="text-red-500">*</span></label>
            <input type="text" id="nama_group_tindakan" name="nama_group_tindakan" value="{{ old('nama_group_tindakan', $group->nama_group_tindakan ?? '') }}" placeholder="Contoh: Darah Lengkap, Rontgen Thorax"
                   class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
            @error('nama_group_tindakan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <!-- Unit Penunjang -->
        <div>
            <label for="bagian_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Unit Penunjang <span class="text-red-500">*</span></label>
            <select id="bagian_id" name="bagian_id" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                <option value="">-- Pilih Unit --</option>
                @foreach ($bagianList as $b)
                    <option value="{{ $b->bagian_id }}" @selected((int) old('bagian_id', $group->bagian_id ?? '') === (int) $b->bagian_id)>{{ $b->nama_bagian }}</option>
                @endforeach
            </select>
            @error('bagian_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Tindakan dalam group -->
    <div class="mt-5">
        <label for="tindakan_ids" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tindakan dalam Group <span class="text-red-500">*</span></label>
        <select id="tindakan_ids" name="tindakan_ids[]" multiple class="select2 w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
            @foreach ($tindakanList as $t)
                <option value="{{ $t->tindakan_id }}" @selected(in_array((string) $t->tindakan_id, old('tindakan_ids', $selectedTindakanIds), true))>
                    {{ $t->nama_tindakan }} ({{ $t->kode_tindakan }})@if ($t->kategori) — {{ $t->kategori->nama_kategori_tindakan }}@endif
                </option>
            @endforeach
        </select>
        <p class="mt-1.5 text-xs text-slate-400">Tahan tombol Ctrl/Cmd untuk memilih beberapa tindakan sekaligus.</p>
        @error('tindakan_ids')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
        @error('tindakan_ids.*')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-5 flex items-start gap-2 px-4 py-3 bg-slate-50 border border-slate-200 rounded-lg">
        <svg class="w-4 h-4 shrink-0 text-slate-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <p class="text-xs text-slate-500">
            Group Tindakan menentukan tindakan mana yang boleh dipilih saat order pada unit tujuan yang sama.
        </p>
    </div>

    <hr class="my-6 border-slate-200">

    <!-- Buttons -->
    <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
        <a href="{{ route('admin.master_group_tindakan.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
            Batal
        </a>
        <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Group' }}
        </button>
    </div>
</form>
