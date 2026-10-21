@php
    $val = fn ($k) => $f[$k] ?? '';
    $nm = function ($k) use ($idx) {
        return $idx === null ? '' : 'name="fields['.$idx.']['.$k.']"';
    };
    $cls = 'w-full text-sm border border-slate-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700';
    $lbl = 'block text-[11px] font-semibold text-slate-600 mb-1';
    $tipeOpsi = \App\Http\Controllers\Administrator\ManajemenEMR\KatalogFormBlanko\KatalogFormBlankoController::TIPE;
@endphp

<div class="kartu-field border border-slate-200 rounded-lg p-4 bg-slate-50/60">
    <div class="flex items-center justify-between mb-3">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Field #<span class="kartu-urut">{{ ($idx ?? 0) + 1 }}</span></span>
        <div class="flex gap-1">
            <button type="button" data-aksi="naik" title="Naikkan urutan" class="px-2 py-1 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded hover:bg-slate-100 transition-colors">▲ Naik</button>
            <button type="button" data-aksi="turun" title="Turunkan urutan" class="px-2 py-1 text-xs font-semibold text-slate-600 bg-white border border-slate-200 rounded hover:bg-slate-100 transition-colors">▼ Turun</button>
            <button type="button" data-aksi="hapus" title="Hapus field dari daftar" class="px-2 py-1 text-xs font-semibold text-red-500 bg-white border border-red-200 rounded hover:bg-red-50 transition-colors">Hapus</button>
        </div>
    </div>

    <input type="hidden" data-f="form_blanko_field_id" {!! $nm('form_blanko_field_id') !!} value="{{ $val('form_blanko_field_id') }}">

    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
        <div class="md:col-span-4">
            <label class="{{ $lbl }}">Label <span class="text-red-500">*</span></label>
            <input data-f="label" {!! $nm('label') !!} value="{{ $val('label') }}" required maxlength="200" class="{{ $cls }}" placeholder="mis. Nama Obat">
        </div>
        <div class="md:col-span-4">
            <label class="{{ $lbl }}">kode_field <span class="text-red-500">*</span></label>
            <input data-f="kode_field" {!! $nm('kode_field') !!} value="{{ $val('kode_field') }}" required maxlength="100" pattern="[a-z][a-z0-9_]*" class="{{ $cls }}" placeholder="nama_obat">
        </div>
        <div class="md:col-span-4">
            <label class="{{ $lbl }}">Tipe Kontrol <span class="text-red-500">*</span></label>
            <select data-f="tipe_kontrol" {!! $nm('tipe_kontrol') !!} class="{{ $cls }}">
                @foreach ($tipeOpsi as $tipe => $nama)
                    <option value="{{ $tipe }}" {{ $val('tipe_kontrol') === $tipe ? 'selected' : '' }}>{{ $nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="md:col-span-3">
            <label class="{{ $lbl }}">Grup / Panel</label>
            <input data-f="grup" {!! $nm('grup') !!} value="{{ $val('grup') }}" maxlength="100" class="{{ $cls }}" placeholder="mis. Data Obat">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $lbl }}">Lebar (1–12)</label>
            <input type="number" data-f="lebar_kolom" {!! $nm('lebar_kolom') !!} value="{{ $val('lebar_kolom') ?: 6 }}" min="1" max="12" class="{{ $cls }}">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $lbl }}">depends_on_field</label>
            <input data-f="depends_on_field" {!! $nm('depends_on_field') !!} value="{{ $val('depends_on_field') }}" maxlength="100" class="{{ $cls }}" placeholder="kode pemicu">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $lbl }}">depends_on_value</label>
            <input data-f="depends_on_value" {!! $nm('depends_on_value') !!} value="{{ $val('depends_on_value') }}" maxlength="100" class="{{ $cls }}" placeholder="nilai pemicu">
        </div>
        <div class="md:col-span-3">
            <label class="{{ $lbl }}">Kewajiban</label>
            <label class="flex items-center gap-2 text-sm text-slate-700 mt-2">
                <input type="checkbox" data-f="wajib" {!! $nm('wajib') !!} value="1" {{ ! empty($val('wajib')) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Wajib diisi
            </label>
        </div>

        <div class="md:col-span-12 blok-opsi" style="display:none">
            <label class="{{ $lbl }}">Opsi (per baris: <code>value</code> atau <code>value|Label</code>)</label>
            <textarea data-f="opsi_text" {!! $nm('opsi_text') !!} rows="3" maxlength="20000" class="{{ $cls }}" placeholder="Ya|Ya&#10;Tidak|Tidak">{{ $val('opsi_text') }}</textarea>
        </div>

        <div class="md:col-span-3">
            <label class="{{ $lbl }}">min</label>
            <input data-f="min" {!! $nm('min') !!} value="{{ $val('min') }}" class="{{ $cls }}" placeholder="0">
        </div>
        <div class="md:col-span-3">
            <label class="{{ $lbl }}">max</label>
            <input data-f="max" {!! $nm('max') !!} value="{{ $val('max') }}" class="{{ $cls }}" placeholder="100">
        </div>
        <div class="md:col-span-3">
            <label class="{{ $lbl }}">maxLength</label>
            <input type="number" data-f="maxLength" {!! $nm('maxLength') !!} value="{{ $val('maxLength') }}" min="1" max="65535" class="{{ $cls }}" placeholder="255">
        </div>
        <div class="md:col-span-3">
            <label class="{{ $lbl }}">pattern (regex)</label>
            <input data-f="pattern" {!! $nm('pattern') !!} value="{{ $val('pattern') }}" maxlength="255" class="{{ $cls }}" placeholder="mis. ^[0-9]+$">
        </div>

        <div class="md:col-span-4">
            <label class="{{ $lbl }}">Placeholder</label>
            <input data-f="placeholder" {!! $nm('placeholder') !!} value="{{ $val('placeholder') }}" maxlength="255" class="{{ $cls }}">
        </div>
        <div class="md:col-span-2 blok-rows" style="display:none">
            <label class="{{ $lbl }}">Baris (textarea)</label>
            <input type="number" data-f="rows" {!! $nm('rows') !!} value="{{ $val('rows') }}" min="1" max="50" class="{{ $cls }}" placeholder="3">
        </div>
        <div class="md:col-span-4 blok-source" style="display:none">
            <label class="{{ $lbl }}">Sumber (select2)</label>
            <input data-f="source" {!! $nm('source') !!} value="{{ $val('source') }}" maxlength="255" class="{{ $cls }}" placeholder="mis. api.dokter.search">
        </div>
        <div class="md:col-span-3">
            <label class="{{ $lbl }}">Nilai default</label>
            <input data-f="default" {!! $nm('default') !!} value="{{ $val('default') }}" maxlength="255" class="{{ $cls }}">
        </div>
        <div class="md:col-span-3">
            <label class="{{ $lbl }}">Multiple (select)</label>
            <label class="flex items-center gap-2 text-sm text-slate-700 mt-2">
                <input type="checkbox" data-f="multiple" {!! $nm('multiple') !!} value="1" {{ ! empty($val('multiple')) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Boleh pilih banyak
            </label>
        </div>
    </div>
</div>
