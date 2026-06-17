<aside class="flex flex-col w-full h-screen bg-slate-800 z-20 overflow-hidden shadow-xl border-r border-slate-700/50">
    <!-- Sidebar Header -->
    <div class="h-16 flex items-center px-5 border-b border-slate-700 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-600 rounded-lg shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
            </div>
            <span class="text-lg font-bold text-white tracking-tight">{{ config('app.name', 'SIMRS') }}</span>
        </div>
    </div>

    <!-- Sidebar Search -->
    <div class="px-4 py-4 flex-shrink-0">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" id="sidebar-search" class="block w-full py-2.5 pl-10 pr-3 text-sm text-slate-100 bg-slate-700/50 border border-slate-600/80 rounded-lg focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500/40 placeholder-slate-400 transition-all outline-none" placeholder="Cari menu...">
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <div class="flex-1 overflow-y-auto px-3 pb-6 sidebar-scroll">
        <nav class="space-y-1">
            
            <!-- Dashboard Menu -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 w-full px-4 py-2.5 mb-2 text-[13px] font-bold text-white bg-gradient-to-r from-blue-600 to-blue-500 rounded-lg shadow-md hover:from-blue-500 hover:to-blue-400 hover:shadow-lg transition-all border border-blue-500/50">
                <svg class="w-[18px] h-[18px] text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Dashboard
            </a>

            @if(isset($moduls) && is_array($moduls) ? count($moduls) > 0 : $moduls->count() > 0)
                <!-- Section Label -->
                <div class="pt-5 pb-2 px-3">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Modul</p>
                </div>

                @foreach($moduls as $modul)
                    <details class="group modul-item border-b border-slate-700 last:border-b-0 pb-1.5 mb-1.5">
                        <summary class="flex items-center justify-between w-full px-3 py-2.5 text-[13px] font-semibold text-slate-200 rounded-lg hover:bg-slate-700 hover:text-white cursor-pointer list-none transition-colors select-none">
                            <span class="flex items-center gap-3 min-w-0">
                                <svg class="w-[18px] h-[18px] text-slate-500 group-hover:text-slate-400 flex-shrink-0 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                <span class="truncate modul-name">{{ $modul->nama_modul }}</span>
                            </span>
                            <svg class="w-4 h-4 text-slate-500 transition-transform duration-200 group-open:rotate-180 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </summary>

                        <div class="mt-1 ml-4 pl-4 border-l border-slate-600/60 space-y-0.5 pb-2">
                            @foreach($modul->menus as $menu)
                                <details class="group/menu menu-item border-b border-slate-700/60 last:border-b-0 pb-1 mb-1">
                                    <summary class="flex items-center justify-between w-full px-3 py-2 text-[13px] font-medium text-slate-300 hover:text-slate-100 rounded-lg hover:bg-slate-700/60 cursor-pointer list-none transition-colors select-none">
                                        <span class="truncate menu-name">{{ $menu->nama_menu }}</span>
                                        <svg class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200 group-open/menu:rotate-180 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </summary>
                                    <div class="mt-1 ml-3 pl-3 border-l border-slate-600/40 space-y-0.5 pb-1">
                                        @php $subMenusList = $menu->subMenus ?? $menu->sub_menus ?? []; @endphp
                                        @foreach($subMenusList as $subMenu)
                                            <a href="{{ $subMenu->file_sub_menu == '#' ? '#' : url($subMenu->file_sub_menu) }}" class="block px-3 py-2 text-xs font-medium text-slate-300 hover:text-white hover:bg-blue-600 rounded-lg transition-colors submenu-item border-b border-slate-600/30 last:border-b-0">
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
                <div class="px-3 py-8 text-center">
                    <p class="text-xs text-slate-500">Belum ada modul tersedia.</p>
                </div>
            @endif
        </nav>
    </div>

    <style>
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background-color: #475569; border-radius: 20px; }
        .sidebar-scroll:hover::-webkit-scrollbar-thumb { background-color: #64748b; }
    </style>
</aside>
