<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SIMRS') }} Dashboard</title>
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

    <!-- Scripts -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script>
        // Polyfill untuk fungsi jQuery yang dihapus di versi 3.3+ namun masih dipakai oleh Select2
        if (typeof jQuery.isArray === 'undefined') {
            jQuery.isArray = Array.isArray;
        }
        if (typeof jQuery.isFunction === 'undefined') {
            jQuery.isFunction = function(obj) {
                return typeof obj === "function";
            };
        }
    </script>
    <script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar-wrapper');
            
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    if (sidebar.classList.contains('w-70')) {
                        sidebar.classList.remove('w-70');
                        sidebar.classList.add('w-0');
                        sidebar.classList.remove('border-r');
                    } else {
                        sidebar.classList.remove('w-0');
                        sidebar.classList.add('w-70');
                        sidebar.classList.add('border-r');
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
</body>
</html>
