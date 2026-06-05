<aside class="flex flex-col w-full h-screen bg-slate-900 border-r border-slate-800 z-20 overflow-hidden">
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center justify-center border-b border-slate-800 px-4 flex-shrink-0">
        <h2 class="text-2xl font-bold text-white tracking-tight flex items-center">
            <span class="w-2 h-2 rounded-full bg-yellow-400 mr-2 flex-shrink-0"></span>
            {{ config('app.name', 'SIMRS') }}
        </h2>
    </div>

    <!-- Sidebar Search -->
    <div class="px-4 py-4 border-b border-slate-800 flex-shrink-0">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" id="sidebar-search" class="block w-full p-2.5 pl-10 text-sm text-slate-300 bg-slate-950 border border-slate-700 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-slate-500 transition-colors shadow-inner" placeholder="Cari menu...">
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <div class="flex-1 overflow-y-auto py-4 px-4">
        <nav class="space-y-1">
            
            <!-- Dashboard Menu -->
            <a href="{{ route('dashboard') }}" class="flex items-center w-full px-3 py-2.5 mb-3 text-sm font-semibold text-white bg-slate-800 rounded-md hover:bg-blue-600 transition-colors select-none border border-slate-700 hover:border-blue-500 shadow-sm group">
                <svg class="w-5 h-5 mr-3 text-blue-400 group-hover:text-white transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="tracking-wide">Dashboard Utama</span>
            </a>

            @if(isset($moduls) && is_array($moduls) ? count($moduls) > 0 : $moduls->count() > 0)
                @foreach($moduls as $modul)
                    <details class="group mb-3 modul-item">
                        <summary class="flex items-start justify-between w-full px-3 py-2.5 text-sm font-semibold text-white bg-slate-800 rounded-md hover:bg-blue-600 cursor-pointer list-none transition-colors select-none border border-slate-700 hover:border-blue-500 shadow-sm">
                            <span class="flex items-start tracking-wide mt-0.5">
                                <svg class="w-5 h-5 mr-3 text-blue-400 group-hover:text-white transition-colors flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                <span class="break-words text-left modul-name">{{ $modul->nama_modul }}</span>
                            </span>
                            <span class="transition-transform duration-300 group-open:rotate-180 flex-shrink-0 mt-0.5 ml-2">
                                <svg fill="none" height="18" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="18"><path d="M6 9l6 6 6-6"></path></svg>
                            </span>
                        </summary>
                        <div class="mt-2 space-y-3 px-2 mb-4 bg-slate-900 rounded-b-md">
                            @foreach($modul->menus as $menu)
                                <details class="group/menu menu-item">
                                    <summary class="flex items-start justify-between w-full px-2 py-1.5 text-xs font-bold text-slate-300 hover:text-yellow-400 uppercase tracking-wider cursor-pointer list-none transition-colors select-none">
                                        <span class="break-words text-left pr-2 mt-0.5 menu-name">{{ $menu->nama_menu }}</span>
                                        <span class="transition-transform duration-300 group-open/menu:rotate-180 text-slate-400 group-hover/menu:text-yellow-400 flex-shrink-0 mt-0.5">
                                            <svg fill="none" height="14" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" width="14"><path d="M6 9l6 6 6-6"></path></svg>
                                        </span>
                                    </summary>
                                    <div class="mt-2 space-y-1 pl-3 border-l-2 border-slate-700 ml-1">
                                        @php $subMenusList = $menu->subMenus ?? $menu->sub_menus ?? []; @endphp
                                        @foreach($subMenusList as $subMenu)
                                            <a href="{{ $subMenu->file_sub_menu == '#' ? '#' : url($subMenu->file_sub_menu) }}" class="block px-3 py-1.5 text-sm text-slate-400 hover:text-white hover:bg-slate-800 transition-colors border-l-2 border-transparent hover:border-blue-500 rounded-r-md break-words submenu-item">
                                                {{ $subMenu->nama_sub_menu }}
                                            </a>
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    </details>
                @endforeach
            @else
                <p class="text-sm text-slate-500 px-3 italic">Tidak ada menu tersedia.</p>
            @endif
        </nav>
    </div>
</aside>
