@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Tambah Supplier</h1>
        <p class="text-sm text-slate-500 mt-1">Tambahkan data supplier / rekanan penyedia baru.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.supplier.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Form Header -->
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
        <div class="p-2 bg-emerald-100 text-emerald-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </div>
        <div>
            <h2 class="text-base font-semibold text-slate-800">Informasi Supplier</h2>
            <p class="text-xs text-slate-500 mt-0.5">Lengkapi data identitas rekanan penyedia barang.</p>
        </div>
    </div>

    <!-- Form Body -->
    <div class="p-6">
        <form action="{{ route('admin.supplier.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">

                <!-- Nama Supplier -->
                <div>
                    <label for="nama_supplier" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Nama Supplier <span class="text-red-500">*</span></label>
                    <input type="text" id="nama_supplier" name="nama_supplier" value="{{ old('nama_supplier') }}" placeholder="Contoh: PT. Medika Sejahtera" required
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('nama_supplier')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telepon -->
                <div>
                    <label for="telepon" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Telepon</label>
                    <input type="text" id="telepon" name="telepon" value="{{ old('telepon') }}" placeholder="Contoh: 081234567890"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('telepon')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Contoh: sales@medika.co.id"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NPWP -->
                <div>
                    <label for="npwp" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">NPWP</label>
                    <input type="text" id="npwp" name="npwp" value="{{ old('npwp') }}" placeholder="Contoh: 01.234.567.8-901.000"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('npwp')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Alamat -->
            <div class="mt-5">
                <label for="alamat" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Alamat</label>
                <textarea id="alamat" name="alamat" rows="2" placeholder="Alamat lengkap supplier..."
                          class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 resize-none">{{ old('alamat') }}</textarea>
                @error('alamat')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <hr class="my-6 border-slate-200">

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                <a href="{{ route('admin.supplier.index') }}" class="w-full sm:w-auto px-6 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-800 transition-colors shadow-sm text-center">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Simpan Supplier
                </button>
            </div>
        </form>
    </div>
</div>
@endsection