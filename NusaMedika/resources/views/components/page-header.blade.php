@props([
    'title' => null,
    'subtitle' => null
])

<div class="mb-6 flex flex-wrap justify-between items-end gap-3">
    <div class="min-w-0">
        <h1 class="text-xl sm:text-[22px] font-bold text-slate-900 tracking-tight">{{ $title }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
    </div>
    {{ $slot }}
</div>