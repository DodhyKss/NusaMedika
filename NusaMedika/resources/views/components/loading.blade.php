<div id="page-loader" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-[3px] transition-all duration-300 opacity-100">
    <div class="flex flex-col items-center gap-3.5 transform transition-transform duration-300 scale-100">
        <div class="relative w-12 h-12 flex items-center justify-center">
            <div class="absolute inset-0 border-4 border-white/20 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-blue-500 rounded-full border-t-transparent animate-spin"></div>
            <div class="w-2.5 h-2.5 bg-blue-500 rounded-full animate-pulse"></div>
        </div>
        <div class="text-center">
            <p class="text-xs font-bold text-white tracking-wide uppercase">Memuat Halaman</p>
            <p class="text-[11px] text-slate-200 mt-0.5">Mohon tunggu sebentar...</p>
        </div>
    </div>
</div>

<script>
    (function() {
        const loader = document.getElementById('page-loader');
        if (!loader) return;

        let hideTimeout;
        let isNavigating = false;

        window.showPageLoader = function(lockNavigation = false) {
            if (hideTimeout) clearTimeout(hideTimeout);
            if (lockNavigation) isNavigating = true;
            loader.style.display = 'flex';
            loader.offsetHeight;
            loader.style.opacity = '1';
            const innerBox = loader.querySelector('div');
            if (innerBox) innerBox.style.transform = 'scale(1)';
        };

        window.hidePageLoader = function(force = false) {
            // JAMINAN MUTLAK: Jangan pernah sembunyikan animasi jika browser masih dalam proses loading (readyState !== 'complete')
            // atau jika sedang dalam proses navigasi/pindah halaman (kecuali dipaksa)
            if (!force && (document.readyState !== 'complete' || isNavigating)) {
                return;
            }

            loader.style.opacity = '0';
            const innerBox = loader.querySelector('div');
            if (innerBox) innerBox.style.transform = 'scale(0.95)';
            if (hideTimeout) clearTimeout(hideTimeout);
            hideTimeout = setTimeout(() => {
                loader.style.display = 'none';
            }, 300);
        };

        // Ketika browser BENAR-BENAR selesai memuat seluruh aset halaman (HTML, CSS, Gambar, JS, Select2)
        window.addEventListener('load', () => {
            isNavigating = false;
            setTimeout(() => window.hidePageLoader(true), 150);
        });

        // Jika saat script ini dijalankan ternyata browser sudah selesai loading sebelumnya
        if (document.readyState === 'complete') {
            setTimeout(() => window.hidePageLoader(true), 150);
        }

        // Sembunyikan jika user tekan tombol Back/Forward browser (BFCache)
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) {
                isNavigating = false;
                window.hidePageLoader(true);
            }
        });

        // Tampilkan saat browser mulai reload / pindah URL
        window.addEventListener('beforeunload', () => {
            window.showPageLoader(true);
        });

        // Intercept klik pada link agar animasi muncul dan TERKUNCI sampai halaman baru selesai di-load browser
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            const target = link.getAttribute('target');
            if (!href || 
                href.startsWith('#') || 
                href.startsWith('javascript:') || 
                (target && target !== '_self') || 
                link.hasAttribute('data-no-loader') || 
                link.closest('[data-no-loader]') || 
                e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
                return;
            }

            window.showPageLoader(true);
        });

        // Intercept submit form
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const target = form.getAttribute('target');
            if (form.hasAttribute('data-no-loader') || form.closest('[data-no-loader]')) {
                return;
            }
            if (!target || target === '_self') {
                window.showPageLoader(true);
            }
        });

        // Fallback pengaman darurat jika koneksi putus (maksimal 25 detik)
        setTimeout(() => {
            if (isNavigating || document.readyState !== 'complete') {
                isNavigating = false;
                window.hidePageLoader(true);
            }
        }, 25000);
    })();
</script>
