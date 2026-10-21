@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Tambah Modul</h1>
        <p class="text-sm text-slate-500 mt-1">Buat modul baru untuk ditampilkan di sidebar.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.modul.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6">
        <form action="{{ route('admin.modul.store') }}" method="POST" id="formTambahModul">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div>
                    <label for="nama_modul" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Modul <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_modul" name="nama_modul" value="{{ old('nama_modul') }}" placeholder="Contoh: Keuangan"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('nama_modul')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="urutan_modul" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Urutan <span class="text-red-500">*</span></label>
                    <input type="number" id="urutan_modul" name="urutan_modul" value="{{ old('urutan_modul', $nextUrutan) }}" placeholder="Contoh: 6"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('urutan_modul')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Icon (Font Awesome)</label>
                    <input type="hidden" name="icon_modul" id="icon_modul" value="{{ old('icon_modul', 'fa-solid fa-square') }}">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-xs font-medium text-slate-500">Pratinjau:</span>
                        <span id="preview_icon" class="flex items-center justify-center w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 text-lg text-blue-600"><i class="{{ old('icon_modul', 'fa-solid fa-square') }}"></i></span>
                        <input type="text" id="icon_search" placeholder="Cari icon..." class="flex-1 text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    </div>
                    <div id="icon_grid" class="h-64 overflow-y-auto border border-slate-200 rounded-lg bg-white p-3 grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-1.5 drag-scroll">
                        @foreach ($icons as $icon)
                        <button type="button" class="icon-option flex items-center justify-center h-10 rounded-md text-base text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-colors cursor-pointer border border-transparent hover:border-blue-200" data-icon="{{ $icon }}" title="{{ $icon }}">
                            <i class="{{ $icon }}"></i>
                        </button>
                        @endforeach
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">{{ count($icons) }} icon tersedia. Klik salah satu untuk memilih.</p>
                    @error('icon_modul')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="reset" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
                    Reset Form
                </button>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Modul
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('icon_modul');
        const preview = document.getElementById('preview_icon');
        const search = document.getElementById('icon_search');
        const grid = document.getElementById('icon_grid');
        const options = Array.from(grid.querySelectorAll('.icon-option'));

        function selectIcon(iconClass) {
            input.value = iconClass;
            preview.innerHTML = '<i class="' + iconClass + '"></i>';
            options.forEach(o => {
                o.classList.remove('bg-blue-100', 'text-blue-600', 'border-blue-300');
                if (o.dataset.icon === iconClass) {
                    o.classList.add('bg-blue-100', 'text-blue-600', 'border-blue-300');
                }
            });
        }

        options.forEach(btn => {
            btn.addEventListener('click', () => selectIcon(btn.dataset.icon));
        });

        search.addEventListener('input', () => {
            const q = search.value.trim().toLowerCase();
            options.forEach(btn => {
                const name = btn.dataset.icon.replace('fa-solid fa-', '').toLowerCase();
                const show = q === '' || name.includes(q);
                btn.style.display = show ? '' : 'none';
            });
        });

        const current = input.value;
        if (current) {
            const match = options.find(o => o.dataset.icon === current);
            if (match) {
                match.classList.add('bg-blue-100', 'text-blue-600', 'border-blue-300');
                match.scrollIntoView({ block: 'center' });
            }
        }
    });
</script>
@endpush
@endsection
