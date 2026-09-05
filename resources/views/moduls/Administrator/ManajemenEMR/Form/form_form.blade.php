@extends('layouts.app')

@section('content')
@php
    $isEdit = isset($row);
    $pk = match ($tab) { 'objek' => 'objek_id', 'mapping' => 'objek_form_control_id', default => 'form_id' };
    $title = match ($tab) {
        'form' => 'Form EMR',
        'objek' => 'Objek EMR',
        default => 'Mapping Form dengan Objek',
    };
    $namaLabel = match ($tab) { 'form' => 'Nama Form', 'objek' => 'Nama Objek', default => null };
    $namaField = match ($tab) { 'form' => 'nama_form', 'objek' => 'nama_objek', default => null };
    $slugField = $tab === 'form' ? 'slug' : null;
    $idDashMenuField = $tab === 'form' ? 'id_dash_menu' : null;
@endphp

<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">{{ $isEdit ? 'Edit' : 'Tambah' }} {{ ucfirst($tab) }}</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola data {{ $title }} pada EMR.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.form.index', ['tab' => $tab]) }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
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
            <h2 class="text-base font-semibold text-slate-800">{{ $title }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi data {{ $tab }} untuk kebutuhan EMR.</p>
        </div>
    </div>

    <div class="p-6">
        <form action="{{ $isEdit ? route('admin.form.update', ['form' => $row->$pk]) : route('admin.form.store') }}" method="POST">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                @if ($tab === 'mapping')
                <div>
                    <label for="form_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Form <span class="text-red-500">*</span></label>
                    <select id="form_id" name="form_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none @error('form_id') border-red-400 @enderror">
                        <option value="">-- Pilih Form --</option>
                        @foreach ($forms as $item)
                            <option value="{{ $item->form_id }}" {{ old('form_id', $isEdit ? $row->form_id : '') == $item->form_id ? 'selected' : '' }}>{{ $item->nama_form }} ({{ $item->slug }})</option>
                        @endforeach
                    </select>
                    @error('form_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="objek_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Objek <span class="text-red-500">*</span></label>
                    <select id="objek_id" name="objek_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none @error('objek_id') border-red-400 @enderror">
                        <option value="">-- Pilih Objek --</option>
                        @foreach ($objeks as $item)
                            <option value="{{ $item->objek_id }}" {{ old('objek_id', $isEdit ? $row->objek_id : '') == $item->objek_id ? 'selected' : '' }}>{{ $item->nama_objek }}</option>
                        @endforeach
                    </select>
                    @error('objek_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="variabel" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Variabel <span class="text-red-500">*</span></label>
                    <input type="text" id="variabel" name="variabel" value="{{ old('variabel', $isEdit ? $row->variabel : '') }}" placeholder="nama field di blade (mis. subjective, gcs_e, td)"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 @error('variabel') border-red-400 @enderror">
                    @error('variabel')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                @else
                <div>
                    <label for="{{ $namaField }}" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">{{ $namaLabel }} <span class="text-red-500">*</span></label>
                    <input type="text" id="{{ $namaField }}" name="{{ $namaField }}" value="{{ old($namaField, $isEdit ? $row->$namaField : '') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 @error($namaField) border-red-400 @enderror">
                    @error($namaField)<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                @endif

                @if ($tab === 'form')
                <div>
                    <label for="slug" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Slug <span class="text-red-500">*</span></label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $isEdit ? $row->slug : '') }}" placeholder="soap, pengkajian_awal_keperawatan, ..."
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 @error('slug') border-red-400 @enderror">
                    <p class="mt-1 text-[11px] text-slate-400">Identitas URL /emr/form/{slug}/... dan nama folder/view EMR.</p>
                    @error('slug')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <!-- Placeholder to balance grid -->
                </div>

                @php
                    $currentIdDashMenu = old('id_dash_menu', $isEdit ? $row->id_dash_menu ?? '' : '');
                @endphp
                <div class="md:col-span-2 p-4 bg-slate-50/80 rounded-xl border border-slate-200 space-y-4">
                    <div>
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Struktur Dashboard EMR (Opsional)</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Pilih Dashboard Menu, Sub Menu, atau Sub Menu Extra agar ID Dash Menu terisi otomatis berdasarkan yang dipilih.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label for="select_menu_id" class="block text-[11px] font-semibold text-slate-600 mb-1">Dashboard Menu</label>
                            <select id="select_menu_id" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                                <option value="">-- Pilih Menu Utama --</option>
                                @foreach ($menus as $m)
                                    <option value="{{ $m->dashboard_menu_id }}">{{ $m->nama_menu }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="select_sub_id" class="block text-[11px] font-semibold text-slate-600 mb-1">Sub Menu</label>
                            <select id="select_sub_id" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none" disabled>
                                <option value="">-- Pilih Sub Menu --</option>
                            </select>
                        </div>
                        <div>
                            <label for="select_extra_id" class="block text-[11px] font-semibold text-slate-600 mb-1">Sub Menu Extra</label>
                            <select id="select_extra_id" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none" disabled>
                                <option value="">-- Pilih Sub Menu Extra --</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                        <div class="text-xs text-slate-600">
                            ID Dash Menu: <code id="id_dash_menu_badge" class="bg-blue-100 text-blue-800 font-bold px-2 py-0.5 rounded">{{ $currentIdDashMenu ?: '-' }}</code>
                        </div>
                        <input type="hidden" id="id_dash_menu" name="id_dash_menu" value="{{ $currentIdDashMenu }}">
                        <button type="button" id="btn_clear_dash" class="text-xs text-red-600 hover:text-red-800 font-semibold cursor-pointer">Kosongkan Menu</button>
                    </div>
                    @error('id_dash_menu')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Jenjang Rawatan</label>
                    <div class="flex flex-wrap gap-4">
                        @foreach (['ri' => 'Rawat Inap', 'rj' => 'Rawat Jalan', 'igd' => 'IGD', 'mcu' => 'MCU'] as $key => $label)
                            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                <input type="checkbox" name="{{ $key }}" value="1" {{ old($key, $isEdit ? $row->$key : 0) == 1 ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const subsData = @json($subs);
                    const extrasData = @json($extras);
                    const currentId = @json($currentIdDashMenu);

                    const $menu = document.getElementById('select_menu_id');
                    const $sub = document.getElementById('select_sub_id');
                    const $extra = document.getElementById('select_extra_id');
                    const $input = document.getElementById('id_dash_menu');
                    const $badge = document.getElementById('id_dash_menu_badge');
                    const $clear = document.getElementById('btn_clear_dash');

                    function updateIdDashMenu() {
                        const menuVal = $menu.value;
                        const subVal = $sub.value;
                        const extraVal = $extra.value;

                        let res = '';
                        if (menuVal) {
                            res = menuVal;
                            if (subVal) {
                                res += '.' + subVal;
                                if (extraVal) {
                                    res += '.' + extraVal;
                                }
                            }
                        }
                        $input.value = res;
                        $badge.textContent = res ? res : '-';
                    }

                    function populateSubs(menuId, selectedSubId = '') {
                        $sub.innerHTML = '<option value="">-- Pilih Sub Menu --</option>';
                        $sub.disabled = true;
                        $extra.innerHTML = '<option value="">-- Pilih Sub Menu Extra --</option>';
                        $extra.disabled = true;

                        if (!menuId) return;

                        const filteredSubs = subsData.filter(s => String(s.dashboard_menu_id) === String(menuId));
                        if (filteredSubs.length > 0) {
                            filteredSubs.forEach(s => {
                                const opt = document.createElement('option');
                                opt.value = s.dashboard_menu_sub_id;
                                opt.textContent = s.nama_sub_menu;
                                if (String(s.dashboard_menu_sub_id) === String(selectedSubId)) {
                                    opt.selected = true;
                                }
                                $sub.appendChild(opt);
                            });
                            $sub.disabled = false;
                        }
                    }

                    function populateExtras(subId, selectedExtraId = '') {
                        $extra.innerHTML = '<option value="">-- Pilih Sub Menu Extra --</option>';
                        $extra.disabled = true;

                        if (!subId) return;

                        const filteredExtras = extrasData.filter(e => String(e.dashboard_menu_sub_id) === String(subId));
                        if (filteredExtras.length > 0) {
                            filteredExtras.forEach(e => {
                                const opt = document.createElement('option');
                                opt.value = e.dashboard_menu_sub_extra_id;
                                opt.textContent = e.nama_sub_menu_extra;
                                if (String(e.dashboard_menu_sub_extra_id) === String(selectedExtraId)) {
                                    opt.selected = true;
                                }
                                $extra.appendChild(opt);
                            });
                            $extra.disabled = false;
                        }
                    }

                    if (currentId) {
                        const parts = currentId.split('.');
                        if (parts.length >= 1 && parts[0]) {
                            $menu.value = parts[0];
                            populateSubs(parts[0], parts[1] || '');
                            if (parts.length >= 2 && parts[1]) {
                                populateExtras(parts[1], parts[2] || '');
                            }
                        }
                    }
                    updateIdDashMenu();

                    $menu.addEventListener('change', function () {
                        populateSubs($menu.value);
                        updateIdDashMenu();
                    });

                    $sub.addEventListener('change', function () {
                        populateExtras($sub.value);
                        updateIdDashMenu();
                    });

                    $extra.addEventListener('change', function () {
                        updateIdDashMenu();
                    });

                    $clear.addEventListener('click', function () {
                        $menu.value = '';
                        populateSubs('');
                        updateIdDashMenu();
                    });
                });
                </script>
                @endif
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