<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('app.name', 'SIMRS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800">
    <div class="min-h-screen flex flex-col justify-center py-12 px-4">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm text-center">
            <!-- Logo -->
            <div class="inline-flex items-center justify-center p-3 bg-blue-600 rounded-xl shadow-sm mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ config('app.name', 'SIMRS') }}</h1>
            <p class="mt-1 text-sm text-slate-500">Sistem Informasi Manajemen Rumah Sakit</p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-sm">
            <div class="bg-white py-8 px-6 shadow-sm rounded-xl border border-slate-200">
                
                @if ($errors->any())
                    <div class="flex items-start gap-3 bg-red-50 border border-red-200 p-3.5 mb-6 rounded-lg">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-red-700 font-medium">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form class="space-y-5" action="{{ route('login') }}" method="POST">
                    @csrf
                    
                    <div>
                        <label for="user_name" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Username</label>
                        <input id="user_name" name="user_name" type="text" autocomplete="username" required value="{{ old('user_name') }}"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    </div>

                    <div>
                        <label for="user_password" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Password</label>
                        <input id="user_password" name="user_password" type="password" autocomplete="current-password" required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors duration-200 active:scale-[0.98]">
                        Masuk
                    </button>
                </form>
            </div>
            
            <p class="mt-6 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} {{ config('app.name', 'SIMRS') }} — Hak Cipta Dilindungi
            </p>
        </div>
    </div>
</body>
</html>
