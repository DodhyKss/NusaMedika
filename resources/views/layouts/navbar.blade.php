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

        <!-- Cek Kecepatan Internet -->
        <div class="relative" id="speedtest-wrap">
            <button id="speedtest-btn" type="button" title="Cek Kecepatan Internet" class="relative p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                <svg id="speedtest-icon" class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
            </button>

            <div id="speedtest-popover" class="hidden absolute right-0 top-full mt-2 w-64 bg-white rounded-xl border border-slate-200 shadow-lg p-4 z-50">
                <p class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Kecepatan Internet</p>
                <div id="speedtest-status" class="hidden text-sm text-slate-500 flex items-center gap-2">
                    <span class="inline-block w-3 h-3 border-2 border-slate-300 border-t-blue-500 rounded-full animate-spin"></span>
                    Menguji kecepatan...
                </div>
                <div id="speedtest-result" class="hidden">
                    <p class="text-2xl font-extrabold text-slate-900" id="speedtest-mbps">--</p>
                    <p class="text-xs text-slate-500 mt-0.5">Estimasi: <span id="speedtest-est">--</span> · Jaringan: <span id="speedtest-net">--</span></p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center gap-3 text-[11px] font-medium text-slate-500">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Bagus (≥10)</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>Lambat (2-10)</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>Jelek (&lt;2)</span>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <div class="w-px h-8 bg-slate-200 mx-2"></div>

        <!-- User Profile -->
        @php
            $profesiName = '-';
            $profesiId = \App\Helpers\AksesEhr::profesiId();
            if ($profesiId) {
                $profesiName = \Illuminate\Support\Facades\DB::table('profesi')->where('profesi_id', $profesiId)->value('nama_profesi') ?? '-';
            }
        @endphp
        <div class="flex items-center gap-3">
            <div class="hidden sm:block text-right">
                <p class="text-sm font-semibold text-slate-800 leading-tight">
                    {{ Auth::user()->nama_pegawai ?? Auth::user()->user_name }}
                </p>
                <p class="text-[11px] text-slate-400 font-medium">{{ $profesiName }}</p>
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

<script>
    (function () {
        const wrap = document.getElementById('speedtest-wrap');
        const btn = document.getElementById('speedtest-btn');
        const popover = document.getElementById('speedtest-popover');
        if (!wrap || !btn || !popover) return;

        const status = document.getElementById('speedtest-status');
        const result = document.getElementById('speedtest-result');
        const mbpsEl = document.getElementById('speedtest-mbps');
        const estEl = document.getElementById('speedtest-est');
        const netEl = document.getElementById('speedtest-net');
        const icon = document.getElementById('speedtest-icon');

        function warnaIcon(mbps) {
            icon.classList.remove('text-slate-400', 'text-emerald-500', 'text-amber-500', 'text-red-500');
            if (mbps >= 10) {
                icon.classList.add('text-emerald-500');
            } else if (mbps >= 2) {
                icon.classList.add('text-amber-500');
            } else {
                icon.classList.add('text-red-500');
            }
        }

        btn.addEventListener('click', async function () {
            const isHidden = popover.classList.contains('hidden');
            popover.classList.toggle('hidden');

            if (isHidden) {
                await runTest();
            }
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) {
                popover.classList.add('hidden');
            }
        });

        async function runTest() {
            status.classList.remove('hidden');
            result.classList.add('hidden');

            const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            estEl.textContent = conn && conn.downlink ? conn.downlink + ' Mbps' : '-';
            netEl.textContent = conn && conn.effectiveType ? conn.effectiveType : '-';

            const url = '/img/logo.png';
            let totalBits = 0;
            let totalTime = 0;
            const attempts = 3;

            for (let i = 0; i < attempts; i++) {
                try {
                    const start = performance.now();
                    const res = await fetch(url + '?t=' + Date.now() + '_' + i, { cache: 'no-store' });
                    const buf = await res.arrayBuffer();
                    const end = performance.now();
                    totalBits += buf.byteLength * 8;
                    totalTime += (end - start) / 1000;
                } catch (e) {
                    // lewati percobaan yang gagal
                }
            }

            const mbps = totalTime > 0 ? (totalBits / totalTime / 1e6) : 0;
            mbpsEl.textContent = mbps > 0 ? mbps.toFixed(1) + ' Mbps' : 'Gagal';
            warnaIcon(mbps);
            status.classList.add('hidden');
            result.classList.remove('hidden');
        }

        runTest();
    })();
</script>
