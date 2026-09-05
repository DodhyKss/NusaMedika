<x-emr-accordion id="acc-pengkajian-nyeri" title="D. Pengkajian Nyeri" bgClass="bg-yellow-50" :isOpen="false">
    <div class="text-sm">
        <label class="block font-bold text-slate-800 mb-2">
            Nyeri / Tidak Nyaman <span class="text-red-500 text-[10px] font-normal italic">*pilih yang perlu</span>
        </label>
        <div class="flex gap-4">
            <label class="inline-flex items-center cursor-pointer">
                <input type="radio" name="nyeri" value="ya" class="text-blue-500 focus:ring-blue-500 w-4 h-4" {{ ($emr_data ?? '') == 'ya' ? 'checked' : '' }}>
                <span class="ml-2 text-slate-600">Ya</span>
            </label>
            <label class="inline-flex items-center cursor-pointer">
                <input type="radio" name="nyeri" value="tidak" class="text-blue-500 focus:ring-blue-500 w-4 h-4" {{ ($emr_data ?? 'tidak') == 'tidak' ? 'checked' : '' }}>
                <span class="ml-2 text-slate-600">Tidak</span>
            </label>
        </div>
    </div>
</x-emr-accordion>
