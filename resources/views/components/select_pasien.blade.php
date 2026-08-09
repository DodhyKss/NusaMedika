@props([
    'id' => 'pasien_id',
    'name' => 'pasien_id',
    'label' => null,
    'placeholder' => '-- Ketik Nama atau No. RM Pasien --',
    'required' => false,
    'selected' => null,
])

@php
    $selectedLabel = null;
    if ($selected) {
        $selectedPasien = \App\Models\Pasien::find($selected);
        $selectedLabel = $selectedPasien ? "{$selectedPasien->no_mr} - {$selectedPasien->nama_pasien}" : null;
    }
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
            {{ $label }}
            @if ($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        class="select2-pasien w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700"
        style="width: 100%"
        data-url="{{ route('api.pasien.search') }}"
        data-placeholder="{{ $placeholder }}"
        @if ($required) required @endif
    >
        <option value=""></option>
        @if ($selectedLabel)
            <option value="{{ $selected }}" selected>{{ $selectedLabel }}</option>
        @endif
    </select>
</div>
