@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Dashboard Rekam Medis (EMR)</h1>
        <p class="text-sm text-slate-500 mt-1">Pusat informasi medis dan riwayat klinis pasien.</p>
    </div>
</div>

<!-- Komponen Informasi Pasien -->
<x-informasi-pasien />

<!-- EMR Tabs / Navigation -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden min-h-[500px]" data-no-loader="true">
    <div class="border-b border-slate-700 bg-slate-800 flex flex-wrap">
        @if(isset($ehrMenus) && count($ehrMenus) > 0)
            @foreach($ehrMenus as $menuName => $subMenus)
                <div class="relative group">
                    <button type="button" onclick="toggleDropdown(this)" class="flex items-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-700/50 min-w-max transition-colors border-b-2 border-transparent hover:border-blue-400 focus:outline-none cursor-pointer">
                        {{ ucwords($menuName) }}
                        @if(is_array($subMenus) && count($subMenus) > 0 && !isset($subMenus['id']))
                            <svg class="w-3.5 h-3.5 ml-1 opacity-70 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        @endif
                    </button>
                    
                    @if(is_array($subMenus) && count($subMenus) > 0 && !isset($subMenus['id']))
                        <!-- Dropdown Sub Menu -->
                        <div class="submenu hidden absolute left-full top-0 w-64 bg-white border border-slate-200 rounded-lg shadow-xl z-50">
                            <div class="py-2">
                                @foreach($subMenus as $subMenuName => $extras)
                                    @if(is_array($extras) && !isset($extras['id']) && count($extras) > 0)
                                        <div class="relative group-sub">
                                            <a href="#" onclick="toggleExtraDropdown(event, this)" class="flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600">
                                                {{ ucwords($subMenuName) }}
                                                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </a>
                                            <!-- Sub-Dropdown (Extra) -->
                                            <div class="extra-submenu hidden absolute left-full top-0 w-64 bg-white border border-slate-200 rounded-lg shadow-xl transition-all z-50 -ml-1">
                                                <div class="py-2">
                                                    @foreach($extras as $extra)
                                                        @php
                                                            $folder_name = Str::slug($extra['nama'], '_');
                                                            $menuUrl = route('emr.dynamic.index', ['form_name' => $folder_name, 'registrasi_detail_id' => $registrasi_detail_id]);
                                                        @endphp
                                                        <a href="{{ $menuUrl }}" target="emr_frame" onclick="document.getElementById('emr_placeholder').style.display='none'; document.getElementById('emr_loader').classList.remove('hidden');" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600">{{ ucwords($extra['nama']) }}</a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $folder_name = Str::slug($subMenuName, '_');
                                            $menuUrl = route('emr.dynamic.index', ['form_name' => $folder_name, 'registrasi_detail_id' => $registrasi_detail_id]);
                                        @endphp
                                        <a href="{{ $menuUrl }}" target="emr_frame" onclick="document.getElementById('emr_placeholder').style.display='none'; document.getElementById('emr_loader').classList.remove('hidden');" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600">{{ ucwords($subMenuName) }}</a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <!-- Fallback if no menus loaded -->
            <a href="{{ route('emr.dynamic.index', ['form_name' => 'soap', 'registrasi_detail_id' => $registrasi_detail_id]) }}" target="emr_frame" onclick="document.getElementById('emr_placeholder').style.display='none'; document.getElementById('emr_loader').classList.remove('hidden');" class="flex items-center gap-2 px-5 py-3.5 text-sm font-bold text-white border-b-2 border-blue-400 bg-slate-700/50 min-w-max transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                CPPT (SOAP)
            </a>
        @endif
    </div>

    <!-- Tab Content Area (Iframe) -->
    <div class="relative bg-slate-50/30 w-full h-[650px]">
        
        <!-- Loading Overlay -->
        <div id="emr_loader" class="absolute inset-0 flex flex-col items-center justify-center bg-slate-50/80 backdrop-blur-sm z-20 hidden">
            <div class="w-10 h-10 border-4 border-slate-200 border-t-blue-600 rounded-full animate-spin mb-3"></div>
            <p class="text-sm font-semibold text-slate-600 animate-pulse">Memuat Form...</p>
        </div>

        <iframe name="emr_frame" id="emr_frame" onload="document.getElementById('emr_loader').classList.add('hidden');" class="w-full h-full border-none relative z-10" src="about:blank"></iframe>
        
        <!-- Area Kosong / Placeholder -->
        <div id="emr_placeholder" class="absolute inset-0 flex flex-col items-center justify-center text-center opacity-50 pointer-events-none bg-slate-50/30 z-30">
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

<script>
    function closeAllDropdowns() {
        document.querySelectorAll('.submenu, .extra-submenu').forEach(menu => {
            menu.classList.add('hidden');
        });
    }

    document.addEventListener('click', function(e) {
        // 1. Jika yang diklik adalah link menu tujuan (bukan tombol toggle submenu)
        if (e.target.closest('a') && !e.target.closest('a').hasAttribute('onclick') || (e.target.closest('a') && !e.target.closest('a').getAttribute('onclick').includes('toggleExtraDropdown'))) {
            // Kita ingin menu tertutup setelah item dipilih
            closeAllDropdowns();
        }
        
        // 2. Jika klik terjadi di luar area menu sama sekali
        else if (!e.target.closest('.group')) {
            closeAllDropdowns();
        }
    });

    // 3. Jika klik terjadi di dalam iframe (iframe iframe merebut fokus dari window utama)
    window.addEventListener('blur', function() {
        closeAllDropdowns();
    });

    function toggleDropdown(button) {
        const submenu = button.nextElementSibling;

        // Tutup submenu lain yang selevel
        document.querySelectorAll('.submenu').forEach(menu => {
            if (menu !== submenu) {
                menu.classList.add('hidden');
            }
        });
        
        // Tutup semua extra-submenu jika ada yang terbuka
        document.querySelectorAll('.extra-submenu').forEach(menu => {
            menu.classList.add('hidden');
        });

        // Toggle submenu yang diklik
        submenu.classList.toggle('hidden');
    }

    function toggleExtraDropdown(event, link) {
        event.preventDefault();
        event.stopPropagation(); // Mencegah click menyebar ke document (agar menu tidak tertutup otomatis)
        
        const extraSubmenu = link.nextElementSibling;
        
        // Tutup extra-submenu lain jika ada yang terbuka
        document.querySelectorAll('.extra-submenu').forEach(menu => {
            if (menu !== extraSubmenu) {
                menu.classList.add('hidden');
            }
        });

        // Toggle extra-submenu yang diklik
        extraSubmenu.classList.toggle('hidden');
    }
</script>
@endsection
