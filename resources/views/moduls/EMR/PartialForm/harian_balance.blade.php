@php
    // Partial: Balance cairan, eliminasi, serta intake (Pengkajian Harian).
    @endphp

<x-emr-accordion id="acc-harian-balance" title="D. Balance Cairan, Eliminasi dan Intake" bgClass="bg-slate-100" :isOpen="false">
    <div class="text-sm">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label for="intake_cairan" class="block font-bold text-slate-800 text-xs mb-1">Intake Cairan</label>
                <div class="flex items-end gap-1.5">
                    <input type="number" step="1" min="0" id="intake_cairan" name="intake_cairan"
                           value="{{ $emr_data['intake_cairan'] ?? '' }}" placeholder="0"
                           class="form-input w-full text-sm text-right border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none px-1 py-1 bg-transparent focus:border-blue-500 focus:ring-0">
                    <span class="text-slate-600 text-xs mb-1 whitespace-nowrap">ml</span>
                </div>
            </div>
            <div>
                <label for="eliminasi" class="block font-bold text-slate-800 text-xs mb-1">Eliminasi</label>
                <div class="flex items-end gap-1.5">
                    <input type="number" step="1" min="0" id="eliminasi" name="eliminasi"
                           value="{{ $emr_data['eliminasi'] ?? '' }}" placeholder="0"
                           class="form-input w-full text-sm text-right border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none px-1 py-1 bg-transparent focus:border-blue-500 focus:ring-0">
                    <span class="text-slate-600 text-xs mb-1">Bj</span>
                </div>
            </div>
            <div>
                <label for="intake_makanan" class="block font-bold text-slate-800 text-xs mb-1">Intake Makanan</label>
                <div class="flex items-end gap-1.5">
                    <input type="number" step="1" min="0" max="100" id="intake_makanan" name="intake_makanan"
                           value="{{ $emr_data['intake_makanan'] ?? '' }}" placeholder="0"
                           class="form-input w-full text-sm text-right border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none px-1 py-1 bg-transparent focus:border-blue-500 focus:ring-0">
                    <span class="text-slate-600 text-xs mb-1">%</span>
                </div>
            </div>
            <div>
                <label for="tidur" class="block font-bold text-slate-800 text-xs mb-1">Tidur</label>
                <div class="flex items-end gap-1.5">
                    <input type="number" step="0.5" min="0" id="tidur" name="tidur"
                           value="{{ $emr_data['tidur'] ?? '' }}" placeholder="0"
                           class="form-input w-full text-sm text-right border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none px-1 py-1 bg-transparent focus:border-blue-500 focus:ring-0">
                    <span class="text-slate-600 text-xs mb-1">jam</span>
                </div>
            </div>
        </div>

        <div id="harian-balance-notif" class="mt-4 hidden px-4 py-2.5 rounded-lg text-xs font-medium"></div>

        <div class="mt-4">
            <label for="catatan_tambahan" class="block font-bold text-slate-800 mb-1.5">Catatan Tambahan</label>
            <textarea id="catatan_tambahan" name="catatan_tambahan" rows="2"
                      placeholder="Catatan lain yang tidak tercakup di atas"
                      class="form-input w-full border border-slate-300 rounded px-3 py-2 focus:border-blue-500 focus:ring-blue-500">{{ $emr_data['catatan_tambahan'] ?? '' }}</textarea>
        </div>
    </div>
</x-emr-accordion>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var intake = document.getElementById('intake_cairan');
    var eliminasi = document.getElementById('eliminasi');
    var notif = document.getElementById('harian-balance-notif');
    if (!intake || !eliminasi || !notif) return;

    function cekBalance() {
        var i = parseFloat(intake.value);
        var e = parseFloat(eliminasi.value);

        if (isNaN(i) && isNaN(e)) {
            notif.classList.add('hidden');
            return;
        }

        var selisih = (isNaN(i) ? 0 : i) - (isNaN(e) ? 0 : e);
        var pesan = '';

        if (selisih < -500) {
            pesan = 'Balance cairan negatif (' + selisih + ' ml). Periksa intake dan pastikan Eliminasi.';
        } else if (selisih > 500) {
            pesan = 'Balance cairan positif (' + selisih + ' ml). Waspadai edema.';
        }

        if (pesan) {
            notif.textContent = pesan;
            notif.className = 'mt-4 px-4 py-2.5 rounded-lg text-xs font-medium ' +
                (selisih < -500 ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200');
        } else {
            notif.className = 'mt-4 px-4 py-2.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200';
            notif.textContent = 'Balance cairan dalam rentang wajar.';
        }
    }

    intake.addEventListener('input', cekBalance);
    eliminasi.addEventListener('input', cekBalance);
    cekBalance();
});
</script>