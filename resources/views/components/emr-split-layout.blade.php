@props([
    'titleRiwayat' => 'Riwayat',
    'titleForm' => 'Form',
    'subtitleForm' => '',
    'historyGrouped' => [],
    'routeName' => '',
    'routeUrl' => '',
    'registrasiDetailId' => '',
    'formAction' => '',
    'isEdit' => false,
    'deleteAction' => '',
    'emrId' => '',
    'printUrl' => '',
    'isView' => false,
    'canCreate' => true,
    'canRead' => true,
    'canUpdate' => true,
    'canDelete' => true
])

@php
    $getLink = function($id) use ($routeName, $routeUrl) {
        if ($routeName) {
            return route($routeName, ['registrasi_detail_id' => $id]);
        }
        if ($routeUrl) {
            return rtrim($routeUrl, '/') . '/' . $id;
        }
        return '#';
    };
@endphp

<div class="h-screen flex flex-col md:flex-row w-full overflow-hidden">
    
    <!-- Panel Kiri: List Riwayat -->
    <div id="panel-kiri" class="relative z-30 w-full md:w-1/3 border-r border-slate-200 bg-white flex flex-col h-full transition-all duration-300">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center relative z-20">
            <h2 class="font-bold text-slate-700">{{ $titleRiwayat }}</h2>
            <div class="flex items-center gap-2">
                <!-- Tombol Refresh -->
                <a href="{{ $getLink($registrasiDetailId) }}" onclick="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Memuat Data...';" title="Refresh" class="inline-flex items-center justify-center p-2 text-slate-500 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-700 rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
                <!-- Dropdown History Kunjungan -->
                <div class="relative group">
                    <button type="button" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        History
                    </button>
                    <!-- Menu Dropdown -->
                    <div class="absolute left-0 mt-1 w-80 max-h-80 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 p-3.5 flex flex-col gap-3">
                        @forelse($historyGrouped ?? [] as $date => $units)
                            @php
                                $first_reg_det_id = reset($units);
                                $hiddenClass = $loop->index >= 3 ? 'hidden history-hidden-item' : '';
                            @endphp
                            <div class="history-item {{ $hiddenClass }} flex flex-col gap-1.5 border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                                <a href="{{ $getLink($first_reg_det_id) }}" onclick="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Memuat Riwayat...';" class="inline-flex items-center gap-1.5 self-start px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors">
                                    <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
                                </a>
                                <div class="flex flex-wrap gap-1.5 mt-0.5">
                                    @foreach($units as $unit_name => $reg_det_id)
                                        <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ strtoupper($unit_name) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-xs text-slate-500 text-center py-2">Tidak ada riwayat kunjungan</div>
                        @endforelse
                        @if(count($historyGrouped ?? []) > 3)
                            <div id="loadMoreHistoryContainer" class="text-center mt-1 pt-2 border-t border-blue-200/50">
                                <span onclick="loadMoreHistory()" class="text-xs font-semibold text-slate-500 hover:text-slate-700 cursor-pointer transition-colors">Load More</span>
                            </div>
                            <script>
                                function loadMoreHistory() {
                                    let hiddenItems = document.querySelectorAll('.history-hidden-item');
                                    let count = 0;
                                    hiddenItems.forEach(function(item) {
                                        if (count < 3) {
                                            item.classList.remove('hidden', 'history-hidden-item');
                                            count++;
                                        }
                                    });
                                    if (document.querySelectorAll('.history-hidden-item').length === 0) {
                                        document.getElementById('loadMoreHistoryContainer').style.display = 'none';
                                    }
                                }
                            </script>
                        @endif
                    </div>
                </div>

                @if($canCreate)
                <a href="{{ $getLink($registrasiDetailId) }}" onclick="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Memuat Data...';" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Baru
                </a>
                @endif
                
                <button type="button" onclick="togglePanel('kiri')" title="Maximize/Minimize" class="inline-flex items-center justify-center p-1.5 text-slate-500 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-700 rounded-lg transition-colors shadow-sm ml-1">
                    <svg id="icon-maximize-kiri" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                </button>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-3 space-y-3">
            {{ $listRiwayat ?? '' }}
        </div>
    </div>
    
    <!-- Panel Kanan: Form -->
    <div id="panel-kanan" class="w-full md:w-2/3 bg-slate-50 flex flex-col h-full overflow-y-auto transition-all duration-300">
        <form id="form-kanan" action="{{ $formAction }}" method="POST" class="p-6 max-w-4xl w-full mx-auto transition-all duration-300" onsubmit="document.getElementById('save_loader').classList.remove('hidden');">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">{{ $titleForm }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ $subtitleForm }}</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <button type="button" onclick="togglePanel('kanan')" title="Maximize/Minimize" class="inline-flex items-center justify-center p-2 text-slate-500 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-700 rounded-lg transition-colors shadow-sm">
                        <svg id="icon-maximize-kanan" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                    </button>
                    
                    @if($isEdit && $deleteAction && $canDelete)
                        <button type="button" onclick="confirmDelete('{{ $emrId }}')" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors border border-red-200">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Batalkan
                        </button>
                    @endif
                </div>
            </div>

            <!-- Isi Form Custom Disini -->
            {{ $slot }}

            <div class="mt-8 pt-5 border-t border-slate-200 flex justify-end gap-3">
                @if($isView)
                    @if($canRead && $printUrl)
                        <button type="button" onclick="window.open('{{ $printUrl }}', '_blank')" class="inline-flex items-center px-6 py-2.5 bg-white border border-slate-300 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Print
                        </button>
                    @endif
                    <a href="{{ $getLink($registrasiDetailId) }}" class="inline-flex items-center px-6 py-2.5 bg-slate-800 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition-all shadow-sm">
                        Tutup
                    </a>
                @elseif($canCreate || $canUpdate)
                    <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-500/20 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan {{ $isEdit ? 'Perubahan' : 'Data' }}
                    </button>
                @else
                    <a href="{{ $getLink($registrasiDetailId) }}" class="inline-flex items-center px-6 py-2.5 bg-slate-800 text-white text-sm font-semibold rounded-lg hover:bg-slate-700 transition-all shadow-sm">
                        Tutup
                    </a>
                @endif
            </div>
        </form>
        
        <!-- Delete Form (Hidden) -->
        @if($isEdit && $deleteAction && $canDelete)
            <form id="delete-form-{{ $emrId }}" action="{{ $deleteAction }}" method="POST" class="hidden" onsubmit="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Membatalkan Data...';">
                @csrf
                @method('DELETE')
            </form>
        @endif
    </div>
