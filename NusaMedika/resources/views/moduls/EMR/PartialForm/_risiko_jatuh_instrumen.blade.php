@php
    /**
     * Panel input untuk satu instrumen risiko jatuh (dipakai oleh
     * pengkajian_risiko_jatuh.blade.php). Panel yang tidak aktif otomatis
     * dinonaktifkan lewat JS agar tidak ikut terkirim.
     */
    $terpilih = $emr_data ?? null;
@endphp

<div id="rj-def-{{ $def['kode'] }}" class="{{ $def['kode'] === ($instrumenAktif ?? '') ? '' : 'hidden' }} border border-slate-300 rounded-sm overflow-hidden bg-white shadow-sm">
    <div class="bg-[#5da9e9] text-white font-bold px-3 py-2 text-xs uppercase tracking-wide flex items-center justify-between">
        <span>{{ $def['nama'] }}</span>
        <span class="font-normal italic lowercase normal-case">{{ $def['rentang_usia'] }}</span>
    </div>

    @if (! empty($def['syarat']))
        <p class="px-3 pt-2 text-[11px] text-slate-500">{{ $def['syarat'] }}</p>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left border-collapse text-slate-700" style="min-width: 520px;">
            <thead>
                <tr class="bg-slate-100">
                    <th class="border-b border-slate-200 px-3 py-2 w-8 text-center font-bold">No</th>
                    <th class="border-b border-slate-200 px-3 py-2 font-bold">Item Penilaian</th>
                    <th class="border-b border-slate-200 px-3 py-2 font-bold text-center" style="width: 260px;">Jawaban</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($def['item'] as $i => $item)
                    @php
                        $nilai = $terpilih[$item['var']] ?? '';
                    @endphp
                    <tr>
                        <td class="px-3 py-2 text-center font-medium align-top">{{ $i + 1 }}</td>
                        <td class="px-3 py-2 align-top">{{ $item['label'] }}</td>
                        <td class="px-3 py-2 align-top">
                            @if (isset($item['opsi']))
                                <div class="flex flex-wrap gap-3">
                                    @foreach ($item['opsi'] as $label => $skor)
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" name="{{ $item['var'] }}" value="{{ $label }}"
                                                   class="text-blue-500 focus:ring-blue-500 w-4 h-4"
                                                   @checked((string) $nilai === (string) $label)>
                                            <span>{{ $label }} <span class="text-slate-400">({{ $skor }})</span></span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif (isset($item['bobot_ya']))
                                <div class="flex flex-wrap gap-3">
                                    @foreach (['Tidak' => 0, 'Ya' => $item['bobot_ya']] as $label => $skor)
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" name="{{ $item['var'] }}" value="{{ $label }}"
                                                   class="text-blue-500 focus:ring-blue-500 w-4 h-4"
                                                   @checked((string) $nilai === (string) $label)>
                                            <span>{{ $label }} <span class="text-slate-400">({{ $skor }})</span></span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    <input type="number" step="0.1" min="0" name="{{ $item['var'] }}" value="{{ $nilai }}"
                                           placeholder="0"
                                           class="w-24 text-sm text-right border border-slate-300 rounded px-2 py-1.5 bg-slate-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                                    <span class="text-slate-500">{{ $item['satuan'] ?? '' }}</span>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>