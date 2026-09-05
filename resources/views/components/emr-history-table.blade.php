@props([
    'slug',
    'registrasiDetailId',
    'currentEmrId' => null,
    'headers' => [] // Array assoc: ['variabel_name' => 'Column Title']
])

@php
    $historyData = \App\Helpers\EmrHelper::getHistoryForForm($slug, (int) $registrasiDetailId);
    $form = $historyData['form'];
    $riwayat = $historyData['riwayat'];
    $details = $historyData['details'];
    $aksesCrud = $form ? \App\Helpers\AksesEhr::flags((int) $form->form_id) : ['read' => true, 'update' => true, 'delete' => true];
@endphp

<!-- Search Bar -->
<div class="mb-4 relative">
    <input type="text" id="searchInput" placeholder="Cari di riwayat..." class="w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
</div>

@if($riwayat->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto drag-scroll">
            <table class="w-full text-left border-collapse" style="min-width: 600px;">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-3 py-3 text-center w-12">No.</th>
                        <th class="px-3 py-3">Tanggal Input</th>
                        <th class="px-3 py-3">Pencatat</th>
                        @foreach($headers as $varKey => $headerLabel)
                            <th class="px-3 py-3">{{ $headerLabel }}</th>
                        @endforeach
                        <th class="px-3 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100">
                    @foreach($riwayat as $i => $item)
                        @php
                            $isActive = $currentEmrId && $currentEmrId == $item->emr_id;
                            $itemDetails = $details[$item->emr_id] ?? [];
                        @endphp
                        <tr class="transition-colors riwayat-row {{ $isActive ? 'bg-blue-50/80 font-semibold' : 'hover:bg-blue-50/30' }}">
                            <td class="px-3 py-3 text-center text-slate-500">{{ $i + 1 }}</td>
                            <td class="px-3 py-3 text-slate-700 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($item->tgl_jam)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-3 py-3 font-medium text-slate-800">
                                {{ $item->nama_pegawai ?? '-' }}
                            </td>
                            @foreach($headers as $varKey => $headerLabel)
                                <td class="px-3 py-3 text-slate-600">
                                    {{ Str::limit($itemDetails[$varKey] ?? '-', 40) }}
                                </td>
                            @endforeach
                            <td class="px-3 py-3 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-1">
                                    @if($aksesCrud['read'])
                                    <a href="{{ route('emr.dynamic.index', ['form_name' => $slug, 'registrasi_detail_id' => $registrasiDetailId, 'emr_id' => $item->emr_id, 'action' => 'view']) }}" class="p-1.5 text-slate-600 hover:bg-slate-100 rounded-md transition-colors" title="Lihat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    @endif
                                    @if($aksesCrud['update'])
                                    <a href="{{ route('emr.dynamic.index', ['form_name' => $slug, 'registrasi_detail_id' => $registrasiDetailId, 'emr_id' => $item->emr_id]) }}" onclick="document.getElementById('save_loader').classList.remove('hidden'); document.querySelector('#save_loader div:nth-child(2)').innerText='Memuat Data...';" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    @endif
                                    @if($aksesCrud['delete'])
                                    <form action="{{ route('emr.form.destroy', ['form_name' => $slug, 'registrasi_detail_id' => $registrasiDetailId, 'emr_id' => $item->emr_id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat ini?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($riwayat->hasPages())
        <div class="mt-4">
            {{ $riwayat->links('components.pagination') }}
        </div>
    @endif
@else
    <div class="flex flex-col items-center justify-center h-40 text-center opacity-50 bg-white rounded-xl border border-slate-200 p-6">
        <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
            </path>
        </svg>
        <p class="text-sm text-slate-500">Belum ada riwayat rekam medis</p>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('.riwayat-row');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }
});
</script>
