@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Edit Bagian</h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui data bagian / unit kerja.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.bagian.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

@if (session('error'))
    <div class="mb-4 px-4 py-3 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6">
        <form action="{{ route('admin.bagian.update', $bagian->bagian_id) }}" method="POST" id="formEditBagian">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div class="md:col-span-2">
                    <label for="nama_bagian" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Bagian <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_bagian" name="nama_bagian" value="{{ old('nama_bagian', $bagian->nama_bagian) }}" oninput="this.value = this.value.toUpperCase()"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 uppercase">
                    @error('nama_bagian')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="referensi_bagian" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Referensi Bagian</label>
                    <select id="referensi_bagian" name="referensi_bagian"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Referensi --</option>
                        @foreach ($referensiBagians as $referensi)
                            <option value="{{ $referensi->referensi_bagian_id }}" {{ old('referensi_bagian', $bagian->referensi_bagian) == $referensi->referensi_bagian_id ? 'selected' : '' }}>{{ $referensi->nama_referensi_bagian }}</option>
                        @endforeach
                    </select>
                    @error('referensi_bagian')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="group_bagian" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Group Bagian</label>
                    <input type="text" id="group_bagian" name="group_bagian" value="{{ old('group_bagian', $bagian->group_bagian) }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('group_bagian')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="seri_bagian" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Seri Bagian</label>
                    <input type="text" id="seri_bagian" name="seri_bagian" value="{{ old('seri_bagian', $bagian->seri_bagian) }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('seri_bagian')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="id_satu_sehat" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">ID Satu Sehat</label>
                    <input type="text" id="id_satu_sehat" name="id_satu_sehat" value="{{ old('id_satu_sehat', $bagian->id_satu_sehat) }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('id_satu_sehat')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="id_location" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">ID Location</label>
                    <input type="number" id="id_location" name="id_location" value="{{ old('id_location', $bagian->id_location) }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('id_location')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="flag_eksekutif" value="1" {{ old('flag_eksekutif', $bagian->flag_eksekutif) ? 'checked' : '' }}
                               class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 accent-blue-600 cursor-pointer">
                        <span class="text-sm text-slate-700">Bagian Eksekutif</span>
                    </label>
                    @error('flag_eksekutif')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="reset" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
                    Reset Form
                </button>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Perubahan
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
@endsection
