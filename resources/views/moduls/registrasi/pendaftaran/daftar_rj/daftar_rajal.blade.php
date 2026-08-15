@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Pendaftaran Rawat Jalan</h1>
        <p class="text-sm text-slate-500 mt-1">Daftarkan pasien untuk mendapatkan layanan di poliklinik tujuan.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('list_pelayanan_pasien.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            List Pelayanan
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <form action="{{ route('daftar_rajal.store') }}" method="POST" id="formDaftarRajal">
        @csrf
        
        <!-- Section 1: Data Pasien -->
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
            <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">1. Identitas Pasien</h2>
                <p class="text-xs text-slate-500 mt-0.5">Cari dan pilih pasien yang akan didaftarkan.</p>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Pilih Pasien -->
                <div class="md:col-span-2">
                    <x-select_pasien label="Cari Pasien" required />
                </div>
            </div>
        </div>

        <!-- Section 2: Layanan & Poliklinik -->
        <div class="px-6 py-4 border-y border-slate-200 bg-slate-50/50 flex items-center gap-3 mt-2">
            <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">2. Tujuan Layanan & Dokter</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tentukan poliklinik tujuan dan jadwal dokter.</p>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Tanggal Kunjungan -->
                <div>
                    <label for="tgl_kunjungan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tanggal Kunjungan <span class="text-red-500">*</span></label>
                    <input type="date" id="tgl_kunjungan" name="tgl_kunjungan" value="{{ date('Y-m-d') }}"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                </div>



                <!-- Poliklinik -->
                <div>
                    <label for="poliklinik" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Poliklinik Tujuan <span class="text-red-500">*</span></label>
                    <select id="poliklinik" name="poliklinik" 
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Poliklinik --</option>
                        @foreach ($polikliniks as $poliklinik)
                            <option value="{{ $poliklinik->bagian_id }}" @selected((string) old('poliklinik') === (string) $poliklinik->bagian_id)>{{ $poliklinik->nama_bagian }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Jadwal Dokter -->
                <div>
                    <label for="jadwal_dokter_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Jadwal Dokter <span class="text-red-500">*</span></label>
                    <select id="jadwal_dokter_id" name="jadwal_dokter_id" required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none disabled:bg-slate-100 disabled:text-slate-400">
                        <option value="">-- Pilih Jadwal Dokter --</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 3: Penjamin & Keluhan -->
        <div class="px-6 py-4 border-y border-slate-200 bg-slate-50/50 flex items-center gap-3 mt-2">
            <div class="p-2 bg-amber-100 text-amber-600 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">3. Penjamin & Informasi Tambahan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Lengkapi data asuransi dan keluhan pasien.</p>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <!-- Nasabah -->
                <div>
                    <label for="nasabah_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nasabah / Penjamin <span class="text-red-500">*</span></label>
                    <select id="nasabah_id" name="nasabah_id" required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        <option value="">-- Pilih Nasabah --</option>
                        @foreach ($nasabahs as $nasabah)
                            <option value="{{ $nasabah->nasabah_id }}" @selected((string) old('nasabah_id') === (string) $nasabah->nasabah_id)>{{ $nasabah->nama_nasabah }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Cara Pasien Datang -->
                <div>
                    <label for="cara_masuk" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Cara Pasien Datang <span class="text-red-500">*</span></label>
                    <select id="cara_masuk" name="cara_masuk" required
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none">
                        {!! \App\Helpers\SelectOption::render('cara_masuk', old('cara_masuk'), '-- Pilih Cara Datang --') !!}
                    </select>
                </div>

                <!-- Diagnosa (ICD) -->
                <div>
                    <label for="icd_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Diagnosa Awal (ICD) <span class="text-red-500">*</span></label>
                    <select id="icd_id" name="icd_id" class="select2-icd w-full" data-url="{{ route('api.icd.search') }}" required>
                        <!-- Options akan dimuat via AJAX -->
                    </select>
                </div>

                <!-- Nomor Rujukan (Opsional) -->
                <div>
                    <label for="no_rujukan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. Rujukan / Surat Kontrol</label>
                    <input type="text" id="no_rujukan" name="no_rujukan" placeholder="Masukkan nomor rujukan jika ada" 
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                </div>

                <!-- Keluhan / Alasan Kunjungan -->
                <div class="md:col-span-2">
                    <label for="keluhan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Keluhan Awal / Alasan Kunjungan <span class="text-red-500">*</span></label>
                    <textarea id="keluhan" name="keluhan" rows="3" placeholder="Deskripsikan keluhan utama pasien secara singkat..." 
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
                    Simpan & Daftarkan
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const poliklinik = document.getElementById('poliklinik');
        const jadwalDokter = document.getElementById('jadwal_dokter_id');
        const tglKunjungan = document.getElementById('tgl_kunjungan');
        if (!poliklinik || !jadwalDokter || !tglKunjungan) return;

        const jadwalsByPoli = @json($jadwalsByPoli);
        const oldJadwal = "{{ old('jadwal_dokter_id') }}";

        function updateJadwal() {
            const bagian = poliklinik.value;
            jadwalDokter.innerHTML = '<option value="">-- Pilih Jadwal Dokter --</option>';
            jadwalDokter.disabled = true;

            let selectedDay = null;
            if (tglKunjungan.value) {
                const d = new Date(tglKunjungan.value);
                if (!isNaN(d.getTime())) {
                    const jsDay = d.getDay(); // 0 = Minggu, 1 = Senin, dll
                    selectedDay = jsDay === 0 ? 7 : jsDay;
                }
            }

            if (bagian && jadwalsByPoli[bagian]) {
                const jadwals = jadwalsByPoli[bagian];
                let adaJadwal = false;
                jadwals.forEach(j => {
                    // Filter berdasarkan hari dari tanggal kunjungan
                    if (selectedDay !== null && j.hari != selectedDay) return;

                    const selected = (oldJadwal == j.id) ? 'selected' : '';
                    const optionText = `${j.dokter} | ${j.nama_hari} (${j.waktu}) | Kuota: ${j.kuota}`;
                    jadwalDokter.innerHTML += `<option value="${j.id}" ${selected}>${optionText}</option>`;
                    adaJadwal = true;
                });
                if (adaJadwal) {
                    jadwalDokter.disabled = false;
                }
            }
        }

        poliklinik.addEventListener('change', updateJadwal);
        tglKunjungan.addEventListener('change', updateJadwal);

        // Init on load if old value exists
        if (poliklinik.value) {
            updateJadwal();
        }
    });
</script>
@endsection
