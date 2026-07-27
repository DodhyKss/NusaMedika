@extends('layouts.iframe')

@section('content')
    @php
        $titleRiwayat = 'Riwayat';
        $titleForm = 'Form Pengkajian Awal Keperawatan';
        $subtitleForm = 'Silahkan isi Pengkajian Awal Keperawatan';
        $historyGrouped = [];
        $routeName = '';
        $routeUrl = url('emr/form/pengkajian_awal_keperawatan');
        $registrasiDetailId  = $registrasi_detail->registrasi_detail_id;
        $formAction = '';
        $isEdit = false;
        $deleteAction = '';
        $emrId = null;
        $printUrl = '';
        $isView = false;
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
        :emrId="$emrId"
        :isView="$isView" 
        :printUrl="$printUrl">

        <x-slot name="listRiwayat">
            <div class="text-center text-sm text-slate-500 py-4">Belum ada riwayat</div>
        </x-slot>

        <!-- Form Partials (A-F) -->
        <div class="space-y-3">
            @include('moduls.emr.partial_form.informasi_pasien')
            @include('moduls.emr.partial_form.riwayat_penyakit')
            @include('moduls.emr.partial_form.pemeriksaan_fisik')
            @include('moduls.emr.partial_form.pengkajian_nyeri')
            @include('moduls.emr.partial_form.riwayat_alergi')
            @include('moduls.emr.partial_form.pengkajian_up_go')
        </div>
        
    </x-emr-split-layout>

@endsection()