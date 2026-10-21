@extends('layouts.iframe')

@section('content')
    @php
        $aksesCrud = $aksesCrud ?? ['create' => true, 'read' => true, 'update' => true, 'delete' => true];

        $titleForm = 'Form Pengkajian Harian Keperawatan';
        $subtitleForm = 'Diisi setiap shift: keluhan, tanda vital, nyeri, risiko jatuh, dan balance cairan.';
        $routeName = null;
        $routeUrl = url('emr/form/pengkajian_harian_keperawatan');
        $registrasiDetailId = $registrasi_detail->registrasi_detail_id;
    @endphp

    <x-emr-split-layout
        titleRiwayat="Riwayat Pengkajian Harian"
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
        :printUrl="''"
        :canCreate="$aksesCrud['create']"
        :canRead="$aksesCrud['read']"
        :canUpdate="$aksesCrud['update']"
        :canDelete="$aksesCrud['delete']">

        <x-slot name="listRiwayat">
            <x-emr-history-table
                slug="pengkajian_harian_keperawatan"
                :registrasi-detail-id="$registrasi_detail->registrasi_detail_id"
                :current-emr-id="$emr_id ?? null"
                :headers="['keluhan' => 'Keluhan', 'td' => 'TD', 'risiko_jatuh_ringkasan' => 'Risiko Jatuh']"
            />
        </x-slot>

        {{-- Form Partials (A-E). Fieldset disables everything saat mode lihat. --}}
        <fieldset class="space-y-3" {{ $isView ? 'disabled' : '' }}>
            @include('moduls.EMR.PartialForm.harian_keluhan')
            @include('moduls.EMR.PartialForm.pemeriksaan_fisik')
            @include('moduls.EMR.PartialForm.pengkajian_nyeri')
            @include('moduls.EMR.PartialForm.riwayat_alergi')
            @include('moduls.EMR.PartialForm.harian_balance')
            @include('moduls.EMR.PartialForm.pengkajian_risiko_jatuh')
        </fieldset>

    </x-emr-split-layout>

@endsection()