<nav class="sticky top-0 z-30 flex items-center justify-between w-full h-16 px-6 bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-sm">
    <div class="flex items-center flex-1">
        <button id="sidebar-toggle" class="p-2 mr-4 text-slate-500 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <div class="flex items-center space-x-4 md:space-x-6">
        <!-- Notification Bell -->
        <button class="relative p-2 text-slate-400 hover:text-blue-600 transition-colors">
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
        </button>

        <!-- User Profile Menu -->
        <div class="flex items-center space-x-3 border-l border-slate-200 pl-4 md:pl-6">
            <div class="flex flex-col text-right hidden sm:block">
                <span class="text-sm font-bold text-slate-800 leading-tight">
                    {{ Auth::user()->nama_pegawai ?? Auth::user()->user_name }}
                </span>
                <span class="text-xs text-slate-500 font-medium">Administrator</span>
            </div>
            <div class="h-9 w-9 rounded-full bg-gradient-to-tr from-blue-600 to-blue-400 text-white flex items-center justify-center font-bold text-sm shadow-md border-2 border-white ring-2 ring-blue-50">
                {{ strtoupper(substr(Auth::user()->nama_pegawai ?? Auth::user()->user_name, 0, 1)) }}
            </div>
            
            <!-- Logout Button Minimalist -->
            <form method="POST" action="{{ route('logout') }}" class="ml-2">
                @csrf
                <button type="submit" title="Keluar" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                </button>
            </form>
        </div>
    </div>
</nav>
