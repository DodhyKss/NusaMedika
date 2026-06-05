<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SIMRS') }} Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Hide details marker for summary */
        summary::-webkit-details-marker {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Wrapper for Toggling -->
        <div id="sidebar-wrapper" class="transition-all duration-300 ease-in-out w-72 overflow-hidden bg-slate-900 border-r border-slate-800 flex-shrink-0">
            @include('layouts.sidebar')
        </div>
        
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden min-w-0 bg-slate-50">
            @include('layouts.navbar')
            <main class="w-full grow p-6 sm:p-8">
                @yield('content')
            </main>
            @include('layouts.footer')
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar-wrapper');
            
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    if (sidebar.classList.contains('w-72')) {
                        sidebar.classList.remove('w-72');
                        sidebar.classList.add('w-0');
                        sidebar.classList.remove('border-r'); // Hide border when collapsed
                    } else {
                        sidebar.classList.remove('w-0');
                        sidebar.classList.add('w-72');
                        sidebar.classList.add('border-r');
                    }
                });
            }

            // Sidebar Search Logic
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
                                    // Show submenu if parent menu or modul matches the search directly, 
                                    // or if search is empty
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
                                // Auto-expand if searching
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
                            // Auto-expand if searching
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
