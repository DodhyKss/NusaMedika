<x-emr-accordion id="acc-pemeriksaan-fisik" title="C. Pemeriksaan Fisik" bgClass="bg-slate-100" :isOpen="false">
    <div class="text-sm space-y-6">
        <div class="flex items-center gap-2">
            <label class="font-bold text-slate-800">Kesadaran :</label> 
            <input type="text" name="kesadaran" value="{{ $emr_data[env('OBJEK_ID_KESADARAN')]['kesadaran'] ?? '' }}" class="form-input border border-slate-300 rounded w-64 h-8 px-2 focus:border-blue-500 focus:ring-blue-500" placeholder="Compos Mentis (Alert)">
        </div>

        <!-- GCS Table -->
        <div class="border border-slate-300 rounded-sm overflow-hidden">
            <div class="bg-[#5da9e9] text-white font-bold p-2.5 text-xs uppercase tracking-wide">
                GLASCOW COMA SCALE <span class="font-normal italic lowercase">( Tandai Yang Paling Sesuai )</span>
            </div>
            <table class="w-full text-xs text-left border-collapse text-slate-700">
                <tbody>
                    <!-- EYE -->
                    <tr>
                        <td rowspan="4" class="border border-slate-300 p-2 text-center font-bold w-24">EYE<br>(E)</td>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_e" class="text-slate-800 focus:ring-slate-800" value="4" {{ ($emr_data[env('OBJEK_ID_GCS_E')]['gcs_e'] ?? '') == '4' ? 'checked' : '' }}> Buka Mata Spontan</label></td>
                        <td class="border border-slate-300 p-2 text-center w-12 font-medium">4</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_e" class="text-slate-800 focus:ring-slate-800" value="3" {{ ($emr_data[env('OBJEK_ID_GCS_E')]['gcs_e'] ?? '') == '3' ? 'checked' : '' }}> Buka Mata Bila Dirangsang Suara</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">3</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_e" class="text-slate-800 focus:ring-slate-800" value="2" {{ ($emr_data[env('OBJEK_ID_GCS_E')]['gcs_e'] ?? '') == '2' ? 'checked' : '' }}> Buka Mata Bila Dirangsang Nyeri</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">2</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_e" class="text-slate-800 focus:ring-slate-800" value="1" {{ ($emr_data[env('OBJEK_ID_GCS_E')]['gcs_e'] ?? '') == '1' ? 'checked' : '' }}> Tidak Bisa Buka Mata Dengan Dirangsang Apapun</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">1</td>
                    </tr>
                    <!-- MOTORIK -->
                    <tr>
                        <td rowspan="6" class="border border-slate-300 p-2 text-center font-bold w-24">MOTORIK<br>(M)</td>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_m" class="text-slate-800 focus:ring-slate-800" value="6" {{ ($emr_data[env('OBJEK_ID_GCS_M')]['gcs_m'] ?? '') == '6' ? 'checked' : '' }}> Mengikuti Perintah</label></td>
                        <td class="border border-slate-300 p-2 text-center w-12 font-medium">6</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_m" class="text-slate-800 focus:ring-slate-800" value="5" {{ ($emr_data[env('OBJEK_ID_GCS_M')]['gcs_m'] ?? '') == '5' ? 'checked' : '' }}> Mengetahui Tempat Rangsang Nyeri</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">5</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_m" class="text-slate-800 focus:ring-slate-800" value="4" {{ ($emr_data[env('OBJEK_ID_GCS_M')]['gcs_m'] ?? '') == '4' ? 'checked' : '' }}> Hanya Menarik Bagian Tubuh Bila Dirangsang</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">4</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_m" class="text-slate-800 focus:ring-slate-800" value="3" {{ ($emr_data[env('OBJEK_ID_GCS_M')]['gcs_m'] ?? '') == '3' ? 'checked' : '' }}> Timbul Fleksi Abnormal Bila Diberi Rangsang Nyeri</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">3</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_m" class="text-slate-800 focus:ring-slate-800" value="2" {{ ($emr_data[env('OBJEK_ID_GCS_M')]['gcs_m'] ?? '') == '2' ? 'checked' : '' }}> Timbul Ekstensi Abnormal Bila Diberi Rangsang Nyeri</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">2</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_m" class="text-slate-800 focus:ring-slate-800" value="1" {{ ($emr_data[env('OBJEK_ID_GCS_M')]['gcs_m'] ?? '') == '1' ? 'checked' : '' }}> Tidak Ada Gerakan Dengan Rangsang Apapun</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">1</td>
                    </tr>
                    <!-- VERBAL -->
                    <tr>
                        <td rowspan="5" class="border border-slate-300 p-2 text-center font-bold w-24">VERBAL<br>(V)</td>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_v" class="text-slate-800 focus:ring-slate-800" value="5" {{ ($emr_data[env('OBJEK_ID_GCS_V')]['gcs_v'] ?? '') == '5' ? 'checked' : '' }}> Komunikasi Verbal Baik, Jawaban Baik</label></td>
                        <td class="border border-slate-300 p-2 text-center w-12 font-medium">5</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_v" class="text-slate-800 focus:ring-slate-800" value="4" {{ ($emr_data[env('OBJEK_ID_GCS_V')]['gcs_v'] ?? '') == '4' ? 'checked' : '' }}> Bingung, Disorientasi Waktu, Orang, Dan Tempat</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">4</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_v" class="text-slate-800 focus:ring-slate-800" value="3" {{ ($emr_data[env('OBJEK_ID_GCS_V')]['gcs_v'] ?? '') == '3' ? 'checked' : '' }}> Dengan Rangsangan Hanya Ada Kata-kata</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">3</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_v" class="text-slate-800 focus:ring-slate-800" value="2" {{ ($emr_data[env('OBJEK_ID_GCS_V')]['gcs_v'] ?? '') == '2' ? 'checked' : '' }}> Dengan Rangsangan Hanya Keluar Suara</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">2</td>
                    </tr>
                    <tr>
                        <td class="border border-slate-300 p-2"><label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="gcs_v" class="text-slate-800 focus:ring-slate-800" value="1" {{ ($emr_data[env('OBJEK_ID_GCS_V')]['gcs_v'] ?? '') == '1' ? 'checked' : '' }}> Timbul Ekstensi Abnormal Bila Diberi Rangsang Nyeri</label></td>
                        <td class="border border-slate-300 p-2 text-center font-medium">1</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <label class="font-bold flex items-center gap-2 text-sm text-slate-800 cursor-pointer w-max">
                Dalam Pengaruh Obat (DPO) : <input type="checkbox" name="dpo" value="1" class="rounded text-slate-800 focus:ring-slate-800 w-4 h-4" {{ ($emr_data[env('OBJEK_ID_DPO')]['dpo'] ?? '') == '1' ? 'checked' : '' }}> <span class="font-normal text-slate-600">DPO</span>
            </label>
        </div>

        <div class="flex flex-col items-center border-t border-slate-200 pt-6 my-4 border-b pb-6">
            <span class="text-xs tracking-widest font-semibold mb-2 text-slate-600">JUMLAH</span>
            <input type="text" name="gcs_jumlah" value="{{ $emr_data[env('OBJEK_ID_GCS_SCORE')]['gcs_jumlah'] ?? '' }}" readonly value="" placeholder="0" class="bg-[#d0e3f2] border-none text-3xl font-bold py-4 px-4 rounded-sm text-center text-[#1e466b] shadow-inner w-32 focus:ring-0">
        </div>

        <!-- Vitals Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-y-6 gap-x-8">
            <!-- Row 1 -->
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">Tekanan Darah Ini</label>
                <div class="flex items-end gap-2 mt-2">
                    <input type="text" name="td" value="{{ $emr_data[env('OBJEK_ID_SISTOLIK')]['td'] ?? '' }}" class="form-input w-20 border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-1 text-sm bg-transparent" placeholder="">
                    <span class="text-slate-600 text-xs mb-1">/ mmHg</span>
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">Nadi</label>
                <div class="flex items-end gap-2 mt-2">
                    <input type="text" name="nadi" value="{{ $emr_data[env('OBJEK_ID_NADI')]['nadi'] ?? '' }}" class="form-input w-20 border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-1 text-sm bg-transparent" placeholder="">
                    <span class="text-slate-600 text-xs mb-1">x/Menit</span>
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">Suhu</label>
                <div class="flex items-end gap-2 mt-2">
                    <input type="text" name="suhu" value="{{ $emr_data[env('OBJEK_ID_SUHU')]['suhu'] ?? '' }}" class="form-input w-20 border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-1 text-sm bg-transparent" placeholder="">
                    <span class="text-slate-600 text-xs mb-1">°C</span>
                </div>
            </div>

            <!-- Row 2 -->
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">Pernapasan</label>
                <div class="flex items-end gap-2 mt-2">
                    <input type="text" name="pernapasan" value="{{ $emr_data[env('OBJEK_ID_PERNAPASAN')]['pernapasan'] ?? '' }}" class="form-input w-20 border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-1 text-sm bg-transparent" placeholder="">
                    <span class="text-slate-600 text-xs mb-1">x/Menit</span>
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">Berat Badan</label>
                <div class="flex items-end gap-2 mt-2">
                    <input type="text" name="berat_badan" value="{{ $emr_data[env('OBJEK_ID_BERAT_BADAN')]['berat_badan'] ?? '' }}" class="form-input w-20 border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-1 text-sm bg-transparent" placeholder="">
                    <span class="text-slate-600 text-xs mb-1">Kg</span>
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">Tinggi Badan</label>
                <div class="flex items-end gap-2 mt-2">
                    <input type="text" name="tinggi_badan" value="{{ $emr_data[env('OBJEK_ID_TINGGI_BADAN')]['tinggi_badan'] ?? '' }}" class="form-input w-20 border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-1 text-sm bg-transparent">
                    <span class="text-slate-600 text-xs mb-1">Cm</span>
                </div>
            </div>

            <!-- Row 3 -->
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">Pemberian O2</label>
                <div class="flex items-end gap-2 mt-2">
                    <input type="text" name="pemberian_o2" value="{{ $emr_data[env('OBJEK_ID_OKSIGEN')]['pemberian_o2'] ?? '' }}" class="form-input w-20 border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-1 text-sm bg-transparent">
                    <span class="text-slate-600 text-xs mb-1">L/Mnt</span>
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">Cara Pemberian</label>
                <div class="flex items-end gap-2 mt-2">
                    <input type="text" name="cara_pemberian_o2" value="{{ $emr_data[env('OBJEK_ID_CARA_PEMBERIAN')]['cara_pemberian_o2'] ?? '' }}" class="form-input w-20 border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-1 text-sm bg-transparent" placeholder="">
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">ETT</label>
                <div class="flex items-end gap-2 mt-2">
                    <input type="text" name="ett" value="{{ $emr_data[env('OBJEK_ID_ETT')]['ett'] ?? '' }}" class="form-input w-20 border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-1 text-sm bg-transparent" placeholder="">
                    <span class="text-slate-600 text-xs mb-1">%</span>
                </div>
            </div>

            <!-- Row 4 -->
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">Saturasi</label>
                <div class="flex items-end gap-2 mt-2">
                    <input type="text" name="saturasi" value="{{ $emr_data[env('OBJEK_ID_SATURASI')]['saturasi'] ?? '' }}" class="form-input w-20 border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-1 text-sm bg-transparent" placeholder="">
                    <span class="text-slate-600 text-xs mb-1">%</span>
                </div>
            </div>
            <div>
                <label class="block font-bold text-slate-800 text-xs mb-1">EWS</label>
                <div class="flex items-end gap-2 mt-2">
                    <input type="text" name="ews" value="{{ $emr_data[env('OBJEK_ID_EWS')]['ews'] ?? '' }}" class="form-input w-20 border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-1 text-sm bg-transparent" placeholder="">
                </div>
            </div>
            <div>
                <div class="flex items-center gap-2 mt-4 mb-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="allo_anamnesa" class="rounded text-slate-800 focus:ring-slate-800 w-3 h-3" {{ ($emr_data[env('OBJEK_ID_ALLO_ANAMNESA')]['allo_anamnesa'] ?? '') == 'on' ? 'checked' : '' }}> 
                        <span class="font-bold text-slate-800 text-xs">Allo Anamnesa</span>
                    </label>
                </div>
                <div class="flex items-center gap-2 text-slate-600 text-xs">
                    <span>Nama:</span> <input type="text" name="nama_allo" value="{{ $emr_data[env('OBJEK_ID_NAMA_ALLO')]['nama_allo'] ?? '' }}" class="form-input border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-0 h-5 w-20 bg-transparent">, 
                    <span>Hubungan:</span> <input type="text" name="hubungan_allo" value="{{ $emr_data[env('OBJEK_ID_HUBUNGAN_ALLO')]['hubungan_allo'] ?? '' }}" class="form-input border-b-2 border-t-0 border-l-0 border-r-0 border-slate-300 rounded-none shadow-none focus:ring-0 focus:border-blue-500 px-1 py-0 h-5 w-20 bg-transparent">
                </div>
            </div>
        </div>

        <div class="pt-4 flex items-center gap-4">
            <h4 class="font-bold text-slate-800 text-xs mb-0">Skrining Status Nutrisi</h4>
            <div class="flex gap-2 items-center">
                <span class="text-slate-600 text-xs">BMI</span>
                <input type="text" name="bmi" value="{{ $emr_data[env('OBJEK_ID_BMI')]['bmi'] ?? '' }}" class="form-input w-16 h-7 text-sm font-bold border border-slate-300 rounded focus:border-blue-500 focus:ring-blue-500" placeholder="0"> 
            </div>
        </div>
    </div>
</x-emr-accordion>
