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
            <x-emr-history-table 
                slug="pengkajian_awal_keperawatan" 
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id" 
                :current-emr-id="$emr_id ?? null" 
                :headers="['kesadaran' => 'Kesadaran', 'td' => 'TD']" 
            />
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