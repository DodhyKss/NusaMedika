<x-emr-accordion id="acc-pengkajian-up-go" title="F. Pengkajian Up & Go" bgClass="bg-yellow-50" :isOpen="false">
    <div class="text-sm space-y-6">
        
        <!-- Tabel Parameter -->
        <div class="border border-slate-300 rounded-sm overflow-hidden bg-white shadow-sm">
            <table class="w-full text-[13px] text-left border-collapse text-slate-700">
                <thead>
                    <tr class="bg-slate-200">
                        <th class="border border-slate-300 p-2 w-10 text-center font-bold">No</th>
                        <th class="border border-slate-300 p-2 font-bold text-center">Parameter</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-slate-300 p-2 text-center align-top font-medium">1</td>
                        <td class="border border-slate-300 p-3">
                            <div class="mb-2 font-medium">Cara berjalan (salah satu atau lebih)</div>
                            <div class="space-y-2">
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <span class="font-medium mr-1">a.</span>
                                    <input type="checkbox" name="up_go_1_a" class="mt-1 rounded-sm text-blue-500 focus:ring-blue-500 w-4 h-4" {{ ($emr_data ?? '') == 'on' ? 'checked' : '' }}>
                                    <span>Tidak seimbang / sempoyongan / pincang</span>
                                </label>
                                <label class="flex items-start gap-2 cursor-pointer">
                                    <span class="font-medium mr-1">b.</span>
                                    <input type="checkbox" name="up_go_1_b" class="mt-1 rounded-sm text-blue-500 focus:ring-blue-500 w-4 h-4" {{ ($emr_data ?? '') == 'on' ? 'checked' : '' }}>
                                    <span>Jalan dengan menggunakan alat bantu (kruk, tripot, kursi, orang lain)</span>
                                </label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2 text-center align-top font-medium">2</td>
                        <td class="border border-slate-300 p-3">
                            <label class="flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="up_go_2" class="mt-1 rounded-sm text-blue-500 focus:ring-blue-500 w-4 h-4" {{ ($emr_data ?? '') == 'on' ? 'checked' : '' }}>
                                <span>Menopang pada saat akan duduk: tampak memegang pinggiran kursi, meja, atau benda lain sebagi penopang akan duduk</span>
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Tabel Hasil Penilaian -->
        <div class="border border-slate-300 rounded-sm overflow-hidden bg-white shadow-sm">
            <table class="w-full text-[13px] text-left border-collapse text-slate-700">
                <thead>
                    <tr class="bg-slate-200">
                        <th class="border border-slate-300 p-2 w-8 text-center font-bold">No</th>
                        <th class="border border-slate-300 p-2 font-bold">Hasil Penilaian</th>
                        <th class="border border-slate-300 p-2 font-bold">Kriteria Hasil Penilaian</th>
                        <th class="border border-slate-300 p-2 font-bold text-center">Intervensi</th>
                        <th class="border border-slate-300 p-2 w-10 text-center font-bold">Ket</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white">
                        <td class="border border-slate-300 p-2 text-center font-medium">1</td>
                        <td class="border border-slate-300 p-2">Tidak Berisiko</td>
                        <td class="border border-slate-300 p-2">Tidak ditemukan a dan b</td>
                        <td class="border border-slate-300 p-2">Tidak ada Intervensi</td>
                        <td class="border border-slate-300 p-2 text-center text-lg font-bold text-slate-800">✓</td>
                    </tr>
                    <tr class="bg-white">
                        <td class="border border-slate-300 p-2 text-center font-medium">2</td>
                        <td class="border border-slate-300 p-2">Risiko Rendah</td>
                        <td class="border border-slate-300 p-2">Ditemukan salah satu a atau b</td>
                        <td class="border border-slate-300 p-2">Edukasi</td>
                        <td class="border border-slate-300 p-2 text-center"></td>
                    </tr>
                    <tr class="bg-white">
                        <td class="border border-slate-300 p-2 text-center font-medium">3</td>
                        <td class="border border-slate-300 p-2">Risiko Tinggi</td>
                        <td class="border border-slate-300 p-2">Ditemukan a dan b</td>
                        <td class="border border-slate-300 p-2">Edukasi dan ikatkan pita warna kuning pada pergelangan tangan pasien</td>
                        <td class="border border-slate-300 p-2 text-center"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
    </div>
</x-emr-accordion>
