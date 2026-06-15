@extends('layouts.app')

@section('content')
@php
    $userRole = strtolower(Auth::user()->role);
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
    
    /* Premium Gradient Stat Cards */
    .stat-card { border: none; border-radius: 1.25rem; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); overflow: hidden; height: 100%; display: flex; flex-direction: column; justify-content: center; position: relative; z-index: 1; color: white; }
    .stat-card::after { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%); transform: rotate(30deg); z-index: -1; pointer-events: none; }
    .stat-card:hover { transform: translateY(-8px) scale(1.02); }
    .stat-card:hover .stat-icon-box { transform: scale(1.1) rotate(10deg); background-color: rgba(255,255,255,0.3) !important; color: white !important; }
    
    .stat-card-1 { background: linear-gradient(135deg, #10b981 0%, #047857 100%); box-shadow: 0 15px 25px -5px rgba(16, 185, 129, 0.4); }
    .stat-card-2 { background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%); box-shadow: 0 15px 25px -5px rgba(245, 158, 11, 0.4); }
    .stat-card-3 { background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); box-shadow: 0 15px 25px -5px rgba(14, 165, 233, 0.4); }
    .stat-card-4 { background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%); box-shadow: 0 15px 25px -5px rgba(244, 63, 94, 0.4); }

    .stat-icon-box { width: 64px; height: 64px; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; flex-shrink: 0; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); background-color: rgba(255, 255, 255, 0.2) !important; color: white !important; backdrop-filter: blur(4px); box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
    .stat-card p, .stat-card h2, .stat-card i { color: white !important; }
    .chart-panel { background: white; border: none; border-radius: 1.25rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); padding: 1.5rem; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-slate-dark fw-bold">Dashboard Eksekutif</h1>
            <p class="text-slate-muted small mb-0 mt-1">Ringkasan performa bisnis dan pergerakan inventaris perusahaan.</p>
        </div>
        <div class="d-flex gap-2">
            {{-- Tombol Audit Trail sudah dipindah ke Sidebar --}}
            
            {{-- Tombol Laporan lama di dashboard dihapus karena sekarang sudah ada di Menu Navigasi (Sidebar) --}}
        </div>
    </div>

    <div class="row g-4 mb-4 align-items-stretch">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-1 p-4">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div>
                        <p class="small text-uppercase fw-bold mb-1 letter-spacing-wide opacity-75">Total Sales Order</p>
                        <h2 class="fw-bolder mb-0" style="font-size: 1.6rem;">{{ $totalSO }}</h2>
                        <p class="small fw-bold mt-2 mb-0 opacity-75"><i class="fas fa-chart-line me-1"></i> Transaksi Tercatat</p>
                    </div>
                    <div class="stat-icon-box">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-2 p-4">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div>
                        <p class="small text-uppercase fw-bold mb-1 letter-spacing-wide opacity-75">Menunggu Approval</p>
                        <h2 class="fw-bolder mb-0" style="font-size: 1.6rem;">{{ $menungguApproval }}</h2>
                        <p class="small fw-bold mt-2 mb-0 opacity-75"><i class="fas fa-clock me-1"></i> Butuh Tindakan</p>
                    </div>
                    <div class="stat-icon-box">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-3 p-4">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div>
                        <p class="small text-uppercase fw-bold mb-1 letter-spacing-wide opacity-75">Total Item Inventaris</p>
                        <h2 class="fw-bolder mb-0" style="font-size: 1.6rem;">{{ number_format($totalBarang, 0, ',', '.') }}</h2>
                        <p class="small fw-bold mt-2 mb-0 opacity-75"><i class="fas fa-boxes me-1"></i> Master Data</p>
                    </div>
                    <div class="stat-icon-box">
                        <i class="fas fa-boxes"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card stat-card-4 p-4">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div>
                        <p class="small text-uppercase fw-bold mb-1 letter-spacing-wide opacity-75">Peringatan Stok Kritis</p>
                        <h2 class="fw-bolder mb-0" style="font-size: 1.6rem;">{{ $stokKritis }}</h2>
                        <p class="small fw-bold mt-2 mb-0 opacity-75"><i class="fas fa-exclamation-triangle me-1"></i> Segera Restock</p>
                    </div>
                    <div class="stat-icon-box">
                        <i class="fas fa-box-open"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="chart-panel h-100">
                <h6 class="fw-bold text-slate-dark mb-4">
                    Tren Sales Order (6 Bulan Terakhir)
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
            series: [
                { name: 'Omzet Penjualan (Rp)', type: 'area', data: @json($omzetData) },
                { name: 'Total Pesanan (SO)', type: 'line', data: @json($salesData) }
            ],
            chart: { height: 320, type: 'line', fontFamily: 'inherit', toolbar: { show: false }, zoom: { enabled: false } },
            colors: ['#10b981', '#f59e0b'], 
            fill: { 
                type: ['gradient', 'solid'], 
                gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] } 
            },
            dataLabels: { enabled: false }, 
            stroke: { curve: 'smooth', width: [3, 4] },
            xaxis: { categories: @json($salesBulan), axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: [
                {
                    title: { text: "Omzet", style: { color: '#10b981', fontWeight: 'bold' } },
                    labels: {
                        formatter: function (val) {
                            if (val >= 1000000000) return "Rp" + (val / 1000000000).toFixed(1) + "M";
                            if (val >= 1000000) return "Rp" + (val / 1000000).toFixed(1) + "Jt";
                            return "Rp" + val.toLocaleString('id-ID');
                        },
                        style: { colors: '#10b981', fontWeight: '600' }
                    }
                },
                {
                    opposite: true,
                    title: { text: "Total SO", style: { color: '#f59e0b', fontWeight: 'bold' } },
                    labels: {
                        formatter: function (val) { return Math.round(val); },
                        style: { colors: '#f59e0b', fontWeight: '600' }
                    }
                }
            ],
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (y, { seriesIndex }) {
                        if (typeof y !== "undefined") {
                            if (seriesIndex === 0) {
                                return "Rp " + y.toLocaleString('id-ID');
                            }
                            return Math.round(y) + " Pesanan";
                        }
                        return y;
                    }
                }
            },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 }
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