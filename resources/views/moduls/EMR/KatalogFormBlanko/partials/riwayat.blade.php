@php
    $labelPerKode = [];
    foreach ($fields as $f) {
        $labelPerKode[$f->kode_field] = $f->label;
    }
@endphp
<div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
        <h3 class="text-sm font-bold text-slate-700">Riwayat Pengisian</h3>
        <p class="text-[11px] text-slate-500 mt-0.5">Maksimal 50 pengisian terakhir pada kunjungan pasien ini.</p>
    </div>

    @if ($riwayat->isEmpty())
        <div class="p-5 text-center text-xs text-slate-400">Belum ada pengisian tersimpan.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-500 text-left">
                        <th class="px-3 py-2 font-semibold">#</th>
                        <th class="px-3 py-2 font-semibold">Tanggal</th>
                        <th class="px-3 py-2 font-semibold">Petugas</th>
                        <th class="px-3 py-2 font-semibold">Ringkasan</th>
                        <th class="px-3 py-2 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($riwayat as $item)
                        @php
                            $idPengisian = $item->form_blanko_pengisian_id;
                            $aktifRing = ((int) ($pengisian_id ?? 0)) === (int) $idPengisian;
                            $ringkasan = json_decode($item->ringkasan ?? '{}', true) ?: [];
                            $teksRing = [];
                            foreach ($ringkasan as $kode => $nilaiRing) {
                                $teksRing[] = ($labelPerKode[$kode] ?? $kode).': '.$nilaiRing;
                            }
                            $teksRing = $teksRing === [] ? '—' : implode(' · ', $teksRing);
                        @endphp
                        <tr class="{{ $aktifRing ? 'bg-blue-50/60' : 'hover:bg-slate-50' }}">
                            <td class="px-3 py-2 text-slate-400">{{ $loop->iteration }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-600">
                                {{ \Carbon\Carbon::parse($item->input_time)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-slate-600">{{ $item->nama_pegawai ?? '—' }}</td>
                            <td class="px-3 py-2 text-slate-600 max-w-[220px] truncate" title="{{ $teksRing }}">{{ $teksRing }}</td>
                            <td class="px-3 py-2">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($aksesCrud['read'])
                                        <a href="{{ route('emr.katalog_form_blanko.isi', ['form_blanko' => $blanko->form_blanko_id, 'registrasi_detail_id' => $registrasi_detail_id, 'pengisian_id' => $idPengisian, 'action' => 'view']) }}"
                                           class="p-1.5 text-slate-600 hover:bg-slate-100 rounded-md transition-colors" title="Lihat">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        </a>
                                    @endif
                                    @if ($aksesCrud['update'])
                                        <a href="{{ route('emr.katalog_form_blanko.isi', ['form_blanko' => $blanko->form_blanko_id, 'registrasi_detail_id' => $registrasi_detail_id, 'pengisian_id' => $idPengisian]) }}"
                                           class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-md transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </a>
                                    @endif
                                    @if ($aksesCrud['read'])
                                        <a href="{{ route('emr.katalog_form_blanko.print', ['form_blanko' => $blanko->form_blanko_id, 'registrasi_detail_id' => $registrasi_detail_id, 'pengisian_id' => $idPengisian]) }}"
                                           target="_blank" rel="noopener"
                                           class="p-1.5 text-slate-600 hover:bg-slate-100 rounded-md transition-colors" title="Cetak">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                        </a>
                                    @endif
                                    @if ($aksesCrud['delete'])
                                        <form action="{{ route('emr.katalog_form_blanko.destroy', ['form_blanko' => $blanko->form_blanko_id, 'registrasi_detail_id' => $registrasi_detail_id, 'pengisian_id' => $idPengisian]) }}"
                                              method="POST" class="inline"
                                              data-confirm-message="Hapus pengisian form blanko ini?"
                                              data-confirm-danger="true" data-confirm-title="Hapus Data">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-md transition-colors" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
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
    @endif
</div>
