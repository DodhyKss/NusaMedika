@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Selamat Datang, {{ Auth::user()->nama_pegawai ?? Auth::user()->user_name }}</h1>
    <p class="text-sm text-slate-500 mt-1">Daftar pasien aktif per layanan. Klik tombol pada masing-masing jenis rawat untuk menampilkan daftar pasien.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    @php
        $cards = [
            [
                'key' => 'ranap',
                'title' => 'Rawat Inap',
                'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'color' => 'bg-blue-50 text-blue-600',
                'btnColor' => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
            ],
            [
                'key' => 'rajal',
                'title' => 'Rawat Jalan',
                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'color' => 'bg-emerald-50 text-emerald-600',
                'btnColor' => 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500',
            ],
            [
                'key' => 'igd',
                'title' => 'IGD',
                'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                'color' => 'bg-rose-50 text-rose-600',
                'btnColor' => 'bg-rose-600 hover:bg-rose-700 focus:ring-rose-500',
            ],
        ];
    @endphp

    @foreach ($cards as $card)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col h-[500px]">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
                <div class="p-2 rounded-lg {{ $card['color'] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"></path></svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-slate-800">{{ $card['title'] }}</h2>
                    <p class="text-[11px] text-slate-400">Layanan Rawat</p>
                </div>
            </div>
            
            <div class="flex-1 flex flex-col justify-between overflow-hidden">
                <!-- Patient List Container -->
                <div id="list-{{ $card['key'] }}" class="divide-y divide-slate-100 overflow-y-auto flex-1 hidden">
                    <!-- Loaded via AJAX -->
                </div>

                <!-- Initial State with Button -->
                <div id="init-{{ $card['key'] }}" class="flex-1 flex flex-col items-center justify-center p-6 text-center">
                    <p class="text-sm text-slate-500 mb-4">Daftar pasien belum dimuat.</p>
                    <button type="button" 
                            onclick="loadPasien('{{ $card['key'] }}')"
                            id="btn-{{ $card['key'] }}"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-white rounded-lg shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $card['btnColor'] }}">
                        <svg class="w-4 h-4 spinner-icon hidden animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Tampilkan Pasien
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
    function loadPasien(key) {
        const $btn = $('#btn-' + key);
        const $spinner = $btn.find('.spinner-icon');
        const $init = $('#init-' + key);
        const $list = $('#list-' + key);

        // Show spinner, disable button
        $btn.prop('disabled', true);
        $spinner.removeClass('hidden');

        $.ajax({
            url: "{{ url('/dashboard/pasien') }}/" + key,
            method: "GET",
            success: function(html) {
                $list.html(html).removeClass('hidden');
                $init.addClass('hidden');
            },
            error: function() {
                alert('Gagal memuat data pasien ' + key + '. Silakan coba lagi.');
                $btn.prop('disabled', false);
                $spinner.addClass('hidden');
            }
        });
    }
</script>
@endpush
@endsection