</div>

<!-- Loading Overlay -->
<div id="save_loader" class="fixed inset-0 flex flex-col items-center justify-center bg-slate-900/40 backdrop-blur-sm z-50 hidden">
    <div class="w-12 h-12 border-4 border-white/30 border-t-white rounded-full animate-spin mb-4 shadow-lg"></div>
    <div class="bg-white px-6 py-2 rounded-full shadow-lg font-semibold text-slate-700 animate-pulse">
        Menyimpan Data...
    </div>
</div>

<!-- Script Panel & SweetAlert -->
<script>
    function confirmDelete(emrId) {
        Swal.fire({
            title: 'Batalkan EMR?',
            text: "Data rekam medis ini akan dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Batalkan!',
            cancelButtonText: 'Kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('save_loader').classList.remove('hidden');
                document.querySelector('#save_loader div:nth-child(2)').innerText = 'Membatalkan Data...';
                document.getElementById('delete-form-' + emrId).submit();
            }
        });
    }

    let panelState = 'split'; // split, kiri-full, kanan-full
    
    function togglePanel(target) {
        const panelKiri = document.getElementById('panel-kiri');
        const panelKanan = document.getElementById('panel-kanan');
        const iconKiri = document.getElementById('icon-maximize-kiri');
        const iconKanan = document.getElementById('icon-maximize-kanan');
        const formKanan = document.getElementById('form-kanan');
        
        if (target === 'kiri') {
            panelState = panelState === 'kiri-full' ? 'split' : 'kiri-full';
        } else if (target === 'kanan') {
            panelState = panelState === 'kanan-full' ? 'split' : 'kanan-full';
        }
        
        if (panelState === 'split') {
            panelKiri.style.display = 'flex';
            panelKanan.style.display = 'flex';
            panelKiri.className = 'relative z-30 w-full md:w-1/3 border-r border-slate-200 bg-white flex flex-col h-full transition-all duration-300';
            panelKanan.className = 'w-full md:w-2/3 bg-slate-50 flex flex-col h-full overflow-y-auto transition-all duration-300';
            if (formKanan) {
                formKanan.classList.remove('max-w-full');
                formKanan.classList.add('max-w-4xl');
            }
            if(iconKiri) iconKiri.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>';
            if(iconKanan) iconKanan.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>';
        } else if (panelState === 'kiri-full') {
            panelKanan.style.display = 'none';
            panelKiri.style.display = 'flex';
            panelKiri.className = 'relative z-30 w-full bg-white flex flex-col h-full transition-all duration-300';
            if(iconKiri) iconKiri.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v4m0 0H5m4 0L3 3m6 18v-4m0 0H5m4 0l-2 2m6-16V3m0 4h4m-4 0l2-2m-6 16v-4m0 0h4m-4 0l2 2"></path>';
        } else if (panelState === 'kanan-full') {
            panelKiri.style.display = 'none';
            panelKanan.style.display = 'flex';
            panelKanan.className = 'w-full bg-slate-50 flex flex-col h-full overflow-y-auto transition-all duration-300';
            if (formKanan) {
                formKanan.classList.remove('max-w-4xl');
                formKanan.classList.add('max-w-full');
            }
            if(iconKanan) iconKanan.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v4m0 0H5m4 0L3 3m6 18v-4m0 0H5m4 0l-2 2m6-16V3m0 4h4m-4 0l2-2m-6 16v-4m0 0h4m-4 0l2 2"></path>';
        }
    }
</script>
