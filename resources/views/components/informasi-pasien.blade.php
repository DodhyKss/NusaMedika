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
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
    <!-- Header Pasien -->
    <div onclick="toggleInfoPasien()" class="bg-slate-800 border-b border-slate-700 px-5 py-4 flex items-center justify-between cursor-pointer hover:bg-slate-700/80 transition-colors group">
        <div class="flex items-center gap-4">
            <!-- Avatar/Initials -->
            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-lg shadow-inner group-hover:scale-105 transition-transform">
                {{ substr($pasien->nama_pasien ?? 'S', 0, 1) }}
            </div>
            
            <!-- Nama & MR -->
            <div>
                <h2 class="text-lg font-bold text-white group-hover:text-blue-100 transition-colors">{{ $pasien->nama_pasien ?? 'Tn. Supriyanto' }}</h2>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-white/10 text-white text-xs font-bold border border-white/20">
                        RM: {{ $pasien->no_mr ?? '00-12-34-56' }}
                    </span>
                    <span class="text-slate-400 text-xs">|</span>
                    <span class="text-slate-200 text-xs font-medium">{{ $pasien->jenis_kelamin ?? 'Laki-laki' }}, {{ $umur }}</span>
                </div>
            </div>
        </div>
        
        <!-- Status Label & Toggle -->
        <div class="flex items-center gap-4">
            <div class="text-right">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-400/10 text-emerald-300 text-xs font-bold border border-emerald-400/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
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
            <div class="text-slate-400 group-hover:text-white transition-colors">
                <svg id="info-pasien-chevron" class="w-5 h-5 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
            </div>
        </div>
    </div>

    <!-- Info Detail -->
    <div id="info-pasien-detail" class="px-5 py-4 transition-all duration-300">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- TTL -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tempat, Tanggal Lahir</p>
                <p class="text-sm font-medium text-slate-700">{{ $pasien->tempat_lahir ?? '-' }}, {{ ($pasien->tgl_lahir ?? null) ? \Carbon\Carbon::parse($pasien->tgl_lahir)->format('d F Y') : '-' }}</p>
            </div>

            <!-- NIK -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">NIK (KTP)</p>
                <p class="text-sm font-medium text-slate-700">{{ $pasien->ktp ?? '-' }}</p>
            </div>

            <!-- No HP -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Nomor Handphone</p>
                <p class="text-sm font-medium text-slate-700">{{ $pasien->no_hp ?? '-' }}</p>
            </div>

            <!-- Tanggal Layanan -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tanggal Layanan</p>
                <p class="text-sm font-bold text-emerald-600">{{ \Carbon\Carbon::parse($pasien->tgl_layanan)->format('d F Y')?? '-' }}</p>
            </div>

            <!-- DPJP -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">DPJP</p>
                <p class="text-sm font-bold text-emerald-600">{{ $pasien->nama_pegawai ?? '-' }}</p>
            </div>

            <!-- Penjamin -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Nasabah</p>
                <p class="text-sm font-bold text-emerald-600">{{ $pasien->nama_nasabah ?? '-' }}</p>
            </div>

            <!-- No Bukti Layanan -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">No. Bukti Layanan</p>
                <p class="text-sm font-bold text-emerald-600">{{ $pasien->sep ?? '-' }}</p>
            </div>

            <!-- Layanan -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Layanan</p>
                <p class="text-sm font-bold text-emerald-600">{{ $pasien->nama_bagian ?? '-' }}</p>
            </div>
        </div>

        <hr class="my-4 border-slate-100">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Alamat -->
            <div class="lg:col-span-2">
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Alamat Lengkap</p>
                <p class="text-sm font-medium text-slate-700 leading-relaxed">{{ $pasien->alamat ?? '-' }}</p>
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
                    <span class="inline-flex items-center px-2.5 py-1 rounded bg-rose-50 text-rose-600 text-xs font-semibold border border-rose-100">
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
    function toggleInfoPasien() {
        const detail = document.getElementById('info-pasien-detail');
        const chevron = document.getElementById('info-pasien-chevron');
        
        if (detail.classList.contains('hidden')) {
            detail.classList.remove('hidden');
            // Menghapus efek rotasi agar panah menghadap ke bawah
            chevron.classList.remove('rotate-180');
        } else {
            detail.classList.add('hidden');
            // Menambahkan efek rotasi agar panah menghadap ke atas
            chevron.classList.add('rotate-180');
        }
    }
</script>
