{{--
    NIPS - Neonatal Infant Pain Scale (6 parameter, total maksimum 7).
    Dipakai form 38 Skrining Nyeri Perina.
    Definisi parameter & label opsi diambil dari `NyeriHelper::nipsItems()`.

    Props:
      items    -> array parameter NIPS (default NyeriHelper::nipsItems())
      values   -> assoc [nama_parameter => nilai] jawaban tersimpan/terpilih
      idPrefix -> prefiks id radio (default `nips`)
--}}
@php
    $nipsItems = $items ?? \App\Helpers\NyeriHelper::nipsItems();
    $nipsValues = $values ?? [];
    $nipsIdPrefix = $idPrefix ?? 'nips';
@endphp

<div class="p-4 border border-slate-200 rounded-lg">
    <div class="font-bold text-slate-800 mb-1">
        NIPS - Neonatal Infant Pain Scale
    </div>
    <p class="text-xs text-slate-500 mb-3">
        Tandai yang paling sesuai pada 6 parameter berikut. Total dihitung otomatis di server.
    </p>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs" style="min-width: 620px;">
            <thead>
                <tr class="bg-slate-100 border-b border-slate-200">
                    <th class="px-3 py-2 font-bold text-slate-600 w-48">Parameter</th>
                    <th class="px-3 py-2 font-bold text-slate-600">Respon</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nipsItems as $nipsKey => $nipsItem)
                    @php
                        $nipsAktif = $nipsValues[$nipsKey] ?? null;
                        $nipsAktif = $nipsAktif === null || $nipsAktif === '' ? null : (string) $nipsAktif;
                    @endphp
                    <tr class="border-b border-slate-100 align-top">
                        <td class="px-3 py-2 font-semibold text-slate-700">{{ $nipsItem['label'] }}</td>
                        <td class="px-3 py-2">
                            <div class="flex flex-col gap-1.5">
                                @foreach ($nipsItem['opsi'] as $nipsOpsi)
                                    @php
                                        $nipsId = $nipsIdPrefix.'-'.$nipsKey.'-'.$nipsOpsi['value'];
                                        $nipsCek = $nipsAktif !== null && $nipsAktif === (string) $nipsOpsi['value'];
                                    @endphp
                                    <label for="{{ $nipsId }}" class="flex items-start gap-1.5 cursor-pointer">
                                        <input type="radio" id="{{ $nipsId }}" name="{{ $nipsKey }}"
                                               value="{{ $nipsOpsi['value'] }}" class="mt-0.5" @checked($nipsCek)>
                                        <span class="text-slate-600">{{ $nipsOpsi['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>