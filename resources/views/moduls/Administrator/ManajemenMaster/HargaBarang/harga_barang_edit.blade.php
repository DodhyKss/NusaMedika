@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Edit Harga Barang</h1>
        <p class="text-sm text-slate-500 mt-1">Perbarui harga beli & jual untuk suatu no. batch.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.harga_barang.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali
        </a>
    </div>
</div>

@if ($errors->any())
    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Form Header -->
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
        <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        </div>
        <div>
            <h2 class="text-base font-semibold text-slate-800">Informasi Harga</h2>
            <p class="text-xs text-slate-500 mt-0.5">Ubah barang, no. batch, atau harga beli/jual.</p>
        </div>
    </div>

    <!-- Form Body -->
    <div class="p-6">
        <form action="{{ route('admin.harga_barang.update', $harga->harga_id) }}" method="POST" id="formEditHarga">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Barang -->
                <div>
                    <label for="barang_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Barang <span class="text-red-500">*</span></label>
                    <select id="barang_id" name="barang_id"
                            class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                        <option value="">— Pilih Barang —</option>
                        @foreach ($barangList as $b)
                            <option value="{{ $b->barang_id }}" @selected((string) old('barang_id', $harga->barang_id) === (string) $b->barang_id)>
                                {{ $b->kode_barang }} — {{ $b->nama_barang }} ({{ $b->satuan->nama_satuan ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('barang_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No. Batch -->
                <div>
                    <label for="no_batch" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">No. Batch <span class="text-red-500">*</span></label>
                    <input type="text" id="no_batch" name="no_batch" value="{{ old('no_batch', $harga->no_batch) }}" placeholder="mis. B001" maxlength="50"
                           class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    @error('no_batch')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Harga Beli -->
                <div>
                    <label for="harga_beli" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Harga Beli</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">Rp</span>
                        <input type="text" id="harga_beli" name="harga_beli" value="{{ number_format((float) old('harga_beli', $harga->harga_beli), 0, ',', '.') }}" placeholder="0" inputmode="numeric"
                               class="w-full text-sm border border-slate-200 rounded-lg pl-9 pr-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    </div>
                    @error('harga_beli')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Harga Jual -->
                <div>
                    <label for="harga_jual" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Harga Jual</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">Rp</span>
                        <input type="text" id="harga_jual" name="harga_jual" value="{{ old('harga_jual', $harga->harga_jual !== null ? number_format((float) $harga->harga_jual, 0, ',', '.') : '') }}" placeholder="0" inputmode="numeric"
                               class="w-full text-sm border border-slate-200 rounded-lg pl-9 pr-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400">
                    </div>
                    @error('harga_jual')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-5 border-t border-slate-200 flex items-center justify-end gap-2">
                <a href="{{ route('admin.harga_barang.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['#harga_beli', '#harga_jual'].forEach(function (sel) {
            var el = document.querySelector(sel);
            if (!el) return;
            el.addEventListener('input', function () {
                var v = this.value.replace(/[^\d]/g, '');
                this.value = v ? Number(v).toLocaleString('id-ID') : '';
            });
        });
        document.getElementById('formEditHarga').addEventListener('submit', function () {
            ['harga_beli', 'harga_jual'].forEach(function (n) {
                var i = document.getElementById(n);
                if (i) i.value = i.value.replace(/[^\d]/g, '');
            });
        });
    });
</script>
@endpush