@extends('layouts.iframe')

@section('content')
<div class="h-screen flex flex-col md:flex-row w-full overflow-hidden">
    
    <!-- Panel Kiri: List SOAP -->
    <div id="panel-kiri" class="relative z-30 w-full md:w-1/3 border-r border-slate-200 bg-white flex flex-col h-full transition-all duration-300">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center relative z-20">
            <h2 class="font-bold text-slate-700">Riwayat SOAP</h2>
            <div class="flex items-center gap-2">
                <!-- Dropdown History Kunjungan (Semua Registrasi Pasien) -->
                <div class="relative group">
                    <button type="button" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        History
                    </button>
                    <!-- Menu Dropdown -->
                    <div class="absolute left-0 mt-1 w-80 max-h-80 overflow-y-auto bg-white border border-slate-200 rounded-xl shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50 p-3.5 flex flex-col gap-3">
                        @forelse($history_grouped ?? [] as $date => $units)
                            @php
                                // Gunakan registrasi_detail_id pertama di hari tersebut
                                $first_reg_det_id = reset($units);
                                // Sembunyikan jika index melebihi 2 (mulai dari 0)
                                $hiddenClass = $loop->index >= 3 ? 'hidden history-hidden-item' : '';
                            @endphp
                            <div class="history-item {{ $hiddenClass }} flex flex-col gap-1.5 border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                                <a href="{{ route('emr.soap.index', ['registrasi_detail_id' => $first_reg_det_id]) }}" onclick="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Memuat Riwayat...';" class="inline-flex items-center gap-1.5 self-start px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 transition-colors">
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
                        @if(count($history_grouped ?? []) > 3)
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

                <a href="{{ route('emr.soap.index', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id]) }}" onclick="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Memuat Data...';" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Baru
                </a>
                
                <button type="button" onclick="togglePanel('kiri')" title="Maximize/Minimize" class="inline-flex items-center justify-center p-1.5 text-slate-500 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-700 rounded-lg transition-colors shadow-sm ml-1">
                    <svg id="icon-maximize-kiri" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                </button>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-3 space-y-3">
            @forelse($riwayat_soap as $item)
                @php
                    $data = json_decode($item->data, true) ?? [];
                    $isActive = isset($edit_soap) && $edit_soap->emr_id == $item->emr_id;
                @endphp
                <div class="group border {{ $isActive ? 'border-blue-400 bg-blue-50 shadow-md' : 'border-slate-200 bg-white hover:border-blue-300 hover:shadow-md' }} rounded-xl p-4 cursor-pointer transition-all relative">
                    <a href="{{ route('emr.soap.index', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $item->emr_id]) }}" onclick="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Memuat Data...';" class="absolute inset-0 z-10"></a>
                    
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="text-xs font-bold text-blue-600">{{ \Carbon\Carbon::parse($item->tgl_jam)->format('d/m/Y H:i') }}</p>
                            <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $item->nama_pegawai ?? 'Dokter' }}</p>
                        </div>
                        
                        @if($isActive)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                Aktif
                            </span>
                        @endif
                    </div>
                    
                    <div class="text-xs text-slate-600 space-y-1 mt-3 line-clamp-3">
                        <p><span class="font-semibold text-slate-700">S:</span> {{ Str::limit($data['s'] ?? '-', 40) }}</p>
                        <p><span class="font-semibold text-slate-700">O:</span> {{ Str::limit($data['o'] ?? '-', 40) }}</p>
                        <p><span class="font-semibold text-slate-700">A:</span> {{ Str::limit($data['a'] ?? '-', 40) }}</p>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-40 text-center opacity-50">
                    <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    <p class="text-sm text-slate-500">Belum ada riwayat SOAP</p>
                </div>
            @endforelse
        </div>
    </div>
    
    <!-- Panel Kanan: Form SOAP -->
    <div id="panel-kanan" class="w-full md:w-2/3 bg-slate-50 flex flex-col h-full overflow-y-auto transition-all duration-300">
        @php
            $isEdit = isset($edit_soap);
            $actionUrl = $isEdit 
                ? route('emr.soap.update', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit_soap->emr_id]) 
                : route('emr.soap.store', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id]);
            
            $formData = $isEdit ? (json_decode($edit_soap->data, true) ?? []) : [];
        @endphp

        <form action="{{ $actionUrl }}" method="POST" class="p-6 max-w-4xl w-full mx-auto" onsubmit="document.getElementById('save_loader').classList.remove('hidden');">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">{{ $isEdit ? 'Edit Form SOAP' : 'Form SOAP Baru' }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ $isEdit ? 'Perbarui rekam medis pasien.' : 'Isi form rekam medis di bawah ini.' }}</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <button type="button" onclick="togglePanel('kanan')" title="Maximize/Minimize" class="inline-flex items-center justify-center p-2 text-slate-500 bg-white border border-slate-300 hover:bg-slate-50 hover:text-slate-700 rounded-lg transition-colors shadow-sm">
                        <svg id="icon-maximize-kanan" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                    </button>
                    
                    @if($isEdit)
                        <button type="button" onclick="confirmDelete('{{ $edit_soap->emr_id }}')" class="inline-flex items-center px-3 py-2 text-sm font-semibold text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors border border-red-200">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Batalkan EMR
                        </button>
                    @endif
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-5 space-y-6">
                    
                    <!-- Subjective -->
                    <div>
                        <label for="subjective" class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-xs">S</span>
                            Subjective (Subyektif)
                        </label>
                        <textarea id="subjective" name="subjective" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors text-sm" placeholder="Keluhan utama, riwayat penyakit, keluhan tambahan...">{{ old('subjective', $formData['s'] ?? '') }}</textarea>
                    </div>

                    <!-- Objective -->
                    <div>
                        <label for="objective" class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs">O</span>
                            Objective (Obyektif)
                        </label>
                        <textarea id="objective" name="objective" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors text-sm" placeholder="Hasil pemeriksaan fisik, tanda vital, hasil lab...">{{ old('objective', $formData['o'] ?? '') }}</textarea>
                    </div>

                    <!-- Assessment -->
                    <div>
                        <label for="assessment" class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-700 font-bold text-xs">A</span>
                            Assessment (Asesmen)
                        </label>
                        <textarea id="assessment" name="assessment" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-colors text-sm" placeholder="Diagnosis kerja, diagnosis banding...">{{ old('assessment', $formData['a'] ?? '') }}</textarea>
                    </div>

                    <!-- Plan -->
                    <div>
                        <label for="plan" class="block text-sm font-semibold text-slate-700 mb-1.5 flex items-center gap-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs">P</span>
                            Plan (Planning)
                        </label>
                        <textarea id="plan" name="plan" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-colors text-sm" placeholder="Rencana tindakan, terapi obat, edukasi...">{{ old('plan', $formData['p'] ?? '') }}</textarea>
                    </div>

                </div>
                
                <div class="px-5 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">
                    <button type="reset" class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">Reset</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Simpan EMR
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Delete Form (Hidden) -->
        @if($isEdit)
            <form id="delete-form-{{ $edit_soap->emr_id }}" action="{{ route('emr.soap.destroy', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $edit_soap->emr_id]) }}" method="POST" class="hidden" onsubmit="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Membatalkan Data...';">
                @csrf
                @method('DELETE')
            </form>
        @endif
        
    </div>
</div>

<!-- Loading Overlay (Saat Menyimpan) -->
<div id="save_loader" class="fixed inset-0 flex flex-col items-center justify-center bg-slate-900/40 backdrop-blur-sm z-50 hidden">
    <div class="w-12 h-12 border-4 border-white/30 border-t-white rounded-full animate-spin mb-4 shadow-lg"></div>
    <div class="bg-white px-6 py-2 rounded-full shadow-lg font-semibold text-slate-700 animate-pulse">
        Menyimpan Data...
    </div>
</div>

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
            if(iconKanan) iconKanan.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v4m0 0H5m4 0L3 3m6 18v-4m0 0H5m4 0l-2 2m6-16V3m0 4h4m-4 0l2-2m-6 16v-4m0 0h4m-4 0l2 2"></path>';
        }
    }
</script>
@endsection
