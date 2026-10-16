{{-- SAFELIST TAILWIND (jangan dihapus — kelas span dibangun dinamis dari lebar_kolom 1-12):
     md:col-span-1 md:col-span-2 md:col-span-3 md:col-span-4 md:col-span-5 md:col-span-6
     md:col-span-7 md:col-span-8 md:col-span-9 md:col-span-10 md:col-span-11 md:col-span-12 --}}
@php
    $kode = $field->kode_field;
    $val = (array) ($field->validasi ?? []);
    $set = (array) ($field->setelan ?? []);
    $opsiList = (array) ($field->opsi ?? []);
    $span = max(1, min(12, (int) ($field->lebar_kolom ?: 12)));
    $nilai = old('data.'.$kode, $dataTersimpan[$kode] ?? null);
    $wajibAttr = $field->wajib ? 'required' : '';
    $kelasInput = 'w-full border border-slate-300 rounded-md px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 bg-white';
    $opsiTerpakai = $opsiList;
@endphp
<div class="md:col-span-{{ $span }} {{ $field->depends_on_field ? 'hidden' : '' }}"
    @if ($field->depends_on_field) data-depends-on="{{ $field->depends_on_field }}" data-depends-value="{{ $field->depends_on_value }}" @endif>
    <label class="block text-xs font-semibold text-slate-600 mb-1" for="fld_{{ $kode }}">
        {{ $field->label }}@if ($field->wajib) <span class="text-red-500">*</span>@endif
    </label>
    @if ($field->keterangan)
        <p class="text-[11px] text-slate-400 -mt-0.5 mb-1.5">{{ $field->keterangan }}</p>
    @endif

    @switch($field->tipe_kontrol)

        @case('textarea')
            <textarea id="fld_{{ $kode }}" name="{{ 'data['.$kode.']' }}" rows="{{ (int) ($set['rows'] ?? 3) }}"
                maxlength="{{ (int) ($val['maxLength'] ?? 65535) }}"
                placeholder="{{ $set['placeholder'] ?? '' }}" {{ $wajibAttr }}
                class="{{ $kelasInput }}">{{ $nilai ?? '' }}</textarea>
            @break

        @case('number')
            <input type="number" id="fld_{{ $kode }}" name="{{ 'data['.$kode.']' }}" value="{{ $nilai ?? '' }}"
                @if (isset($val['min'])) min="{{ $val['min'] }}" @endif
                @if (isset($val['max'])) max="{{ $val['max'] }}" @endif
                step="{{ $set['step'] ?? ($val['step'] ?? 'any') }}"
                placeholder="{{ $set['placeholder'] ?? '' }}" {{ $wajibAttr }}
                class="{{ $kelasInput }}">
            @break

        @case('date')
            <input type="date" id="fld_{{ $kode }}" name="{{ 'data['.$kode.']' }}" value="{{ $nilai ?? '' }}"
                {{ $wajibAttr }} class="{{ $kelasInput }}">
            @break

        @case('datetime')
            <input type="datetime-local" id="fld_{{ $kode }}" name="{{ 'data['.$kode.']' }}" value="{{ $nilai ?? '' }}"
                {{ $wajibAttr }} class="{{ $kelasInput }}">
            @break

        @case('time')
            <input type="time" id="fld_{{ $kode }}" name="{{ 'data['.$kode.']' }}" value="{{ $nilai ?? '' }}"
                {{ $wajibAttr }} class="{{ $kelasInput }}">
            @break

        @case('select')
            <select id="fld_{{ $kode }}" name="{{ 'data['.$kode.']' . ((bool) ($set['multiple'] ?? false) ? '[]' : '') }}"
                @if ($set['multiple'] ?? false) multiple @endif {{ $wajibAttr }}
                class="{{ $kelasInput }}">
                @unless ($set['multiple'] ?? false)
                    <option value="">-- Pilih --</option>
                @endunless
                @foreach ($opsiTerpakai as $o)
                    <option value="{{ $o['value'] }}"
                        @selected(in_array((string) $o['value'], array_map('strval', (array) ($nilai ?? [])), true))>
                        {{ $o['label'] }}
                    </option>
                @endforeach
            </select>
            @break

        @case('radio')
            <div class="flex flex-wrap gap-x-4 gap-y-1.5 mt-0.5" id="fld_{{ $kode }}">
                @foreach ($opsiTerpakai as $i => $o)
                    <label class="inline-flex items-center gap-1.5 text-sm text-slate-700 cursor-pointer">
                        <input type="radio" name="{{ 'data['.$kode.']' }}" value="{{ $o['value'] }}"
                            @checked((string) ($nilai ?? '') === (string) $o['value'])
                            @if ($i === 0) {{ $wajibAttr }} @endif
                            class="text-blue-600 border-slate-300 focus:ring-blue-500">
                        {{ $o['label'] }}
                    </label>
                @endforeach
            </div>
            @break

        @case('checkbox')
            <div class="flex flex-wrap gap-x-4 gap-y-1.5 mt-0.5" id="fld_{{ $kode }}">
                @foreach ($opsiTerpakai as $o)
                    <label class="inline-flex items-center gap-1.5 text-sm text-slate-700 cursor-pointer">
                        <input type="checkbox" name="{{ 'data['.$kode.']' }}[]" value="{{ $o['value'] }}"
                            @checked(in_array((string) $o['value'], array_map('strval', (array) ($nilai ?? [])), true))
                            class="text-blue-600 border-slate-300 focus:ring-blue-500">
                        {{ $o['label'] }}
                    </label>
                @endforeach
            </div>
            @break

        @case('checkbox_single')
            {{-- pola legacy hidden 0 + checkbox 1 → selalu terkirim --}}
            <input type="hidden" name="{{ 'data['.$kode.']' }}" value="0">
            <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer mt-1">
                <input type="checkbox" name="{{ 'data['.$kode.']' }}" value="1" id="fld_{{ $kode }}"
                    @checked(in_array((string) ($nilai ?? ''), ['1', 'on', 'true'], true))
                    class="text-blue-600 border-slate-300 focus:ring-blue-500 rounded">
                {{ $set['label_cek'] ?? ($field->label) }}
            </label>
            @break

        @case('select2')
            {{-- Deviasi terdokumentasi (KONSEP §4): mesin AJAX Select2 tidak
                 diintegrasikan — render select statis bila opsi diisi, selain
                 itu input teks manual. `source` tetap wajib di admin sbg intent. --}}
            @if ($opsiTerpakai !== [])
                <select id="fld_{{ $kode }}" name="{{ 'data['.$kode.']' }}" {{ $wajibAttr }} class="{{ $kelasInput }}">
                    <option value="">-- Pilih --</option>
                    @foreach ($opsiTerpakai as $o)
                        <option value="{{ $o['value'] }}" @selected((string) ($nilai ?? '') === (string) $o['value'])>
                            {{ $o['label'] }}
                        </option>
                    @endforeach
                </select>
            @else
                <input type="text" id="fld_{{ $kode }}" name="{{ 'data['.$kode.']' }}" value="{{ $nilai ?? '' }}"
                    {{ $wajibAttr }} class="{{ $kelasInput }}"
                    placeholder="Isi manual (sumber: {{ $set['source'] ?? 'master' }})">
            @endif
            @break

        @case('signature')
            @php
                $ttdNilai = (is_string($nilai) && str_starts_with($nilai, 'data:image/')) ? $nilai : '';
            @endphp
            @if ($isView ?? false)
                @if ($ttdNilai !== '')
                    <img src="{{ $ttdNilai }}" alt="Tanda tangan" class="border border-slate-300 rounded-md bg-white max-h-28 p-1">
                @else
                    <p class="text-xs text-slate-400 italic">Belum ditandatangani.</p>
                @endif
            @else
                <div class="border border-slate-300 rounded-md bg-white p-1.5">
                    <input type="hidden" name="{{ 'data['.$kode.']' }}" id="fld_{{ $kode }}" value="{{ $ttdNilai }}">
                    <canvas id="cv_{{ $kode }}" width="600" height="160" class="w-full touch-none cursor-crosshair" style="height:110px"></canvas>
                </div>
                <div class="flex justify-between items-center mt-1">
                    <span class="text-[11px] text-slate-400">Tanda tangan dengan kursor/sentuhan</span>
                    <button type="button" data-hapus-ttd="cv_{{ $kode }}"
                        class="text-[11px] font-semibold text-red-600 hover:text-red-700">Hapus</button>
                </div>
            @endif
            @break

        @default
            {{-- text --}}
            <input type="text" id="fld_{{ $kode }}" name="{{ 'data['.$kode.']' }}" value="{{ $nilai ?? '' }}"
                @if (isset($val['maxLength'])) maxlength="{{ (int) $val['maxLength'] }}" @endif
                @if (isset($val['min'])) minlength="{{ (int) $val['min'] }}" @endif
                @if (! empty($val['pattern'])) pattern="{{ $val['pattern'] }}" @endif
                placeholder="{{ $set['placeholder'] ?? '' }}" {{ $wajibAttr }}
                class="{{ $kelasInput }}">

    @endswitch

    @error('data.'.$kode)
        <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
