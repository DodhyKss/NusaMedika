@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800 tracking-tight">List Pelayanan Pasien</h1>
    <p class="text-sm text-slate-500 mt-1">Daftar riwayat kunjungan dan pelayanan medis pasien.</p>
</div>

<!-- Filter Card -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-6 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
    <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center">
        <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
        Filter Pencarian Data
    </h2>
    <form action="{{ route('list_pelayanan_pasien.index') }}" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5 items-end">
            <!-- Tanggal Awal -->
            <div>
                <label for="tanggal_awal" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal Awal</label>
                <input type="date" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors outline-none text-slate-700">
            </div>
            
            <!-- Tanggal Akhir -->
            <div>
                <label for="tanggal_akhir" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal Akhir</label>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors outline-none text-slate-700">
            </div>
            
            <!-- Jenis Layanan -->
            <div>
                <label for="jenis_layanan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Jenis Layanan</label>
                <div class="relative">
                    <select id="jenis_layanan" name="jenis_layanan" class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors outline-none text-slate-700 appearance-none font-medium">
                        <option value="" {{ request('jenis_layanan') == '' ? 'selected' : '' }}>-- Semua Layanan --</option>
                        <option value="IGD" {{ request('jenis_layanan') == 'IGD' ? 'selected' : '' }}>Gawat Darurat (IGD)</option>
                        <option value="RI" {{ request('jenis_layanan') == 'RI' ? 'selected' : '' }}>Rawat Inap (RI)</option>
                        <option value="RJ" {{ request('jenis_layanan') == 'RJ' ? 'selected' : '' }}>Rawat Jalan (RJ)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold py-2.5 px-4 rounded-lg shadow-[0_2px_10px_rgba(37,99,235,0.2)] transition-all flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Tampilkan
                </button>
                <a href="{{ route('list_pelayanan_pasien.index') }}" title="Reset Filter" class="bg-white border border-slate-300 text-slate-500 hover:bg-slate-50 hover:text-slate-800 text-sm font-semibold py-2.5 px-3 rounded-lg shadow-sm transition-colors flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Area Tabel / Data -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    @if(isset($kunjungan) && count($kunjungan) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs uppercase font-bold text-slate-700">
                    <tr>
                        <th scope="col" class="px-6 py-4">ID Registrasi</th>
                        <th scope="col" class="px-6 py-4">Tgl Masuk</th>
                        <th scope="col" class="px-6 py-4">Pasien ID</th>
                        <th scope="col" class="px-6 py-4">Jenis Rawat</th>
                        <th scope="col" class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($kunjungan as $row)
                        <tr class="hover:bg-blue-50/50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $row->registrasi_id }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $row->tgl_masuk ? date('d-m-Y H:i', strtotime($row->tgl_masuk)) : '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded text-xs font-medium border border-slate-200">
                                    {{ $row->pasien_id ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($row->jenis_rawat == 'IGD')
                                    <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-bold border border-red-200">IGD</span>
                                @elseif($row->jenis_rawat == 'RI')
                                    <span class="bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full text-xs font-bold border border-blue-200">Rawat Inap</span>
                                @elseif($row->jenis_rawat == 'RJ')
                                    <span class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full text-xs font-bold border border-emerald-200">Rawat Jalan</span>
                                @else
                                    <span class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-full text-xs font-bold">{{ $row->jenis_rawat }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($row->status_batal == 1)
                                    <span class="text-red-500 font-bold text-xs uppercase"><i class="fas fa-times-circle mr-1"></i> Batal</span>
                                @else
                                    <span class="text-emerald-500 font-bold text-xs uppercase"><i class="fas fa-check-circle mr-1"></i> Aktif</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $kunjungan->links('components.pagination') }}
        </div>
    @else
        <div class="p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-50 mb-4 border border-slate-100">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <p class="text-slate-500 font-medium text-sm">Tidak ada data pelayanan pasien yang sesuai dengan filter.</p>
        </div>
    @endif
</div>
@endsection