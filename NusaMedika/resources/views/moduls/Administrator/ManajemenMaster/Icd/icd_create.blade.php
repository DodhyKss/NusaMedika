@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Tambah ICD</h1>
        <p class="text-sm text-slate-500 mt-1">Tambahkan data ICD / diagnosa penyakit.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.icd.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Form Header -->
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
        <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
        <div>
            <h2 class="text-base font-semibold text-slate-800">Informasi ICD</h2>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi data ICD / diagnosa penyakit.</p>
        </div>
    </div>

    <!-- Form Body -->
    <div class="p-6">
        <form action="{{ route('admin.icd.store') }}" method="POST" id="formTambahIcd">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">

                <!-- Kode Diagnosa -->
                <div>
                    <label for="kode_diagnosa" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kode Diagnosa <span class="text-red-500">*</span></label>
                    <input type="text" id="kode_diagnosa" name="kode_diagnosa" value="{{ old('kode_diagnosa') }}" maxlength="10" placeholder="Contoh: A00, E11.9"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('kode_diagnosa')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori -->
                <div>
                    <label for="kategori" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kategori</label>
                    <select id="kategori" name="kategori"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        <option value="">Pilih Kategori</option>
                        {!! \App\Helpers\SelectOption::render('kategori_icd', old('kategori')) !!}
                    </select>
                    @error('kategori')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Diagnosa -->
                <div class="md:col-span-2">
                    <label for="nama_diagnosa" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Diagnosa <span class="text-red-500">*</span></label>
                    <textarea id="nama_diagnosa" name="nama_diagnosa" rows="2" placeholder="Contoh: Kolera akibat Vibrio cholerae"
                              class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">{{ old('nama_diagnosa') }}</textarea>
                    @error('nama_diagnosa')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('admin.icd.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Simpan ICD
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
