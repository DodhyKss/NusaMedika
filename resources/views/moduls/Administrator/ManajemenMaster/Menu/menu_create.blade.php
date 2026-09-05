@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Tambah Menu</h1>
        <p class="text-sm text-slate-500 mt-1">Buat menu baru di dalam modul.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.menu.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6">
        <form action="{{ route('admin.menu.store') }}" method="POST" id="formTambahMenu">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div>
                    <label for="modul_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Modul <span class="text-red-500">*</span></label>
                    <select id="modul_id" name="modul_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Modul --</option>
                        @foreach ($moduls as $modul)
                            <option value="{{ $modul->modul_id }}" data-urutan="{{ ($urutanPerModul[$modul->modul_id] ?? 0) + 1 }}" {{ old('modul_id') == $modul->modul_id ? 'selected' : '' }}>{{ $modul->nama_modul }}</option>
                        @endforeach
                    </select>
                    @error('modul_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="urutan_menu" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Urutan <span class="text-red-500">*</span></label>
                    <input type="number" id="urutan_menu" name="urutan_menu" value="{{ old('urutan_menu', $nextUrutan) }}" placeholder="Contoh: 1"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('urutan_menu')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="nama_menu" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Menu <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_menu" name="nama_menu" value="{{ old('nama_menu') }}" placeholder="Contoh: Keuangan"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('nama_menu')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="reset" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
                    Reset Form
                </button>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Menu
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var select = document.getElementById('modul_id');
        var input = document.getElementById('urutan_menu');

        select.addEventListener('change', function () {
            var option = select.options[select.selectedIndex];
            if (option && option.dataset.urutan) {
                input.value = option.dataset.urutan;
            }
        });
    });
</script>
@endsection
