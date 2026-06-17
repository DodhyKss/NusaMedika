<nav class="sticky top-0 z-30 flex items-center justify-between w-full h-16 min-h-16 px-6 bg-white border-b border-slate-200 flex-shrink-0">
    <div class="flex items-center">
        <button id="sidebar-toggle" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg focus:outline-none transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <div class="flex items-center gap-2">
        <!-- Notification Bell -->
        <button class="relative p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        </button>

        <!-- Divider -->
        <div class="w-px h-8 bg-slate-200 mx-2"></div>

        <!-- User Profile -->
        <div class="flex items-center gap-3">
            <div class="hidden sm:block text-right">
                <p class="text-sm font-semibold text-slate-800 leading-tight">
                    {{ Auth::user()->nama_pegawai ?? Auth::user()->user_name }}
                </p>
                <p class="text-[11px] text-slate-400 font-medium">Administrator</p>
            </div>
            <div class="relative">
                <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold">
                    {{ strtoupper(substr(Auth::user()->nama_pegawai ?? Auth::user()->user_name, 0, 1)) }}
                </div>
                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full"></span>
            </div>
            
            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Keluar" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </div>
</nav>
