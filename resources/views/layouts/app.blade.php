<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SIMRS') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('logo_blue.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Dependencies loaded via Vite (app.js & app.css) -->
    <style>
        summary::-webkit-details-marker { display: none; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">
    @include('components.loading')

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar-wrapper" class="transition-all duration-300 ease-in-out w-70 overflow-hidden bg-slate-900 border-r border-slate-800 flex-shrink-0">
            @include('layouts.sidebar')
        </div>
        
        <!-- Main Content -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden min-w-0 bg-slate-50">
            @include('layouts.navbar')
            <main class="w-full grow p-6">
                {{-- Notification --}}
                @if(session('success'))
                    <div class="flex items-start gap-3 bg-emerald-50 border border-emerald-200 p-3.5 mb-6 rounded-lg">
                        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
                    </div>
                @endif
                @if(session('error'))
                    <div class="flex items-start gap-3 bg-red-50 border border-red-200 p-3.5 mb-6 rounded-lg">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                @endif
                @yield('content')
            </main>
            @include('layouts.footer')
        </div>
    </div>

    <!-- Scripts (jQuery & Select2 dibundle via Vite di resources/js/app.js) -->
    <!-- Custom Matcher Select2 Removed -->
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar-wrapper');
            
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    closeFlyout();
                    const collapsed = sidebar.classList.toggle('sidebar-collapsed');

                    if (collapsed) {
                        sidebar.classList.remove('w-70');
                        sidebar.classList.add('w-[68px]');
                        sidebar.querySelectorAll('details[open]').forEach(d => d.removeAttribute('open'));
                    } else {
                        sidebar.classList.remove('w-[68px]');
                        sidebar.classList.add('w-70');
                    }
                });

                // Floating Flyout untuk Sidebar Collapsed
                function openFlyout(summary) {
                    const modulItem = summary.closest('.modul-item');
                    if (!modulItem) return;

                    const modulName = modulItem.querySelector('.modul-name')?.textContent.trim() || 'Menu';
                    const iconEl = summary.querySelector('i');
                    const iconHtml = iconEl ? iconEl.outerHTML : '<i class="fa-solid fa-square"></i>';
                    const modulContent = modulItem.querySelector('.modul-content');

                    let popover = document.getElementById('sidebar-floating-popover');
                    if (!popover) {
                        popover = document.createElement('div');
                        popover.id = 'sidebar-floating-popover';
                        popover.className = 'fixed z-[9999] w-64 max-w-[280px] bg-slate-900 border border-slate-700/80 rounded-xl shadow-2xl shadow-black/80 overflow-hidden flex flex-col transition-all duration-150';
                        document.body.appendChild(popover);
                    }

                    let contentHtml = '';
                    if (modulContent) {
                        const clonedContent = modulContent.cloneNode(true);
                        clonedContent.className = 'p-2 space-y-1.5 overflow-y-auto max-h-[70vh] sidebar-scroll';
                        // Pastikan menu di dalam flyout terbuka & rapi
                        clonedContent.querySelectorAll('details').forEach(d => {
                            d.setAttribute('open', '');
                            d.className = 'group/menu menu-item border-b border-slate-800/60 last:border-b-0 pb-1.5 mb-1';
                        });
                        contentHtml = clonedContent.outerHTML;
                    } else {
                        contentHtml = '<div class="p-3 text-xs text-slate-400 text-center">Tidak ada menu</div>';
                    }

                    popover.innerHTML = `
                        <div class="px-3.5 py-2.5 bg-slate-800/90 border-b border-slate-700/60 flex items-center justify-between gap-2 flex-shrink-0">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="text-blue-400 text-sm flex-shrink-0">${iconHtml}</span>
                                <span class="text-xs font-bold text-white tracking-wide truncate">${modulName}</span>
                            </div>
                            <button type="button" id="close-floating-popover" class="text-slate-400 hover:text-white p-1 rounded-md hover:bg-slate-700/50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        ${contentHtml}
                    `;

                    // Posisikan popover di samping ikon (sidebar collapsed = 68px)
                    const rect = summary.getBoundingClientRect();
                    const left = 72;
                    popover.style.display = 'flex';
                    popover.style.left = left + 'px';
                    
                    let top = rect.top;
                    const popoverHeight = popover.offsetHeight || 300;
                    if (top + popoverHeight > window.innerHeight - 16) {
                        top = Math.max(16, window.innerHeight - popoverHeight - 16);
                    }
                    popover.style.top = top + 'px';
                    popover.dataset.activeSummary = modulName;

                    const closeBtn = popover.querySelector('#close-floating-popover');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', closeFlyout);
                    }
                }

                function closeFlyout() {
                    const popover = document.getElementById('sidebar-floating-popover');
                    if (popover) {
                        popover.style.display = 'none';
                        delete popover.dataset.activeSummary;
                    }
                }

                // Saat collapsed, klik ikon modul = buka popover flyout menu & biarkan sidebar tetap tertutup.
                sidebar.addEventListener('click', function(e) {
                    const summary = e.target.closest('.modul-summary');
                    if (summary && sidebar.classList.contains('sidebar-collapsed')) {
                        e.preventDefault();
                        e.stopPropagation();

                        const modulName = summary.closest('.modul-item')?.querySelector('.modul-name')?.textContent.trim() || '';
                        const popover = document.getElementById('sidebar-floating-popover');

                        if (popover && popover.style.display !== 'none' && popover.dataset.activeSummary === modulName) {
                            closeFlyout();
                        } else {
                            openFlyout(summary);
                        }
                    }
                });

                // Tutup popover jika klik di luar
                document.addEventListener('click', function(e) {
                    const popover = document.getElementById('sidebar-floating-popover');
                    if (popover && popover.style.display !== 'none') {
                        if (!popover.contains(e.target) && !e.target.closest('.modul-summary')) {
                            closeFlyout();
                        }
                    }
                });

                // Tutup popover dengan tombol Escape
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeFlyout();
                    }
                });

                // Shortcut keyboard Ctrl+B untuk toggle sidebar
                document.addEventListener('keydown', function(e) {
                    if (e.ctrlKey && e.key.toLowerCase() === 'b') {
                        e.preventDefault();
                        toggleBtn.click();
                    }
                });
            }

            // Sidebar Search
            const searchInput = document.getElementById('sidebar-search');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase().trim();
                    const moduls = document.querySelectorAll('.modul-item');
                    
                    moduls.forEach(modul => {
                        let modulMatches = false;
                        const modulName = modul.querySelector('.modul-name').textContent.toLowerCase();
                        
                        if (modulName.includes(searchTerm)) {
                            modulMatches = true;
                        }
                        
                        const menus = modul.querySelectorAll('.menu-item');
                        let anyMenuMatches = false;
                        
                        menus.forEach(menu => {
                            let menuMatches = false;
                            const menuName = menu.querySelector('.menu-name').textContent.toLowerCase();
                            
                            if (menuName.includes(searchTerm)) {
                                menuMatches = true;
                            }
                            
                            const submenus = menu.querySelectorAll('.submenu-item');
                            let anySubmenuMatches = false;
                            
                            submenus.forEach(submenu => {
                                const submenuName = submenu.textContent.toLowerCase();
                                if (submenuName.includes(searchTerm)) {
                                    submenu.style.display = 'block';
                                    anySubmenuMatches = true;
                                } else {
                                    if (searchTerm === '' || menuMatches || modulMatches) {
                                        submenu.style.display = 'block';
                                    } else {
                                        submenu.style.display = 'none';
                                    }
                                }
                            });
                            
                            if (anySubmenuMatches || menuMatches || modulMatches) {
                                menu.style.display = 'block';
                                anyMenuMatches = true;
                                if (searchTerm !== '') {
                                    menu.setAttribute('open', '');
                                } else {
                                    menu.removeAttribute('open');
                                }
                            } else {
                                menu.style.display = 'none';
                            }
                        });
                        
                        if (anyMenuMatches || modulMatches) {
                            modul.style.display = 'block';
                            if (searchTerm !== '') {
                                modul.setAttribute('open', '');
                            } else {
                                modul.removeAttribute('open');
                            }
                        } else {
                            modul.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
    <x-confirm-alert />
    @stack('scripts')
</body>
</html>
