@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Pendaftaran Rawat Inap</h1>
        <p class="text-sm text-slate-500 mt-1">Daftarkan pasien ke ruang perawatan/bangsal rawat inap.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('list_pelayanan_pasien.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            List Pelayanan
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <form action="#" method="POST" id="formDaftarRanap">
        @csrf
        
        <!-- Section 1: Data Pasien -->
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">1. Identitas Pasien</h2>
                <p class="text-xs text-slate-500 mt-0.5">Cari dan pilih pasien yang akan dirawat inap.</p>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Pilih Pasien -->
                <div class="md:col-span-2">
                    <label for="pasien_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Cari Pasien <span class="text-red-500">*</span></label>
                    <select id="pasien_id" name="pasien_id" class="select2-pasien w-full text-sm border border-slate-200 rounded-lg bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700" style="width: 100%">
                        <option value="">-- Ketik Nama atau No. RM Pasien --</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Ruangan & Layanan -->
        <div class="px-6 py-4 border-y border-slate-200 bg-slate-50/50 flex items-center gap-3 mt-2">
            <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">2. Ruang Perawatan & DPJP</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tentukan ruangan, kelas, bed, dan dokter penanggung jawab.</p>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Tanggal Masuk -->
                <div>
                    <label for="tgl_masuk" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal & Waktu Masuk <span class="text-red-500">*</span></label>
                    <input type="datetime-local" id="tgl_masuk" name="tgl_masuk" value="{{ date('Y-m-d\TH:i') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                </div>

                <!-- Dokter DPJP -->
                <div>
                    <label for="dokter_dpjp" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Dokter (DPJP) <span class="text-red-500">*</span></label>
                    <select id="dokter_dpjp" name="dokter_dpjp" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Dokter --</option>
                        <option value="1">dr. Andi Saputra, Sp.PD</option>
                        <option value="2">dr. Siti Aminah, Sp.A</option>
                        <option value="3">dr. Budi Santoso, Sp.B</option>
                    </select>
                </div>

                <!-- Ruangan / Bangsal -->
                <div>
                    <label for="ruangan_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Ruangan / Bangsal <span class="text-red-500">*</span></label>
                    <select id="ruangan_id" name="ruangan_id" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Ruangan --</option>
                        <option value="1">Ruang Melati (Penyakit Dalam)</option>
                        <option value="2">Ruang Mawar (Kebidanan)</option>
                        <option value="3">Ruang Anggrek (Anak)</option>
                        <option value="4">ICU / HCU</option>
                    </select>
                </div>

                <!-- Kelas Perawatan -->
                <div>
                    <label for="kelas_perawatan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Kelas Perawatan <span class="text-red-500">*</span></label>
                    <select id="kelas_perawatan" name="kelas_perawatan" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Kelas --</option>
                        <option value="Kelas 1">Kelas 1</option>
                        <option value="Kelas 2">Kelas 2</option>
                        <option value="Kelas 3">Kelas 3</option>
                        <option value="VIP">VIP</option>
                        <option value="VVIP">VVIP</option>
                    </select>
                </div>

                <!-- Nomor Bed -->
                <div>
                    <label for="nomor_bed" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nomor Bed <span class="text-red-500">*</span></label>
                    <select id="nomor_bed" name="nomor_bed" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Bed Tersedia --</option>
                        <option value="Bed 1">Bed 1 (Kosong)</option>
                        <option value="Bed 2">Bed 2 (Kosong)</option>
                        <option value="Bed 3">Bed 3 (Kosong)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 3: Penjamin & Penanggung Jawab -->
        <div class="px-6 py-4 border-y border-slate-200 bg-slate-50/50 flex items-center gap-3 mt-2">
            <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">3. Penjamin & Penanggung Jawab</h2>
                <p class="text-xs text-slate-500 mt-0.5">Informasi asuransi, keluarga terdekat, dan diagnosa awal.</p>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Jenis Penjamin -->
                <div>
                    <label for="jenis_penjamin" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Jenis Penjamin <span class="text-red-500">*</span></label>
                    <select id="jenis_penjamin" name="jenis_penjamin" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Penjamin --</option>
                        <option value="Umum">Umum / Mandiri</option>
                        <option value="BPJS Kesehatan">BPJS Kesehatan</option>
                        <option value="Asuransi Swasta">Asuransi Swasta</option>
                        <option value="Perusahaan">Perusahaan</option>
                    </select>
                </div>

                <!-- Asal Masuk (Rujukan/IGD) -->
                <div>
                    <label for="asal_masuk" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Asal Masuk <span class="text-red-500">*</span></label>
                    <select id="asal_masuk" name="asal_masuk" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Asal --</option>
                        <option value="IGD">Instalasi Gawat Darurat (IGD)</option>
                        <option value="Poliklinik">Poliklinik (Rawat Jalan)</option>
                        <option value="Rujukan Luar">Rujukan RS/Klinik Lain</option>
                    </select>
                </div>

                <!-- Nama Penanggung Jawab -->
                <div>
                    <label for="nama_pj" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Penanggung Jawab <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_pj" name="nama_pj" placeholder="Nama lengkap keluarga/kerabat" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Hubungan dengan Pasien -->
                <div>
                    <label for="hubungan_pj" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Hubungan dengan Pasien <span class="text-red-500">*</span></label>
                    <select id="hubungan_pj" name="hubungan_pj" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Hubungan --</option>
                        <option value="Suami">Suami</option>
                        <option value="Istri">Istri</option>
                        <option value="Anak">Anak</option>
                        <option value="Orang Tua">Orang Tua</option>
                        <option value="Saudara Kandung">Saudara Kandung</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <!-- No. HP Penanggung Jawab -->
                <div>
                    <label for="nohp_pj" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. HP Penanggung Jawab <span class="text-red-500">*</span></label>
                    <input type="text" id="nohp_pj" name="nohp_pj" placeholder="08xxxxxxxxxx" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Diagnosa Awal / Alasan Masuk -->
                <div class="md:col-span-2">
                    <label for="diagnosa_awal" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Diagnosa Awal / Indikasi Rawat Inap <span class="text-red-500">*</span></label>
                    <textarea id="diagnosa_awal" name="diagnosa_awal" rows="3" placeholder="Masukkan diagnosa awal atau alasan medis pasien dirawat inap..." 
                              class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 resize-none"></textarea>
                </div>
            </div>
            
            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="reset" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
                    Reset Form
                </button>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan & Daftarkan Ranap
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    /* Custom select chevron */
    select {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
    }
</style>
@endsection
