@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <a href="{{ route('admin.notifikasi.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Kembali ke Daftar
    </a>
    <h1 class="text-[22px] font-bold text-slate-900 tracking-tight mt-2">Kirim Notifikasi</h1>
    <p class="text-sm text-slate-500 mt-1">Notifikasi akan muncul di lonceng navbar penerima sampai mereka menutupnya satu per satu.</p>
</div>

<form action="{{ route('admin.notifikasi.store') }}" method="POST" id="formNotifikasi">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom kiri: isi -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-slate-700 mb-4">Isi Notifikasi</h2>

                <div class="space-y-4">
                    <div>
                        <label for="judul" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Judul <span class="text-red-500">*</span></label>
                        <input type="text" id="judul" name="judul" required maxlength="150" value="{{ old('judul') }}" placeholder="Contoh: Ronde Malam Dihapus"
                               class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 @error('judul') border-red-400 @enderror">
                        @error('judul')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="pesan" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Isi Pesan <span class="text-red-500">*</span></label>
                        <textarea id="pesan" name="pesan" rows="6" required placeholder="Tuliskan isi notifikasi yang akan dibaca pengguna..."
                                  class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder-slate-400 @error('pesan') border-red-400 @enderror">{{ old('pesan') }}</textarea>
                        @error('pesan')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="prioritas" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Prioritas</label>
                        <select id="prioritas" name="prioritas" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700">
                            @foreach (\App\Models\Notifikasi::PRIORITAS as $p)
                                <option value="{{ $p }}" @selected(old('prioritas', 'INFO') === $p)>{{ $p }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-400">URGENT ditampilkan merah, PENTING kuning, INFO netral.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom kanan: target -->
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h2 class="text-sm font-semibold text-slate-700 mb-4">Penerima</h2>

                <div class="space-y-4">
                    <div>
                        <label for="tipe_target" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Tipe Target <span class="text-red-500">*</span></label>
                        <select id="tipe_target" name="tipe_target" required
                                class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 @error('tipe_target') border-red-400 @enderror">
                            <option value="SEMUA" @selected(old('tipe_target', 'SEMUA') === 'SEMUA')>Semua User</option>
                            <option value="USER" @selected(old('tipe_target') === 'USER')>User Tertentu</option>
                            <option value="BAGIAN" @selected(old('tipe_target') === 'BAGIAN')>User per Bagian</option>
                        </select>
                        @error('tipe_target')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div id="wrap-user" class="hidden">
                        <label for="user_ids" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pilih User <span class="text-red-500">*</span></label>
                        <select id="user_ids" name="user_ids[]" multiple size="8"
                                class="select2 w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 @error('user_ids') border-red-400 @enderror">
                            @foreach ($users as $u)
                                <option value="{{ $u->user_id }}" @selected(in_array($u->user_id, old('user_ids', []) ?: []))>{{ $u->nama_pegawai }} ({{ $u->user_name }})</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-400">Tekan Ctrl/Cmd untuk memilih lebih dari satu user.</p>
                        @error('user_ids')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        @error('user_ids.*')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div id="wrap-bagian" class="hidden">
                        <label for="bagian_id" class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wider">Pilih Bagian <span class="text-red-500">*</span></label>
                        <select id="bagian_id" name="bagian_id" class="w-full text-sm border border-slate-200 rounded-lg px-3.5 py-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-slate-700 @error('bagian_id') border-red-400 @enderror">
                            <option value="">-- Pilih Bagian --</option>
                            @foreach ($bagians as $b)
                                <option value="{{ $b->bagian_id }}" @selected((string) old('bagian_id') === (string) $b->bagian_id)>{{ $b->nama_bagian }}</option>
                            @endforeach
                        </select>
                        @error('bagian_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5">
                <a href="{{ route('admin.notifikasi.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm shadow-blue-600/20 transition-all hover:-translate-y-0.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Kirim Notifikasi
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    const tipe = document.getElementById('tipe_target');
    const wrapUser = document.getElementById('wrap-user');
    const wrapBagian = document.getElementById('wrap-bagian');
    const userIds = document.getElementById('user_ids');
    const bagianId = document.getElementById('bagian_id');
    if (!tipe) { return; }

    function sync() {
        const isUser = tipe.value === 'USER';
        const isBagian = tipe.value === 'BAGIAN';
        wrapUser.classList.toggle('hidden', !isUser);
        wrapBagian.classList.toggle('hidden', !isBagian);
        // Field tersembunyi tidak boleh ikut tervalidasi / terkirim.
        userIds.disabled = !isUser;
        bagianId.disabled = !isBagian;
    }

    tipe.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
@endsection
