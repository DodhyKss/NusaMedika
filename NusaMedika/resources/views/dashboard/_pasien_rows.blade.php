@forelse ($rows as $row)
    @php
        $jk = ($row->jenis_kelamin == 'L' || $row->jenis_kelamin == 1) ? 'Laki-laki' : (($row->jenis_kelamin == 'P' || $row->jenis_kelamin == 2) ? 'Perempuan' : $row->jenis_kelamin);
        $umur = !empty($row->tgl_lahir) ? \Carbon\Carbon::parse($row->tgl_lahir)->age . ' Tahun' : '';
        $infoPasien = trim($jk . ($jk && $umur ? ', ' : '') . $umur);
    @endphp
    <div class="px-5 py-3.5 hover:bg-blue-50/40 transition-colors">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-slate-800 truncate">{{ $row->nama_pasien }}</p>
                <p class="text-xs text-blue-600 font-medium">{{ $row->no_mr }}</p>
                @if ($row->nama_bagian)
                    <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $row->nama_bagian }}</p>
                @endif
            </div>
            <div class="shrink-0 flex items-center gap-2">
                @if (!empty($infoPasien))
                    <span class="text-[11px] text-slate-400 hidden md:block">{{ $infoPasien }}</span>
                @endif
                @if (!empty($row->registrasi_detail_id) && Route::has('dashboard_pasien.index'))
                    <a target="_blank" href="{{ route('dashboard_pasien.index', $row->registrasi_detail_id) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                        </svg>
                        Medis
                    </a>
                @endif
            </div>
        </div>
    </div>
@empty
    <p class="text-center text-sm text-slate-400 py-10">Belum ada pasien aktif.</p>
@endforelse