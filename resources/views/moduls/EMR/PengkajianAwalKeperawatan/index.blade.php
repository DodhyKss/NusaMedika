@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleRiwayat = 'Riwayat';
        $titleForm = 'Form Pengkajian Awal Keperawatan';
        $subtitleForm = 'Silahkan isi Pengkajian Awal Keperawatan';
        $routeName = null;
        $routeUrl = url('emr/form/pengkajian_awal_keperawatan');
        $registrasiDetailId  = $registrasi_detail->registrasi_detail_id;
    @endphp

    <x-emr-split-layout 
        :titleRiwayat="$titleRiwayat" 
        :titleForm="$titleForm" 
        :subtitleForm="$subtitleForm"
        :historyGrouped="$historyGrouped" 
        :routeName="$routeName" 
        :routeUrl="$routeUrl" 
        :registrasiDetailId="$registrasiDetailId"
        :formAction="$formAction" 
        :isEdit="$isEdit" 
        :deleteAction="$deleteAction" 
        :emrId="$emr_id"
        :isView="$isView" 
        :printUrl="$printUrl"
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']">

        <x-slot name="listRiwayat">
            @forelse($riwayatPengkajianAwal as $item)
                @php
                    $isActive = isset($emr_id) && $emr_id == $item->emr_id;
                @endphp
                <div class="riwayat-item group border {{ $isActive ? 'border-blue-400 bg-blue-50 shadow-md' : 'border-slate-200 bg-white hover:border-blue-300 hover:shadow-md' }} rounded-xl p-4 transition-all relative mb-3">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="text-xs font-bold text-blue-600">
                                {{ \Carbon\Carbon::parse($item->tgl_jam)->format('d/m/Y H:i') }}</p>
                            <p class="text-sm font-semibold text-slate-800 mt-0.5">Oleh: {{ $item->nama_pegawai ?? 'Perawat' }}</p>
                        </div>
                        @if($isActive)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                Aktif
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 pt-3 border-t border-slate-100 mt-3">
                        @if($aksesCrud['read'])
                        <a href="{{ route('emr.dynamic.index', ['form_name' => 'pengkajian_awal_keperawatan', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $item->emr_id, 'action' => 'view']) }}" class="flex-1 inline-flex justify-center items-center gap-1 px-3 py-1.5 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 hover:text-slate-800 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Lihat
                        </a>
                        @endif
                        @if($aksesCrud['update'])
                        <a href="{{ route('emr.dynamic.index', ['form_name' => 'pengkajian_awal_keperawatan', 'registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $item->emr_id]) }}" class="flex-1 inline-flex justify-center items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 hover:text-blue-800 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit
                        </a>
                        @endif
                        @if($aksesCrud['delete'])
                        <form action="{{ route('emr.pengkajian_awal_keperawatan.destroy', ['registrasi_detail_id' => $registrasi_detail->registrasi_detail_id, 'emr_id' => $item->emr_id]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat ini?');" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full inline-flex justify-center items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 hover:text-red-800 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Hapus
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-sm text-slate-500 py-4">Belum ada riwayat</div>
            @endforelse
        </x-slot>

        <!-- Form Partials (A-F) -->
        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            @include('moduls.EMR.PartialForm.informasi_pasien')
            @include('moduls.EMR.PartialForm.riwayat_penyakit')
            @include('moduls.EMR.PartialForm.pemeriksaan_fisik')
            @include('moduls.EMR.PartialForm.pengkajian_nyeri')
            @include('moduls.EMR.PartialForm.riwayat_alergi')
            @include('moduls.EMR.PartialForm.pengkajian_up_go')
        </fieldset>
        
    </x-emr-split-layout>

@endsection()