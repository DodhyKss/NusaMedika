@php
    $umur = 'Unknown';
    if(isset($pasien->tgl_lahir) && $pasien->tgl_lahir) {
        try {
            $umur = \Carbon\Carbon::parse($pasien->tgl_lahir)->age . ' Tahun';
        } catch (\Exception $e) {
            $umur = 'Unknown';
        }
    }
@endphp
<div data-info-pasien class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
    <!-- Header Pasien -->
    <div onclick="toggleInfoPasien(this)" class="bg-slate-800 border-b border-slate-700 px-4 sm:px-5 py-3 sm:py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4 cursor-pointer hover:bg-slate-700/80 transition-colors group">
        <div class="flex items-center gap-3 sm:gap-4 min-w-0 sm:flex-1">
            <!-- Avatar/Initials -->
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-base sm:text-lg shadow-inner group-hover:scale-105 transition-transform flex-shrink-0">
                {{ substr($pasien->nama_pasien ?? 'S', 0, 1) }}
            </div>
            
            <!-- Nama & MR -->
            <div class="min-w-0 flex-1">
                <h2 class="text-base sm:text-lg font-bold text-white group-hover:text-blue-100 transition-colors truncate">{{ $pasien->nama_pasien ?? 'Tn. Supriyanto' }}</h2>
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-white/10 text-white text-xs font-bold border border-white/20">
                        RM: {{ $pasien->no_mr ?? '-' }}
                    </span>
                    <span class="text-slate-400 text-xs hidden sm:inline">|</span>
                    <span class="text-slate-200 text-xs font-medium">{{ $pasien->jenis_kelamin ?? 'Laki-laki' }}, {{ $umur }}</span>
                </div>
            </div>
        </div>
        
        <!-- Status Label & Toggle -->
        <div class="flex items-center justify-between sm:justify-end gap-2 sm:gap-4 flex-shrink-0">
            <div class="text-right min-w-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1 sm:py-1.5 rounded-lg bg-emerald-400/10 text-emerald-300 text-[11px] sm:text-xs font-bold border border-emerald-400/20 whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse flex-shrink-0"></span>
                    @php
                        $jenis_rawat_text = 'Unknown';
                        if(isset($pasien)) {
                            $jr = strtolower(trim($pasien->jenis_rawat));
                            if ($jr == '1' || $jr == 'igd') $jenis_rawat_text = 'IGD';
                            elseif ($jr == '2' || $jr == 'rj') $jenis_rawat_text = 'Rawat Jalan';
                            elseif ($jr == '3' || $jr == 'ri') $jenis_rawat_text = 'Rawat Inap';
                        }
                    @endphp
                    {{ $jenis_rawat_text }}
                </span>
            </div>
            
            <!-- Chevron Toggle Icon -->
            <div class="text-slate-400 group-hover:text-white transition-colors flex-shrink-0">
                <svg id="info-pasien-chevron" class="w-5 h-5 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
            </div>
        </div>
    </div>

    <!-- Info Detail -->
    <div id="info-pasien-detail" class="px-4 sm:px-5 py-3 sm:py-4 transition-all duration-300">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- TTL -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tempat, Tanggal Lahir</p>
                <p class="text-sm font-medium text-slate-700 break-words">{{ $pasien->tempat_lahir ?? '-' }}, {{ ($pasien->tgl_lahir ?? null) ? \Carbon\Carbon::parse($pasien->tgl_lahir)->format('d F Y') : '-' }}</p>
            </div>

            <!-- NIK -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">NIK (KTP)</p>
                <p class="text-sm font-medium text-slate-700 break-words">{{ $pasien->ktp ?? '-' }}</p>
            </div>

            <!-- No HP -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Nomor Handphone</p>
                <p class="text-sm font-medium text-slate-700 break-words">{{ $pasien->no_hp ?? '-' }}</p>
            </div>

            <!-- Tanggal Layanan -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal Layanan</p>
                <p class="text-sm font-bold text-emerald-600 break-words">{{ \Carbon\Carbon::parse($pasien->tgl_layanan)->format('d F Y')?? '-' }}</p>
            </div>

            <!-- DPJP -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">DPJP</p>
                <p class="text-sm font-bold text-emerald-600 break-words">{{ $pasien->nama_pegawai ?? '-' }}</p>
            </div>

            <!-- Penjamin -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Nasabah</p>
                <p class="text-sm font-bold text-emerald-600 break-words">{{ $pasien->nama_nasabah ?? '-' }}</p>
            </div>

            <!-- No Bukti Layanan -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">No. Bukti Layanan</p>
                <p class="text-sm font-bold text-emerald-600 break-words">{{ $pasien->sep ?? '-' }}</p>
            </div>

            <!-- Layanan -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Layanan</p>
                <p class="text-sm font-bold text-emerald-600 break-words">{{ $pasien->nama_bagian ?? '-' }}</p>
            </div>
        </div>

        <hr class="my-4 border-slate-100">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Alamat -->
            <div class="lg:col-span-2">
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Alamat Lengkap</p>
                <p class="text-sm font-medium text-slate-700 leading-relaxed break-words">{{ $pasien->alamat ?? '-' }}</p>
            </div>

            <!-- Diagnosa Awal -->
            <div class="lg:col-span-2">
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Diagnosa Awal</p>
                <p class="text-sm font-medium text-slate-400 italic">Belum ada diagnosa terdata</p>
            </div>

            <!-- Berat Badan dan Tinggi Badan -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">BB || TB</p>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-600 font-bold border border-red-100">
                    {{ ($pasien->gol_darah ?? 'Tidak Tahu') == 'Tidak Tahu' ? '-' : $pasien->gol_darah }}
                </span>
            </div>

            <!-- Alergi -->
            <div class="lg:col-span-3">
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Alergi Obat/Makanan</p>
                @if(isset($pasien->alergi) && $pasien->alergi != '')
                    <span class="inline-flex items-center px-2.5 py-1 rounded bg-rose-50 text-rose-600 text-xs font-semibold border border-rose-100 break-words">
                        {{ $pasien->alergi }}
                    </span>
                @else
                    <span class="text-sm font-medium text-slate-400 italic">Tidak ada alergi terdata</span>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function toggleInfoPasien(el) {
        // Scope ke kartu yang diklik supaya aman bila komponen dipakai > 1x di halaman.
        const card = el.closest('[data-info-pasien]') || document;
        const detail = card.querySelector('#info-pasien-detail');
        const chevron = card.querySelector('#info-pasien-chevron');
        if (!detail) return;

        if (detail.classList.contains('hidden')) {
            detail.classList.remove('hidden');
            // Menghapus efek rotasi agar panah menghadap ke bawah
            chevron?.classList.remove('rotate-180');
        } else {
            detail.classList.add('hidden');
            // Menambahkan efek rotasi agar panah menghadap ke atas
            chevron?.classList.add('rotate-180');
        }
    }
</script>
