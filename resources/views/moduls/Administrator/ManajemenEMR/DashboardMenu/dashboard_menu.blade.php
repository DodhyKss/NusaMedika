@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Master Dashboard Menu</h1>
        <p class="text-sm text-slate-500 mt-1">Kelola struktur menu, sub menu, dan sub menu extra struktur dashboard menu pada EMR.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.dashboard_menu.create', ['tab' => $tab]) }}" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah {{ ucfirst($tab) }}
        </a>
    </div>
</div>

<!-- Tabs -->
<div class="mb-5">
    <div class="inline-flex bg-slate-100 rounded-xl p-1 gap-1">
        @php
            $tabs = [
                'menu' => ['label' => 'Menu', 'count' => $menus->count()],
                'sub' => ['label' => 'Sub Menu', 'count' => $subs->count()],
                'extra' => ['label' => 'Sub Menu Extra', 'count' => $extraCount],
            ];
        @endphp
        @foreach ($tabs as $key => $t)
            <a href="{{ route('admin.dashboard_menu.index', ['tab' => $key]) }}"
               class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $tab === $key ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $t['label'] }}
                <span class="ml-1.5 text-[11px] {{ $tab === $key ? 'bg-blue-50 text-blue-600' : 'bg-slate-200 text-slate-500' }} px-1.5 py-0.5 rounded-full">{{ $t['count'] }}</span>
            </a>
        @endforeach
    </div>
</div>

<!-- Filter -->
@if ($tab !== 'menu')
<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-5">
    <form action="{{ route('admin.dashboard_menu.index') }}" method="GET">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div class="w-full sm:w-80">
                <label for="parent_filter" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
                    {{ $tab === 'sub' ? 'Menu' : 'Sub Menu' }}
                </label>
                <div class="relative">
                    <select id="parent_filter" name="{{ $tab === 'sub' ? 'dashboard_menu_id' : 'dashboard_menu_sub_id' }}"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none"
                            onchange="this.form.submit()">
                        @if ($tab === 'sub')
                            <option value="">-- Semua Menu --</option>
                            @foreach ($menus as $item)
                                <option value="{{ $item->dashboard_menu_id }}" {{ request('dashboard_menu_id') == $item->dashboard_menu_id ? 'selected' : '' }}>{{ $item->nama_menu }}</option>
                            @endforeach
                        @else
                            <option value="">-- Semua Sub Menu --</option>
                            @foreach ($subs as $item)
                                <option value="{{ $item->dashboard_menu_sub_id }}" {{ request('dashboard_menu_sub_id') == $item->dashboard_menu_sub_id ? 'selected' : '' }}>{{ $item->nama_sub_menu }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>
            @if (request()->has($tab === 'sub' ? 'dashboard_menu_id' : 'dashboard_menu_sub_id'))
                <a href="{{ route('admin.dashboard_menu.index', ['tab' => $tab]) }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">Reset</a>
            @endif
        </div>
    </form>
</div>
@endif

<!-- Data Table -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto drag-scroll">
        <table class="w-full text-left" style="min-width: 900px;">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200">
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">No.</th>
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        {{ match ($tab) { 'sub' => 'Nama Sub Menu', 'extra' => 'Nama Sub Menu Extra', default => 'Nama Menu' } }}
                    </th>
                    @if ($tab === 'sub')
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Menu</th>
                    @elseif ($tab === 'extra')
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sub Menu</th>
                    @endif
                    @if ($tab === 'extra')
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Menu</th>
                    @endif
                    @if ($tab !== 'extra')
                        <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">{{ $tab === 'menu' ? 'Jumlah Sub Menu' : 'Jumlah Extra' }}</th>
                    @endif
                    <th class="px-3 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-[12px] divide-y divide-slate-100">
                @forelse ($data as $i => $row)
                @php
                    $pk = match ($tab) { 'sub' => 'dashboard_menu_sub_id', 'extra' => 'dashboard_menu_sub_extra_id', default => 'dashboard_menu_id' };
                    $nama = match ($tab) { 'sub' => $row->nama_sub_menu, 'extra' => $row->nama_sub_menu_extra, default => $row->nama_menu };
                    $countField = match ($tab) { 'menu' => 'sub_count', 'sub' => 'extra_count', default => null };
                @endphp
                <tr class="hover:bg-blue-50/40 transition-colors">
                    <td class="px-3 py-3 text-center text-slate-500">{{ $i + 1 }}</td>
                    <td class="px-3 py-3 font-semibold text-slate-800">{{ $nama }}</td>
                    @if ($tab === 'sub')
                        <td class="px-3 py-3 text-slate-600">{{ $row->nama_menu }}</td>
                    @elseif ($tab === 'extra')
                        <td class="px-3 py-3 text-slate-600">{{ $row->nama_sub_menu }}</td>
                        <td class="px-3 py-3 text-slate-600">{{ $row->nama_menu }}</td>
                    @endif
                    @if ($tab !== 'extra')
                        <td class="px-3 py-3 text-center text-slate-600">{{ $row->{$countField} }}</td>
                    @endif
                    <td class="px-3 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <a href="{{ route('admin.dashboard_menu.edit', ['dashboard_menu' => $row->$pk, 'tab' => $tab]) }}" class="cursor-pointer p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-600 rounded-md transition-colors" title="Edit {{ ucfirst($tab) }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form action="{{ route('admin.dashboard_menu.destroy', ['dashboard_menu' => $row->$pk, 'tab' => $tab]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus {{ $nama }} beserta data turunannya?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cursor-pointer p-1.5 text-red-500 hover:bg-red-50 hover:text-red-600 rounded-md transition-colors" title="Hapus {{ ucfirst($tab) }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-3 py-8 text-center text-slate-400">Belum ada data {{ $tab }}.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
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