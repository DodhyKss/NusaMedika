<div id="page-loader" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-50 transition-opacity duration-400">
    <div class="flex flex-col items-center gap-4">
        <div class="relative w-10 h-10">
            <div class="absolute inset-0 border-[3px] border-slate-200 rounded-full"></div>
            <div class="absolute inset-0 border-[3px] border-blue-600 rounded-full border-t-transparent animate-spin"></div>
        </div>
        <p class="text-sm font-medium text-slate-400">Memuat...</p>
    </div>
</div>

<script>
    (function() {
        let hidden = false;
        function hide() {
            if (hidden) return;
            hidden = true;
            const el = document.getElementById('page-loader');
            if (!el) return;
            el.style.opacity = '0';
            setTimeout(() => el.style.display = 'none', 400);
        }
        window.addEventListener('load', () => setTimeout(hide, 200));
        setTimeout(hide, 5000);
    })();
</script>
