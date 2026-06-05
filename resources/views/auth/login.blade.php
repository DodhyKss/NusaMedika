<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name', 'SIMRS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800 relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute top-0 left-0 w-full h-96 bg-slate-900 shadow-xl" style="clip-path: polygon(0 0, 100% 0, 100% 100%, 0 80%);"></div>
    <div class="absolute top-20 right-20 w-64 h-64 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse"></div>
    <div class="absolute top-40 left-20 w-72 h-72 bg-yellow-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse" style="animation-delay: 2s;"></div>
    
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative z-10">
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <h1 class="text-4xl font-extrabold text-white tracking-tight flex items-center justify-center">
                <span class="w-3 h-3 rounded-full bg-yellow-400 mr-3 flex-shrink-0"></span>
                {{ config('app.name', 'SIMRS') }}
            </h1>
            <p class="mt-3 text-sm text-slate-300 font-medium">
                Sistem Informasi Manajemen Rumah Sakit
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-10 px-4 shadow-2xl sm:rounded-2xl sm:px-10 border-t-4 border-blue-500 relative">
                <!-- Small green accent -->
                <div class="absolute -top-4 right-8 w-8 h-8 bg-green-500 rounded-full border-4 border-white shadow-sm"></div>

                <form class="space-y-6" action="{{ route('login') }}" method="POST">
                    @csrf
                    
                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700 font-medium">
                                        {{ $errors->first() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label for="user_name" class="block text-sm font-semibold text-slate-700">
                            Username
                        </label>
                        <div class="mt-2">
                            <input id="user_name" name="user_name" type="text" autocomplete="username" required value="{{ old('user_name') }}"
                                class="appearance-none block w-full px-4 py-3 border border-slate-200 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-shadow">
                        </div>
                    </div>

                    <div>
                        <label for="user_password" class="block text-sm font-semibold text-slate-700">
                            Password
                        </label>
                        <div class="mt-2">
                            <input id="user_password" name="user_password" type="password" autocomplete="current-password" required
                                class="appearance-none block w-full px-4 py-3 border border-slate-200 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-shadow">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-slate-900 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all duration-200">
                            Masuk ke Dashboard
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="mt-8 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} {{ config('app.name', 'SIMRS') }} Hospital System. <br class="sm:hidden">Hak Cipta Dilindungi.
            </div>
        </div>
    </div>
</body>
</html>
