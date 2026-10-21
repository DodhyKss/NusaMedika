@extends('layouts.app')

@section('content')
<!-- Komponen Informasi Pasien -->
<x-informasi-pasien />

<!-- EMR Tabs / Navigation -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm min-h-[500px]" data-no-loader="true">
    <div class="border-b border-slate-700 bg-slate-800 flex flex-wrap">
        @if(isset($ehrMenus) && count($ehrMenus) > 0)
            @foreach($ehrMenus as $menuName => $subMenus)
                <div class="relative group">
                    <button type="button" onclick="toggleDropdown(event, this)" class="flex items-center gap-2 px-5 py-3.5 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-700/50 min-w-max transition-colors border-b-2 border-transparent hover:border-blue-400 focus:outline-none cursor-pointer">
                        {{ ucwords($menuName) }}
                        @if(is_array($subMenus) && count($subMenus) > 0 && !isset($subMenus['id']))
                            <svg class="w-3.5 h-3.5 ml-1 opacity-70 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        @endif
                    </button>
                    
                    @if(is_array($subMenus) && count($subMenus) > 0 && !isset($subMenus['id']))
                        <!-- Dropdown Sub Menu -->
                        <div class="submenu hidden absolute top-full left-0 mt-1 w-64 bg-white border border-slate-200 rounded-lg shadow-xl z-[100]">
                            <div class="py-2">
                                @foreach($subMenus as $subMenuName => $extras)
                                    @if(is_array($extras) && !isset($extras['id']) && count($extras) > 0)
                                        <div class="relative group-sub">
                                            <a href="#" onclick="toggleExtraDropdown(event, this)" class="flex items-center justify-between px-4 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600">
                                                {{ ucwords($subMenuName) }}
                                                <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </a>
                                            <!-- Sub-Dropdown (Extra) -->
                                            <div class="extra-submenu hidden absolute top-0 left-full ml-1 w-64 bg-white border border-slate-200 rounded-lg shadow-xl transition-all z-[100]">
                                                <div class="py-2">
                                                    @foreach($extras as $extra)
                                                        @php
                                                            $folder_name = Str::slug($extra['nama'], '_');
                                                            $menuUrl = $folder_name === 'katalog_form_blanko'
                                                                ? route('emr.katalog_form_blanko.index', ['registrasi_detail_id' => $registrasi_detail_id])
                                                                : route('emr.dynamic.index', ['form_name' => $folder_name, 'registrasi_detail_id' => $registrasi_detail_id]);
                                                        @endphp
                                                        <a href="{{ $menuUrl }}" target="emr_frame" onclick="document.getElementById('emr_placeholder').style.display='none'; document.getElementById('emr_loader').classList.remove('hidden');" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600">{{ ucwords($extra['nama']) }}</a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        @php
                                            $folder_name = Str::slug($subMenuName, '_');
                                            $menuUrl = $folder_name === 'katalog_form_blanko'
                                                ? route('emr.katalog_form_blanko.index', ['registrasi_detail_id' => $registrasi_detail_id])
                                                : route('emr.dynamic.index', ['form_name' => $folder_name, 'registrasi_detail_id' => $registrasi_detail_id]);
                                        @endphp
                                        <a href="{{ $menuUrl }}" target="emr_frame" onclick="document.getElementById('emr_placeholder').style.display='none'; document.getElementById('emr_loader').classList.remove('hidden');" class="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600">{{ ucwords($subMenuName) }}</a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    <!-- Tab Content Area (Iframe) -->
    <div class="relative bg-slate-50/30 w-full h-[650px] overflow-hidden rounded-b-xl">
        
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
            resetPosisi(menu);
        });
    }

    function resetPosisi(menu) {
        menu.style.left = '';
        menu.style.right = '';
        menu.style.top = '';
        menu.style.bottom = '';
        menu.style.marginLeft = '';
        menu.style.marginRight = '';
    }

    function pasangPosisi(menu, anchor, jenis) {
        resetPosisi(menu);
        menu.classList.remove('hidden');

        const rectMenu = menu.getBoundingClientRect();
        const rectAnchor = anchor.getBoundingClientRect();
        const vw = window.innerWidth;
        const vh = window.innerHeight;
        const gap = 8;
        const margin = 8;

        if (jenis === 'bawah') {
            // Buka ke bawah; flip ke atas bila melewati batas bawah viewport
            menu.style.top = '100%';
            menu.style.left = '0';
            menu.style.marginTop = gap + 'px';

            if (rectAnchor.bottom + gap + rectMenu.height + margin > vh) {
                menu.style.top = 'auto';
                menu.style.bottom = '100%';
                menu.style.marginTop = '0';
                menu.style.marginBottom = gap + 'px';
            }

            // Flip horizontal bila melewati batas kanan viewport
            if (rectAnchor.left + rectMenu.width + margin > vw) {
                menu.style.left = 'auto';
                menu.style.right = '0';
            }
        } else {
            // Sub-extra: default ke kanan; flip ke kiri bila melewati batas kanan
            menu.style.top = '0';
            menu.style.left = '100%';
            menu.style.marginLeft = gap + 'px';

            if (rectAnchor.right + gap + rectMenu.width + margin > vw) {
                menu.style.left = 'auto';
                menu.style.right = '100%';
                menu.style.marginLeft = '0';
                menu.style.marginRight = gap + 'px';
            }

            // Flip vertical bila melewati batas bawah viewport
            if (rectAnchor.top + rectMenu.height + margin > vh) {
                menu.style.top = 'auto';
                menu.style.bottom = '0';
            }
        }
    }

    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (link) {
            const onclick = link.getAttribute('onclick') || '';
            if (!onclick.includes('toggleExtraDropdown')) {
                closeAllDropdowns();
            }
        } else if (!e.target.closest('.group')) {
            closeAllDropdowns();
        }
    });

    // Klik di dalam iframe membuat window utama blur — tutup menu agar tidak nyangkut
    window.addEventListener('blur', function() {
        closeAllDropdowns();
    });

    window.addEventListener('resize', function() {
        closeAllDropdowns();
    });

    function toggleDropdown(event, button) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const submenu = button.nextElementSibling;
        if (!submenu || !submenu.classList.contains('submenu')) {
            return;
        }

        const sedangTerbuka = !submenu.classList.contains('hidden');

        document.querySelectorAll('.submenu').forEach(menu => {
            if (menu !== submenu) {
                menu.classList.add('hidden');
                resetPosisi(menu);
            }
        });
        document.querySelectorAll('.extra-submenu').forEach(menu => {
            menu.classList.add('hidden');
            resetPosisi(menu);
        });

        if (sedangTerbuka) {
            submenu.classList.add('hidden');
            resetPosisi(submenu);
        } else {
            pasangPosisi(submenu, button, 'bawah');
        }
    }

    function toggleExtraDropdown(event, link) {
        event.preventDefault();
        event.stopPropagation();

        const extraSubmenu = link.nextElementSibling;
        if (!extraSubmenu || !extraSubmenu.classList.contains('extra-submenu')) {
            return;
        }

        const sedangTerbuka = !extraSubmenu.classList.contains('hidden');

        document.querySelectorAll('.extra-submenu').forEach(menu => {
            if (menu !== extraSubmenu) {
                menu.classList.add('hidden');
                resetPosisi(menu);
            }
        });

        if (sedangTerbuka) {
            extraSubmenu.classList.add('hidden');
            resetPosisi(extraSubmenu);
        } else {
            pasangPosisi(extraSubmenu, link, 'samping');
        }
    }
</script>
@endsection
