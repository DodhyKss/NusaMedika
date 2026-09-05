@props([
    'title' => null,
    'subtitle' => null
])

<div class="mb-6 flex justify-between items-end">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">{{ $title }}</h1>
        <p class="text-sm text-slate-500 mt-1">{{ $subtitle }}</p>
    </div>
</div>