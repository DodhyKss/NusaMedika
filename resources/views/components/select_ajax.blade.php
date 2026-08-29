@props([
    'id' => null,
    'name' => null,
    'placeholder' => '-- Ketik Data Pencarian --',
    'label' => null,
    'url' => null,
    'required' => false,
])

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">
            {{ $label }}
            @if ($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <select 
        name="{{ $name }}" 
        id="{{ $id }}"
        class="select2-ajax w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-blue-500/200 focus:border-blue-500 transition-all outline-none text-slate-700"
        style="width: 100%"
        data-url="{{ $url }}"
        data-placeholder="{{ $placeholder }}"
        @if ($required) required @endif
    >
    </select>
</div>