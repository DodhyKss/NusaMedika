<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('app.name', 'SIMRS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans antialiased text-slate-800">
    <div class="min-h-screen flex w-full">
        <!-- Left Side: Hero Image & Branding -->
        <div class="hidden lg:flex w-1/2 relative bg-blue-900 overflow-hidden">
            <!-- Background Image -->
            <img src="https://images.unsplash.com/photo-1538108149393-fbbd81895907?auto=format&fit=crop&q=80&w=2000" 
                 alt="Hospital Background" 
                 class="absolute inset-0 w-full h-full object-cover opacity-100 mix-blend-overlay scale-105 animate-[pulse_20s_ease-in-out_infinite_alternate]" />
            
            <!-- Solid Overlay -->
            <div class="absolute inset-0 bg-blue-950/80"></div>

            <!-- Content -->
            <div class="relative w-full h-full flex flex-col justify-between p-16 z-10">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-white/10 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/20 shadow-xl">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    </div>
                    <span class="text-2xl font-bold text-white tracking-wide">{{ config('app.name', 'SIMRS') }}</span>
                </div>

                <div class="max-w-md">
                    <h2 class="text-4xl font-extrabold text-white leading-tight mb-6">
                        Layanan Kesehatan yang Lebih Baik & Terpadu
                    </h2>
                    <p class="text-blue-100 text-lg leading-relaxed font-light">
                        Sistem Informasi Manajemen Rumah Sakit (SIMRS) yang dirancang untuk mempercepat pelayanan dan mempermudah manajemen data medis secara real-time.
                    </p>
                </div>

                <div class="flex items-center gap-4 text-sm text-blue-200">
                    <div class="flex -space-x-3">
                        <img class="w-10 h-10 rounded-full border-2 border-blue-900 object-cover" src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&q=80&w=100" alt="Doctor 1">
                        <img class="w-10 h-10 rounded-full border-2 border-blue-900 object-cover" src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&q=80&w=100" alt="Doctor 2">
                        <img class="w-10 h-10 rounded-full border-2 border-blue-900 object-cover" src="https://images.unsplash.com/photo-1594824432258-005085e93340?auto=format&fit=crop&q=80&w=100" alt="Doctor 3">
                    </div>
                    <p>Dipercaya oleh <strong>500+</strong> tenaga medis</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-24 bg-slate-50/50">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden flex justify-center mb-8">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-600/30">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    </div>
                </div>

                <div class="text-center lg:text-left mb-10">
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight mb-2">Selamat Datang</h1>
                    <p class="text-slate-500">Silakan masuk ke akun Anda untuk melanjutkan.</p>
                </div>

                @if ($errors->any())
                    <div class="animate-pulse flex items-start gap-3 bg-red-50 border border-red-200 p-4 mb-8 rounded-xl shadow-sm">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-red-700 font-medium">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form class="space-y-6" action="{{ route('login') }}" method="POST">
                    @csrf
                    
                    <div class="group relative">
                        <label for="user_name" class="block text-sm font-medium text-slate-700 mb-2 transition-colors group-focus-within:text-blue-600">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input id="user_name" name="user_name" type="text" autocomplete="username" required value="{{ old('user_name') }}"
                                class="block w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none placeholder-slate-400 shadow-sm" placeholder="Masukkan username Anda">
                        </div>
                    </div>

                    <div class="group relative">
                        <div class="flex items-center justify-between mb-2">
                            <label for="user_password" class="block text-sm font-medium text-slate-700 transition-colors group-focus-within:text-blue-600">Password</label>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="user_password" name="user_password" type="password" autocomplete="current-password" required
                                class="block w-full pl-11 pr-4 py-3.5 bg-white border border-slate-200 rounded-xl text-slate-900 text-sm focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none placeholder-slate-400 shadow-sm" placeholder="••••••••">
                        </div>
                    </div>

                    <button type="submit"
                        class="relative w-full flex justify-center items-center gap-2 py-3.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-600/30 hover:shadow-blue-600/40 transition-all duration-300 hover:-translate-y-0.5 active:translate-y-0 overflow-hidden group">
                        <span class="relative z-10">Masuk ke Sistem</span>
                        <svg class="w-4 h-4 relative z-10 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        <div class="absolute inset-0 h-full w-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                    </button>
                </form>

                <p class="mt-12 text-center lg:text-left text-xs text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'SIMRS') }}. All rights reserved.<br>
                    <span class="inline-block mt-1">Sistem Informasi Manajemen Rumah Sakit</span>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
