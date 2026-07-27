<x-emr-accordion id="acc-riwayat-penyakit" title="B. Riwayat Penyakit" bgClass="bg-yellow-50" :isOpen="true">
    <div class="space-y-5 text-sm">
        <div>
            <label class="block font-bold text-slate-800 mb-1">Diagnosa Medis</label>
            <input type="text" name="diagnosa_medis" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" placeholder="Masukkan diagnosa medis...">
        </div>
        <div>
            <label class="block font-bold text-slate-800 mb-1">Keluhan Utama</label>
            <textarea name="keluhan_utama" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Ketik keluhan utama..."></textarea>
        </div>
        <div>
            <label class="block font-bold text-slate-800 mb-1">Riwayat Penyakit Sebelumnya</label>
            <textarea name="riwayat_penyakit_sebelumnya" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Ketik riwayat penyakit sebelumnya..."></textarea>
        </div>
        <div>
            <label class="block font-bold text-slate-800 mb-1">Riwayat Penyakit Sekarang</label>
            <textarea name="riwayat_penyakit_sekarang" class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500" rows="2" placeholder="Ketik riwayat penyakit sekarang..."></textarea>
        </div>
        
        <div class="pt-2">
            <h4 class="font-bold text-slate-800 mb-2">Apakah Termasuk Jenis Penyakit Infeksius ? <span class="text-red-500 text-xs font-normal">*</span></h4>
            <div class="flex gap-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="infeksius" value="tidak" class="text-blue-500 focus:ring-blue-500" checked>
                    <span class="ml-2 text-slate-600">Tidak</span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="infeksius" value="ya" class="text-blue-500 focus:ring-blue-500">
                    <span class="ml-2 text-slate-600">Ya</span>
                </label>
            </div>
        </div>
        
        <div class="pt-2">
            <h4 class="font-bold text-slate-800 mb-2">Penurunan Imunologi / Daya Tahan Tubuh ? <span class="text-red-500 text-xs font-normal">*</span></h4>
            <div class="flex gap-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="imunologi" value="tidak" class="text-blue-500 focus:ring-blue-500" checked>
                    <span class="ml-2 text-slate-600">Tidak</span>
                </label>
                <label class="inline-flex items-center cursor-pointer">
                    <input type="radio" name="imunologi" value="ya" class="text-blue-500 focus:ring-blue-500">
                    <span class="ml-2 text-slate-600">Ya</span>
                </label>
            </div>
        </div>
    </div>
</x-emr-accordion>
