@props(['pasien', 'registrasi_detail' => null])
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
    <div class="bg-slate-50 border-b border-slate-200 px-5 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <!-- Avatar/Initials -->
            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg shadow-inner">
                {{ substr($pasien->nama_pasien ?? 'S', 0, 1) }}
            </div>
            
            <!-- Nama & MR -->
            <div>
                <h2 class="text-lg font-bold text-slate-800">{{ $pasien->nama_pasien ?? 'Tn. Supriyanto' }}</h2>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-xs font-bold border border-blue-100">
                        RM: {{ $pasien->no_mr ?? '00-12-34-56' }}
                    </span>
                    <span class="text-slate-400 text-xs">|</span>
                    <span class="text-slate-500 text-xs font-medium">{{ $pasien->jenis_kelamin ?? 'Laki-laki' }}, {{ $umur }}</span>
                </div>
            </div>
        </div>
        
        <!-- Status Label -->
        <div class="text-right">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                @php
                    $jenis_rawat_text = 'Unknown';
                    if(isset($registrasi)) {
                        $jr = strtolower(trim($registrasi->jenis_rawat));
                        if ($jr == '1' || $jr == 'igd') $jenis_rawat_text = 'IGD';
                        elseif ($jr == '2' || $jr == 'rj') $jenis_rawat_text = 'Rawat Jalan';
                        elseif ($jr == '3' || $jr == 'ri') $jenis_rawat_text = 'Rawat Inap';
                    }
                @endphp
                {{ $jenis_rawat_text }}
            </span>
        </div>
    </div>

    <!-- Info Detail -->
    <div class="px-5 py-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- TTL -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Tempat, Tanggal Lahir</p>
                <p class="text-sm font-medium text-slate-700">{{ $pasien->tempat_lahir ?? '-' }}, {{ $pasien->tgl_lahir ? \Carbon\Carbon::parse($pasien->tgl_lahir)->format('d F Y') : '-' }}</p>
            </div>

            <!-- NIK -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">NIK (KTP)</p>
                <p class="text-sm font-medium text-slate-700">{{ $pasien->nik ?? '-' }}</p>
            </div>

            <!-- No HP -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Nomor Handphone</p>
                <p class="text-sm font-medium text-slate-700">{{ $pasien->no_hp ?? '-' }}</p>
            </div>

            <!-- Penjamin -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Penjamin Utama</p>
                <p class="text-sm font-bold text-emerald-600">{{ $pasien->penjamin ?? '-' }}</p>
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
                @if(isset($registrasi_detail) && isset($registrasi_detail->diagnosa_awal) && $registrasi_detail->diagnosa_awal != '')
                    <p class="text-sm font-bold text-slate-700">{!! $registrasi_detail->diagnosa_awal !!}</p>
                @else
                    <p class="text-sm font-medium text-slate-400 italic">Belum ada diagnosa terdata</p>
                @endif
            </div>

            <!-- Golongan Darah -->
            <div>
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Golongan Darah</p>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-600 font-bold border border-red-100">
                    {{ $pasien->gol_darah == 'Tidak Tahu' ? '-' : $pasien->gol_darah }}
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
