<footer class="mt-auto border-t border-slate-200 bg-white">
    <div class="px-6 py-3 flex flex-col md:flex-row justify-between items-center text-xs text-slate-400">
        <p>&copy; {{ date('Y') }} <span class="font-semibold text-slate-500">{{ config('app.name', 'SIMRS') }}</span> — MediTech Team</p>
        <div class="mt-1 md:mt-0 flex gap-4">
            <a href="#" class="hover:text-blue-600 transition-colors">Bantuan</a>
            <a href="#" class="hover:text-blue-600 transition-colors">Panduan</a>
            <a href="#" class="hover:text-blue-600 transition-colors">Dukungan IT</a>
        </div>
    </div>
</footer>
