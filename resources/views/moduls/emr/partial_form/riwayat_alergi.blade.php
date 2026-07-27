<x-emr-accordion id="acc-riwayat-alergi" title="E. Riwayat Alergi" bgClass="bg-slate-100" :isOpen="true">
    <div class="text-sm">
        <label class="block font-bold text-slate-800 mb-2">
            Riwayat Alergi <span class="text-red-500 text-[10px] font-normal italic">*pilih yang perlu</span>
        </label>
        <div class="flex gap-4">
            <label class="inline-flex items-center cursor-pointer">
                <input type="radio" name="alergi" value="ada" class="text-blue-500 focus:ring-blue-500 w-4 h-4">
                <span class="ml-2 text-slate-600">Ada</span>
            </label>
            <label class="inline-flex items-center cursor-pointer">
                <input type="radio" name="alergi" value="tidak" class="text-blue-500 focus:ring-blue-500 w-4 h-4" checked>
                <span class="ml-2 text-slate-600">Tidak</span>
            </label>
        </div>
    </div>
</x-emr-accordion>
