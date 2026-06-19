@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Dashboard Rekam Medis (EMR)</h1>
        <p class="text-sm text-slate-500 mt-1">Pusat informasi medis dan riwayat klinis pasien.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('list_pelayanan_pasien.index') }}" class="inline-flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-semibold py-2 px-4 rounded-lg shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<!-- Komponen Informasi Pasien -->
<x-informasi-pasien />

<!-- EMR Tabs / Navigation -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden min-h-[500px]">
    <div class="border-b border-slate-200 bg-slate-50/50 flex flex-wrap">
        @if(isset($ehrMenus) && count($ehrMenus) > 0)
            @foreach($ehrMenus as $menuName => $subMenus)
                <div class="relative group">
                    <button class="flex items-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-600 hover:text-blue-600 hover:bg-white min-w-max transition-colors border-b-2 border-transparent hover:border-blue-600 focus:outline-none">
                        {{ ucwords($menuName) }}
                        @if(is_array($subMenus) && count($subMenus) > 0 && !isset($subMenus['id']))
                            <svg class="w-3.5 h-3.5 ml-1 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        @endif
                    </button>
                    
                    @if(is_array($subMenus) && count($subMenus) > 0 && !isset($subMenus['id']))
                        <!-- Dropdown Sub Menu -->
                        <div class="absolute left-0 top-full w-64 bg-white border border-slate-200 rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                            <div class="py-2">
                                @foreach($subMenus as $subMenuName => $extras)
                                    @if(is_array($extras) && !isset($extras['id']) && count($extras) > 0)
                                        <div class="relative group/sub">
                                            <a href="#" class="flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600">
                                                {{ ucwords($subMenuName) }}
                                                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </a>
                                            <!-- Sub-Dropdown (Extra) -->
                                            <div class="absolute left-full top-0 w-64 bg-white border border-slate-200 rounded-lg shadow-xl opacity-0 invisible group-hover/sub:opacity-100 group-hover/sub:visible transition-all z-50 -ml-1">
                                                <div class="py-2">
                                                    @foreach($extras as $extra)
                                                        <a href="#" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600">{{ ucwords($extra['nama']) }}</a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <a href="#" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600">{{ ucwords($subMenuName) }}</a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <!-- Fallback if no menus loaded -->
            <button class="flex items-center gap-2 px-5 py-3.5 text-sm font-bold text-blue-600 border-b-2 border-blue-600 bg-white min-w-max transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                CPPT (SOAP)
            </button>
        @endif
    </div>

    <!-- Tab Content Area -->
    <div class="p-6 bg-slate-50/30 h-full min-h-[600px]">
        <!-- Area Kosong / Placeholder -->
        <div class="flex flex-col items-center justify-center h-[300px] text-center opacity-50">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4 border border-slate-200">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            </div>
            <h3 class="text-base font-bold text-slate-600">Form Belum Dipilih</h3>
            <p class="text-sm text-slate-400 mt-1 max-w-sm">Silakan pilih menu dari tab di atas untuk membuka rekam medis atau form pemeriksaan pasien.</p>
        </div>
    </div>
</div>

<style>
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection
