@extends('layouts.app')

@section('content')
@php
    $isEdit = isset($row);
    $pk = match ($tab) { 'sub' => 'dashboard_menu_sub_id', 'extra' => 'dashboard_menu_sub_extra_id', default => 'dashboard_menu_id' };
    $namaField = match ($tab) { 'sub' => 'nama_sub_menu', 'extra' => 'nama_sub_menu_extra', default => 'nama_menu' };
    $namaLabel = match ($tab) { 'sub' => 'Nama Sub Menu', 'extra' => 'Nama Sub Menu Extra', default => 'Nama Menu' };
    $parentField = match ($tab) { 'sub' => 'dashboard_menu_id', 'extra' => 'dashboard_menu_sub_id', default => null };
    $parentLabel = match ($tab) { 'sub' => 'Menu', 'extra' => 'Sub Menu', default => null };
    $parentList = match ($tab) { 'sub' => $menus, 'extra' => $subs, default => collect() };
    $parentNamaField = match ($tab) { 'sub' => 'nama_menu', 'extra' => 'nama_sub_menu', default => null };
@endphp

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">{{ $isEdit ? 'Edit' : 'Tambah' }} {{ ucfirst($tab) }}</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola data {{ $tab }} pada struktur dashboard pasien EMR.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.dashboard_menu.index', ['tab' => $tab]) }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="mb-4 px-4 py-3 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
        <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </div>
        <div>
            <h2 class="text-base font-semibold text-slate-800">Informasi Dashboard Pasien</h2>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi data {{ $tab }} untuk struktur menu EMR.</p>
        </div>
    </div>

    <div class="p-6">
        <form action="{{ $isEdit ? route('admin.dashboard_menu.update', ['dashboard_menu' => $row->$pk]) : route('admin.dashboard_menu.store') }}" method="POST">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                @if ($parentField)
                <div>
                    <label for="{{ $parentField }}" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">{{ $parentLabel }} <span class="text-red-500">*</span></label>
                    <select id="{{ $parentField }}" name="{{ $parentField }}"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none @error($parentField) border-red-400 @enderror">
                        <option value="">-- Pilih {{ $parentLabel }} --</option>
                        @foreach ($parentList as $item)
                            <option value="{{ $item->{$parentField} }}" {{ old($parentField, $isEdit ? $row->$parentField : '') == $item->{$parentField} ? 'selected' : '' }}>{{ $item->{$parentNamaField} }}</option>
                        @endforeach
                    </select>
                    @error($parentField)<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                @endif

                <div>
                    <label for="{{ $namaField }}" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">{{ $namaLabel }} <span class="text-red-500">*</span></label>
                    <input type="text" id="{{ $namaField }}" name="{{ $namaField }}" value="{{ old($namaField, $isEdit ? $row->$namaField : '') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 @error($namaField) border-red-400 @enderror">
                    @error($namaField)<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="reset" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
                    Reset Form
                </button>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan
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