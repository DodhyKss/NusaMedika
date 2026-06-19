@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Pendaftaran Pasien Baru</h1>
        <p class="text-sm text-slate-500 mt-1">Formulir untuk mendaftarkan data rekam medis pasien baru ke dalam sistem.</p>
    </div>
    <div class="flex gap-2">
        <a href="#" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Form Header -->
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </div>
        <div>
            <h2 class="text-base font-semibold text-slate-800">Identitas Diri Pasien</h2>
            <p class="text-xs text-slate-500 mt-0.5">Isi data dengan lengkap dan benar sesuai kartu identitas (KTP/KK).</p>
        </div>
    </div>

    <!-- Form Body -->
    <div class="p-6">
        <form action="#" method="POST" id="formDaftarPasien">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                
                <!-- NIK -->
                <div>
                    <label for="nik" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. KTP / NIK <span class="text-red-500">*</span></label>
                    <input type="text" id="nik" name="nik" placeholder="Masukkan 16 digit NIK" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Nama Lengkap -->
                <div>
                    <label for="nama_lengkap" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama sesuai KTP" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label for="tempat_lahir" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" placeholder="Kota / Kabupaten" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label for="tanggal_lahir" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal Lahir <span class="text-red-500">*</span></label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label for="jenis_kelamin" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select id="jenis_kelamin" name="jenis_kelamin" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <!-- Agama -->
                <div>
                    <label for="agama" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Agama</label>
                    <select id="agama" name="agama" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Agama --</option>
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen Protestan</option>
                        <option value="Katolik">Katolik</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Buddha">Buddha</option>
                        <option value="Konghucu">Konghucu</option>
                    </select>
                </div>

                <!-- No HP -->
                <div>
                    <label for="no_hp" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. Handphone (WA) <span class="text-red-500">*</span></label>
                    <input type="text" id="no_hp" name="no_hp" placeholder="Contoh: 081234567890" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Nama Ibu Kandung -->
                <div>
                    <label for="nama_ibu" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Ibu Kandung</label>
                    <input type="text" id="nama_ibu" name="nama_ibu" placeholder="Nama Ibu" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Golongan Darah -->
                <div>
                    <label for="gol_darah" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Golongan Darah</label>
                    <select id="gol_darah" name="gol_darah" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Gol. Darah --</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="AB">AB</option>
                        <option value="O">O</option>
                    </select>
                </div>

                <!-- Status Pernikahan -->
                <div>
                    <label for="status_pernikahan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Status Pernikahan</label>
                    <select id="status_pernikahan" name="status_pernikahan" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Status --</option>
                        <option value="Belum Kawin">Belum Kawin</option>
                        <option value="Kawin">Kawin</option>
                        <option value="Cerai Hidup">Cerai Hidup</option>
                        <option value="Cerai Mati">Cerai Mati</option>
                    </select>
                </div>

                <!-- Alamat Lengkap -->
                <div class="md:col-span-2 mt-2">
                    <label for="alamat" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Alamat Lengkap KTP <span class="text-red-500">*</span></label>
                    <textarea id="alamat" name="alamat" rows="3" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan" 
                              class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 resize-none"></textarea>
                </div>
            </div>

            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <button type="reset" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm">
                    Reset Form
                </button>
                <button type="button" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Simpan Data Pasien
                </button>
            </div>
        </form>
    </div>
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