@php
    // Partial: Keluhan & catatan harian (Pengkajian Harian Keperawatan).
@endphp

<x-emr-accordion id="acc-harian-keluhan" title="A. Keluhan dan Catatan" bgClass="bg-slate-100" :isOpen="true">
    <div class="text-sm space-y-4">

        <div>
            <label for="keluhan" class="block font-bold text-slate-800 mb-1.5">
                Keluhan Utama Hari Ini <span class="text-red-500 text-[10px] font-normal italic">*wajib diisi</span>
            </label>
            <textarea id="keluhan" name="keluhan" rows="2"
                      placeholder="Contoh: Batuk ringan sejak dua hari, tidak disertai sesak napas"
                      class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500">{{ $emr_data['keluhan'] ?? '' }}</textarea>
        </div>

        <div>
            <label for="catatan_keperawatan" class="block font-bold text-slate-800 mb-1.5">Catatan Keperawatan</label>
            <textarea id="catatan_keperawatan" name="catatan_keperawatan" rows="3"
                      placeholder="Perubahan kondisi, tindakan yang dilakukan, dan rencana keperawatan"
                      class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500">{{ $emr_data['catatan_keperawatan'] ?? '' }}</textarea>
        </div>

        <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-100 rounded-lg">
            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <p class="text-xs text-blue-700">
                Pengkajian harian diisi setiap shift. Data yang sudah tersimpan tetap dapat diubah selama status order belum final.
            </p>
        </div>
    </div>
</x-emr-accordion>