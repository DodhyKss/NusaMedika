@extends('layouts.app')

@section('content')
<!-- Page Header -->
<div class="mb-6">
    <h1 class="text-[22px] font-bold text-slate-900 tracking-tight">Selamat Datang, {{ Auth::user()->nama_pegawai ?? Auth::user()->user_name }}</h1>
    <p class="text-sm text-slate-500 mt-1">Pantau jumlah pasien rawat inap, rawat jalan, dan IGD serta statistik kunjungan dari database.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <!-- Rawat Inap (aktif) -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rawat Inap</p>
            <div class="p-2 bg-indigo-50 rounded-lg">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($jumlahRi) }}</p>
        <p class="text-xs text-slate-400 mt-1">Pasien masih dirawat (belum pulang)</p>
    </div>

    <!-- Rawat Jalan hari ini -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rawat Jalan</p>
            <div class="p-2 bg-blue-50 rounded-lg">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($jumlahRj) }}</p>
        <p class="text-xs text-slate-400 mt-1">Kunjungan rawat jalan hari ini</p>
    </div>

    <!-- IGD hari ini -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">IGD</p>
            <div class="p-2 bg-amber-50 rounded-lg">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ number_format($jumlahIgd) }}</p>
        <p class="text-xs text-slate-400 mt-1">Kunjungan IGD hari ini</p>
    </div>
</div>

<!-- System Status Bar -->
<div class="flex items-center gap-2 px-4 py-2.5 mb-6 bg-emerald-50 border border-emerald-200 rounded-lg">
    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
    <span class="text-xs font-semibold text-emerald-700">Status Sistem: Operational</span>
    <span class="text-xs text-emerald-500 ml-auto">Total kunjungan hari ini: {{ number_format($totalHariIni) }} &middot; Diperbarui {{ now()->format('H:i') }}</span>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
    <!-- Tren 7 hari -->
    <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <h2 class="text-base font-semibold text-slate-800 mb-1">Tren Kunjungan 7 Hari Terakhir</h2>
        <p class="text-xs text-slate-400 mb-4">Jumlah registrasi per hari menurut jenis rawat</p>
        <div class="relative h-[280px]">
            <canvas id="chartTren"></canvas>
        </div>
    </div>

    <!-- Komposisi hari ini -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
        <h2 class="text-base font-semibold text-slate-800 mb-1">Komposisi Hari Ini</h2>
        <p class="text-xs text-slate-400 mb-4">Distribusi jenis rawat hari ini</p>
        <div class="relative h-[280px] flex items-center justify-center">
            <canvas id="chartKomposisi"></canvas>
        </div>
    </div>
</div>

<!-- Top bagian -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="text-base font-semibold text-slate-800">Top 5 Bagian Tujuan Hari Ini</h2>
        <p class="text-xs text-slate-400 mt-0.5">Berdasarkan jumlah pasien terdaftar pada kunjungan hari ini</p>
    </div>
    @forelse($topBagian as $item)
        @php
            $persen = $totalHariIni > 0 ? max(4, round($item->jumlah / max(1, $topBagian->first()->jumlah) * 100)) : 4;
        @endphp
        <div class="px-5 py-3.5 border-b border-slate-100 last:border-0 flex items-center gap-4">
            <span class="text-sm font-semibold text-slate-400 w-6 text-right">{{ $loop->iteration }}</span>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1.5">
                    <p class="text-sm font-medium text-slate-800 truncate">{{ $item->nama_bagian }}</p>
                    <span class="text-sm font-bold text-slate-900 ml-3">{{ number_format($item->jumlah) }}</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 rounded-full" style="width: {{ $persen }}%"></div>
                </div>
            </div>
        </div>
    @empty
        <div class="px-5 py-8 text-center text-sm text-slate-400">Belum ada kunjungan tercatat hari ini.</div>
    @endforelse
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.9/dist/chart.umd.min.js"></script>
@push('scripts')
<script>
    (function () {
        if (typeof Chart === 'undefined') {
            return;
        }

        const labels = @json($labelHari);

        new Chart(document.getElementById('chartTren'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Rawat Jalan',
                        data: @json($dataRajal),
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'Rawat Inap',
                        data: @json($dataRanap),
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                    {
                        label: 'IGD',
                        data: @json($dataIgd),
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, stepSize: 1 },
                        grid: { color: 'rgba(148, 163, 184, 0.15)' },
                    },
                    x: { grid: { display: false } },
                },
            },
        });

        const komposisi = {
            labels: ['Rawat Jalan', 'Rawat Inap', 'IGD'],
            datasets: [{
                data: [{{ (int) $jumlahRj }}, {{ (int) $jumlahRi }}, {{ (int) $jumlahIgd }}],
                backgroundColor: ['#2563eb', '#4f46e5', '#d97706'],
                borderWidth: 0,
                hoverOffset: 6,
            }],
        };

        const totalKomposisi = komposisi.datasets[0].data.reduce((a, b) => a + b, 0);

        new Chart(document.getElementById('chartKomposisi'), {
            type: 'doughnut',
            data: komposisi,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0) || 1;
                                const pct = ((ctx.parsed / total) * 100).toFixed(1);
                                return ' ' + ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            },
                        },
                    },
                },
            },
            plugins: [{
                id: 'centerText',
                afterDraw: function (chart) {
                    const { ctx, width, height } = chart;
                    const total = chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                    ctx.save();
                    ctx.font = 'bold 22px ui-sans-serif, system-ui, sans-serif';
                    ctx.fillStyle = '#0f172a';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(String(total), width / 2, height / 2 - 10);
                    ctx.font = '11px ui-sans-serif, system-ui, sans-serif';
                    ctx.fillStyle = '#94a3b8';
                    ctx.fillText('Total', width / 2, height / 2 + 12);
                    ctx.restore();
                },
            }],
        });
    })();
</script>
@endpush
@endsection
