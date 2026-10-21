@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Manajemen Akses EHR</h1>
        <p class="text-sm text-slate-500 mt-1">Pilih profesi dahulu, lalu centang aksi (Tambah / Lihat / Ubah / Hapus) per form EMR yang boleh diakses profesi tersebut.</p>
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

<!-- Pilih Profesi -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-5">
    <form action="{{ route('admin.akses_ehr.index') }}" method="GET">
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="w-full sm:w-80">
                <label for="profesi_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Profesi</label>
                <div class="relative">
                    <select id="profesi_id" name="profesi_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none"
                            onchange="this.form.submit()">
                        <option value="">-- Pilih Profesi --</option>
                        @foreach ($profesis as $p)
                            <option value="{{ $p->profesi_id }}" {{ $profesiId === $p->profesi_id ? 'selected' : '' }}>{{ $p->nama_profesi }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <button type="submit" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">Tampilkan</button>
            </div>
        </div>
    </form>
</div>

@if (! $profesiId)
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-10 text-center">
        <p class="text-sm font-medium text-blue-700">Silakan pilih profesi terlebih dahulu untuk melihat daftar dashboard menu.</p>
    </div>
@else
    @php
        $selectedProfesi = $profesis->firstWhere('profesi_id', $profesiId);
        $aksi = ['create' => 'Tambah', 'read' => 'Lihat', 'update' => 'Ubah', 'delete' => 'Hapus'];
    @endphp

    <form action="{{ route('admin.akses_ehr.store') }}" method="POST" id="form-akses">
        @csrf
        <input type="hidden" name="profesi_id" value="{{ $profesiId }}">

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-5">
            <div class="px-4 py-3 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 bg-slate-50">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm font-semibold text-slate-800">Profesi:</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-blue-600 text-white text-xs font-bold">{{ $selectedProfesi->nama_profesi ?? '-' }}</span>
                    <span class="text-xs text-slate-500">{{ $aksesCount }} dari {{ $totalLeaf }} form diakses</span>
                </div>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" id="pilih-semua" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500/30">
                        Pilih Semua
                    </label>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Akses
                    </button>
                </div>
            </div>

            @if (count($tree) === 0)
                <div class="px-4 py-10 text-center text-slate-400 text-sm">Belum ada dashboard menu / form yang terdaftar.</div>
            @else
                <div class="overflow-x-auto drag-scroll">
                    <table class="w-full text-left" style="min-width: 860px;">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Akses</th>
                                <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Form EMR</th>
                                <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Dashboard Menu</th>
                                <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ref.</th>
                            </tr>
                        </thead>
                        <tbody class="text-[12px] divide-y divide-slate-100">
                            @foreach ($tree as $menu)
                                <tr class="bg-slate-100/70">
                                    <td class="px-3 py-2.5" colspan="4">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500/30 select-menu" data-menu="menu-{{ $menu['id'] }}">
                                            <span class="text-[13px] font-bold text-slate-800">{{ $menu['nama'] }}</span>
                                        </label>
                                    </td>
                                </tr>

                                @foreach ($menu['subs'] as $sub)
                                    @if ($sub['leaf'])
                                        @php $leaf = $sub['leaf']; $ref = $leaf['id_dash_menu']; $path = $sub['nama']; @endphp
                                        @include('moduls.Administrator.ManajemenEMR.AksesEhr._leaf_akses', ['leaf' => $leaf])
                                    @endif

                                    @foreach ($sub['extras'] as $extra)
                                        @php $leaf = $extra; $ref = $extra['id_dash_menu']; $path = $sub['nama'] . ' › ' . $extra['nama_extra']; @endphp
                                        @include('moduls.Administrator.ManajemenEMR.AksesEhr._leaf_akses', ['leaf' => $leaf])
                                    @endforeach
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </form>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var semua = document.getElementById('pilih-semua');
        var semuaLeaf = document.querySelectorAll('.leaf');

        function semuaTercentang() {
            if (!semua) return;
            semua.checked = semuaLeaf.length > 0 && Array.prototype.every.call(semuaLeaf, function (c) { return c.checked; });
        }

        semuaLeaf.forEach(function (c) {
            c.addEventListener('change', function () {
                var menuId = c.dataset.menu;
                var menuCheck = document.querySelector('.select-menu[data-menu="' + menuId + '"]');
                var leaves = document.querySelectorAll('.leaf[data-menu="' + menuId + '"]');
                if (menuCheck) {
                    menuCheck.checked = Array.prototype.every.call(leaves, function (l) { return l.checked; });
                }
                semuaTercentang();
            });
        });

        document.querySelectorAll('.select-menu').forEach(function (m) {
            m.addEventListener('change', function () {
                document.querySelectorAll('.leaf[data-menu="' + m.dataset.menu + '"]').forEach(function (l) {
                    l.checked = m.checked;
                });
                semuaTercentang();
            });
        });

        if (semua) {
            semua.addEventListener('change', function () {
                semuaLeaf.forEach(function (l) { l.checked = semua.checked; });
                document.querySelectorAll('.select-menu').forEach(function (m) { m.checked = semua.checked; });
            });
        }

        semuaTercentang();
    });
</script>

<style>
    select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
    }
</style>
@endsection