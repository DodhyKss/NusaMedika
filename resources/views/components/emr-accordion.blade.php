@props(['title', 'id', 'bgClass' => 'bg-slate-50', 'isOpen' => false])

<div class="border border-slate-200 mb-2 rounded-sm shadow-sm">
    <button type="button" 
        onclick="document.getElementById('{{ $id }}').classList.toggle('hidden'); document.getElementById('icon-{{ $id }}').classList.toggle('rotate-180');"
        class="w-full flex items-center justify-between px-4 py-3 {{ $bgClass }} hover:brightness-95 transition-all text-left">
        <span class="text-cyan-600 font-medium text-[15px]">{{ $title }}</span>
        <svg id="icon-{{ $id }}" class="w-4 h-4 text-cyan-600 transition-transform duration-300 {{ $isOpen ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div id="{{ $id }}" class="{{ $isOpen ? '' : 'hidden' }} p-5 bg-white border-t border-slate-200">
        {{ $slot }}
    </div>
</div>
