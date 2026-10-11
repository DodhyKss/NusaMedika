{{--
    Tabel FLACC Scale (Face, Legs, Activity, Cry, Consolability).
    Dipakai form 27 Penilaian Nyeri & form 36 Metode Nyeri.
    Definisi butir & label opsi diambil dari `NyeriHelper::flaccItems()`
    supaya tidak diduplikasi di tiap form.

    Props:
      items    -> array butir FLACC (default NyeriHelper::flaccItems())
      values   -> assoc [nama_butir => nilai] jawaban tersimpan/terpilih
      idPrefix -> prefiks id radio (default `flacc`)
--}}
@php
    $flaccItems = $items ?? \App\Helpers\NyeriHelper::flaccItems();
    $flaccValues = $values ?? [];
    $flaccIdPrefix = $idPrefix ?? 'flacc';
@endphp

<div class="p-4 border border-slate-200 rounded-lg">
    <div class="font-bold text-slate-800 mb-1">
        FLACC Scale (Face, Legs, Activity, Cry, Consolability)
    </div>
    <p class="text-xs text-slate-500 mb-3">Tandai yang paling sesuai. Total dihitung otomatis di server.</p>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs" style="min-width: 560px;">
            <thead>
                <tr class="bg-slate-100 border-b border-slate-200">
                    <th class="px-3 py-2 font-bold text-slate-600 w-40">Butir</th>
                    <th class="px-3 py-2 font-bold text-slate-600">0</th>
                    <th class="px-3 py-2 font-bold text-slate-600">1</th>
                    <th class="px-3 py-2 font-bold text-slate-600">2</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($flaccItems as $flaccKey => $flaccItem)
                    @php
                        $flaccAktif = $flaccValues[$flaccKey] ?? null;
                        $flaccAktif = $flaccAktif === null || $flaccAktif === '' ? null : (string) $flaccAktif;
                    @endphp
                    <tr class="border-b border-slate-100 align-top">
                        <td class="px-3 py-2 font-semibold text-slate-700">{{ $flaccItem['label'] }}</td>
                        @foreach ($flaccItem['opsi'] as $flaccOpsi)
                            @php
                                $flaccId = $flaccIdPrefix.'-'.$flaccKey.'-'.$flaccOpsi['value'];
                                $flaccCek = $flaccAktif !== null && $flaccAktif === (string) $flaccOpsi['value'];
                            @endphp
                            <td class="px-3 py-2">
                                <label for="{{ $flaccId }}" class="flex items-start gap-1.5 cursor-pointer">
                                    <input type="radio" id="{{ $flaccId }}" name="{{ $flaccKey }}"
                                           value="{{ $flaccOpsi['value'] }}" class="mt-0.5" @checked($flaccCek)>
                                    <span class="text-slate-600">{{ $flaccOpsi['label'] }}</span>
                                </label>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>