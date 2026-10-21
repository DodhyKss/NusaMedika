@extends('layouts.iframe')

@section('content')
<div class="h-screen w-full bg-slate-50 flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-amber-100 mb-6">
            <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-2">Form Belum Tersedia</h2>
        <p class="text-slate-500 mb-6 leading-relaxed">
            Maaf, form rekam medis yang Anda pilih saat ini belum didukung atau masih dalam tahap pengembangan.
        </p>
        <button onclick="window.parent.document.getElementById('emr_placeholder').style.display='flex'; window.location.href='about:blank';" class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
            Tutup
        </button>
    </div>
</div>
@endsection
