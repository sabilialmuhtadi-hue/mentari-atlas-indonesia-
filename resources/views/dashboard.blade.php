@extends('layouts.app')

@section('content')
@php
    $userRole = strtolower(Auth::user()->role);
    $isSales = in_array($userRole, ['sales', 'marketing']);
    $isDirektur = ($userRole == 'direktur' || $userRole == 'superadmin');
    $hakAkses = Auth::user()->hak_akses ?? [];
@endphp

<style>
    /* Mengikuti Tema Emerald Premium Mentari Atlas */
    body { background-color: #f8fafc !important; }
    .text-emerald-custom { color: #10b981 !important; }
    .bg-emerald-custom { background-color: #10b981 !important; color: white; }
    .text-slate-dark { color: #0f172a !important; }
    .text-slate-muted { color: #64748b !important; }
    
    .stat-card { border: none; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); transition: transform 0.3s ease, box-shadow 0.3s ease; background: white; overflow: hidden; height: 100%; display: flex; flex-direction: column; justify-content: center; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
    .stat-icon-box { width: 56px; height: 56px; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
    .chart-panel { background: white; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); padding: 1.5rem; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            @if($isSales)
                <h1 class="h3 mb-0 text-slate-dark fw-bold">Area Kerja Sales</h1>
                <p class="text-slate-muted small mb-0 mt-1">Pantau performa target penjualan dan status order Anda hari ini.</p>
            @else
                <h1 class="h3 mb-0 text-slate-dark fw-bold">Dashboard Eksekutif</h1>
                <p class="text-slate-muted small mb-0 mt-1">Ringkasan performa bisnis dan pergerakan inventaris perusahaan.</p>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if(!$isSales)
            <a href="{{ url('/activity-logs') }}" class="btn rounded-pill px-4 shadow-sm border text-decoration-none d-inline-flex align-items-center" style="background-color: white; color: #475569; font-weight: 600;">
                <i class="fas fa-history text-primary me-2"></i> Audit Trail
            </a>
            @endif
            
            {{-- Tombol Laporan lama di dashboard dihapus karena sekarang sudah ada di Menu Navigasi (Sidebar) --}}
        </div>
    </div>

    <div class="row g-4 mb-4 align-items-stretch">
        @if($isSales)
            <div class="col-xl-3 col-md-6"><div class="stat-card p-4"><div class="d-flex justify-content-between align-items-center w-100"><div><p class="text-slate-muted small text-uppercase fw-bold mb-1">Total Order Saya</p><h3 class="fw-bold text-slate-dark mb-0">{{ $totalSO }}</h3><p class="text-primary small fw-bold mt-2 mb-0"><i class="fas fa-shopping-cart me-1"></i> Transaksi SO</p></div><div class="stat-icon-box text-primary" style="background-color: #e0f2fe;"><i class="fas fa-file-invoice"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="stat-card p-4"><div class="d-flex justify-content-between align-items-center w-100"><div><p class="text-slate-muted small text-uppercase fw-bold mb-1">Sedang Diproses</p><h3 class="fw-bold text-slate-dark mb-0">{{ $menungguApproval }}</h3><p class="text-warning small fw-bold mt-2 mb-0"><i class="fas fa-clock me-1"></i> Menunggu Direktur</p></div><div class="stat-icon-box text-warning" style="background-color: #fef3c7;"><i class="fas fa-hourglass-half"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="stat-card p-4"><div class="d-flex justify-content-between align-items-center w-100"><div><p class="text-slate-muted small text-uppercase fw-bold mb-1">Order Disetujui</p><h3 class="fw-bold text-slate-dark mb-0">{{ $statusData[0] }}</h3><p class="text-success small fw-bold mt-2 mb-0"><i class="fas fa-check-circle me-1"></i> Berhasil Goal</p></div><div class="stat-icon-box text-success" style="background-color: #d1fae5;"><i class="fas fa-thumbs-up"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="stat-card p-4"><div class="d-flex justify-content-between align-items-center w-100"><div><p class="text-slate-muted small text-uppercase fw-bold mb-1">Order Ditolak</p><h3 class="fw-bold text-danger mb-0">{{ $statusData[2] ?? 0 }}</h3><p class="text-danger small fw-bold mt-2 mb-0"><i class="fas fa-times-circle me-1"></i> Evaluasi Ulang</p></div><div class="stat-icon-box text-danger" style="background-color: #fee2e2;"><i class="fas fa-ban"></i></div></div></div></div>
        @else
            <div class="col-xl-3 col-md-6"><div class="stat-card p-4"><div class="d-flex justify-content-between align-items-center w-100"><div><p class="text-slate-muted small text-uppercase fw-bold mb-1">Total Sales Order</p><h3 class="fw-bold text-slate-dark mb-0">{{ $totalSO }}</h3><p class="text-success small fw-bold mt-2 mb-0"><i class="fas fa-chart-line me-1"></i> Transaksi Tercatat</p></div><div class="stat-icon-box text-emerald-custom" style="background-color: #d1fae5;"><i class="fas fa-file-invoice-dollar"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="stat-card p-4"><div class="d-flex justify-content-between align-items-center w-100"><div><p class="text-slate-muted small text-uppercase fw-bold mb-1">Menunggu Approval</p><h3 class="fw-bold text-slate-dark mb-0">{{ $menungguApproval }}</h3><p class="text-warning small fw-bold mt-2 mb-0"><i class="fas fa-clock me-1"></i> Butuh Tindakan</p></div><div class="stat-icon-box text-warning" style="background-color: #fef3c7;"><i class="fas fa-user-check"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="stat-card p-4"><div class="d-flex justify-content-between align-items-center w-100"><div><p class="text-slate-muted small text-uppercase fw-bold mb-1">Total Item Inventaris</p><h3 class="fw-bold text-slate-dark mb-0">{{ number_format($totalBarang, 0, ',', '.') }}</h3><p class="text-emerald-custom small fw-bold mt-2 mb-0"><i class="fas fa-boxes me-1"></i> Master Data</p></div><div class="stat-icon-box text-primary" style="background-color: #e0f2fe;"><i class="fas fa-boxes"></i></div></div></div></div>
            <div class="col-xl-3 col-md-6"><div class="stat-card p-4"><div class="d-flex justify-content-between align-items-center w-100"><div><p class="text-slate-muted small text-uppercase fw-bold mb-1">Peringatan Stok Kritis</p><h3 class="fw-bold text-danger mb-0">{{ $stokKritis }}</h3><p class="text-danger small fw-bold mt-2 mb-0"><i class="fas fa-exclamation-triangle me-1"></i> Segera Restock</p></div><div class="stat-icon-box text-danger" style="background-color: #fee2e2;"><i class="fas fa-box-open"></i></div></div></div></div>
        @endif
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-panel h-100">
                <h6 class="fw-bold text-slate-dark mb-4">
                    @if($isSales) Tren Penjualan Saya (6 Bulan Terakhir) @else Tren Sales Order (6 Bulan Terakhir) @endif
                </h6>
                <div id="salesChart" style="min-height: 300px;"></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-panel h-100">
                <h6 class="fw-bold text-slate-dark mb-4">Distribusi Status Order</h6>
                <div id="statusChart" style="min-height: 300px;" class="d-flex justify-content-center align-items-center"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var salesOptions = {
            series: [{ name: 'Total Pesanan (SO)', data: @json($salesData) }],
            chart: { height: 320, type: 'area', fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false } },
            colors: ['#10b981'], 
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } },
            dataLabels: { enabled: false }, stroke: { curve: 'smooth', width: 3 },
            xaxis: { categories: @json($salesBulan), axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { offsetX: -10 } }, grid: { borderColor: '#e2e8f0', strokeDashArray: 4, yaxis: { lines: { show: true } } }
        };
        var salesChart = new ApexCharts(document.querySelector("#salesChart"), salesOptions);
        salesChart.render();

        var statusOptions = {
            series: @json($statusData),
            labels: ['Selesai', 'Menunggu Approval', 'Ditolak'],
            chart: { type: 'donut', height: 320, fontFamily: 'inherit' },
            colors: ['#10b981', '#f59e0b', '#ef4444'],
            plotOptions: { pie: { donut: { size: '70%', labels: { show: true, name: { show: true }, value: { show: true, fontSize: '24px', fontWeight: 'bold', color: '#0f172a' }, total: { show: true, showAlways: true, label: 'Total SO', fontSize: '14px', color: '#64748b' } } } } },
            dataLabels: { enabled: false }, stroke: { width: 0 }, legend: { position: 'bottom', markers: { radius: 12 } }
        };
        var statusChart = new ApexCharts(document.querySelector("#statusChart"), statusOptions);
        statusChart.render();
    });
</script>
@endsection