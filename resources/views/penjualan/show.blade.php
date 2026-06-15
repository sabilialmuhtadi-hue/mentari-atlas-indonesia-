@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f8fafc !important; }
    .text-slate-dark { color: #0f172a !important; }
    .text-slate-muted { color: #64748b !important; }
    .text-emerald { color: #10b981 !important; }
    .bg-emerald { background-color: #10b981 !important; }
    .bg-gradient-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .bg-gradient-slate { background: linear-gradient(135deg, #334155 0%, #0f172a 100%); }
    .border-left-emerald { border-left: 4px solid #10b981 !important; }
    .border-left-info { border-left: 4px solid #0ea5e9 !important; }
    .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .card-custom:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
    .badge-gacor { padding: 0.5em 0.8em; font-weight: 600; letter-spacing: 0.5px; border-radius: 50rem; }
    .table-gacor th { background-color: #f1f5f9; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0; }
    .table-gacor td { vertical-align: middle; color: #334155; }
    .table-gacor tbody tr:hover { background-color: #f8fafc; }
    .letter-spacing-wide { letter-spacing: 0.5px; }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-slate-dark fw-bolder" style="letter-spacing: -0.5px;">
                <i class="fas fa-file-invoice-dollar text-emerald me-2"></i> Detail Sales Order
            </h1>
            <p class="text-slate-muted small mb-0 mt-1">Nomor Referensi: <span class="fw-bold text-slate-dark">{{ $penjualan->no_so }}</span></p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="{{ route('penjualan.index') }}" class="btn btn-white border shadow-sm rounded-pill fw-medium px-4">
                <i class="fas fa-arrow-left me-2 text-muted"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri -->
        <div class="col-lg-4">
            <!-- Informasi Order Card -->
            <div class="card card-custom border-left-emerald h-100 mb-4" style="min-height: 250px;">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="mb-0 fw-bold text-slate-dark text-uppercase letter-spacing-wide" style="font-size: 0.85rem;">
                        <i class="fas fa-info-circle text-emerald me-2"></i> Informasi Order
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush mt-2">
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center bg-transparent">
                            <span class="text-slate-muted small"><i class="fas fa-user-tie me-2"></i>Customer</span>
                            <span class="fw-bold text-slate-dark">{{ $penjualan->customer->nama_customer }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center bg-transparent">
                            <span class="text-slate-muted small"><i class="fas fa-calendar-alt me-2"></i>Tanggal</span>
                            <span class="fw-bold text-slate-dark">{{ \Carbon\Carbon::parse($penjualan->tanggal_order)->format('d M Y') }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center bg-transparent">
                            <span class="text-slate-muted small"><i class="fas fa-user-tag me-2"></i>Sales</span>
                            <span class="fw-bold text-slate-dark">{{ $penjualan->user->name }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center bg-transparent">
                            <span class="text-slate-muted small"><i class="fas fa-chart-pie me-2"></i>Probabilitas</span>
                            <span class="fw-bolder text-emerald">{{ $penjualan->peluang }}%</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center bg-transparent border-bottom-0 pb-0">
                            <span class="text-slate-muted small"><i class="fas fa-flag me-2"></i>Status</span>
                            <span class="badge badge-gacor {{ $penjualan->status_approval == 'disetujui' ? 'bg-success bg-opacity-10 text-success border border-success' : ($penjualan->status_approval == 'pending' ? 'bg-warning bg-opacity-10 text-warning border border-warning' : 'bg-danger bg-opacity-10 text-danger border border-danger') }}">
                                {{ ucfirst($penjualan->status_approval) }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Audit Trail Card -->
            <div class="card card-custom border-left-info mt-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="mb-0 fw-bold text-slate-dark text-uppercase letter-spacing-wide" style="font-size: 0.85rem;">
                        <i class="fas fa-history text-info me-2"></i> Audit Trail Transaksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="position-relative ms-3 mt-2 border-start border-2 pb-4" style="border-color: #cbd5e1 !important;">
                        <!-- Milestone 1 -->
                        <div class="position-relative mb-4 ms-4">
                            <span class="position-absolute top-0 start-0 translate-middle p-2 bg-white border border-info border-2 rounded-circle shadow-sm" style="left: -1.05rem !important; margin-top: 0.2rem;"></span>
                            <small class="text-info fw-bold d-block text-uppercase letter-spacing-wide" style="font-size: 0.7rem;">Dibuat Oleh Sales</small>
                            <span class="fw-bold text-slate-dark d-block mt-1">{{ $penjualan->sales_created_by ?? $penjualan->user->name }}</span>
                            <small class="text-slate-muted"><i class="far fa-clock me-1"></i> {{ $penjualan->sales_created_at ? $penjualan->sales_created_at->format('d M Y, H:i') : '-' }} WIB</small>
                        </div>
                        
                        <!-- Milestone 2 -->
                        <div class="position-relative ms-4">
                            @if($penjualan->status_approval === 'disetujui')
                                <span class="position-absolute top-0 start-0 translate-middle p-2 bg-success rounded-circle shadow-sm" style="left: -1.05rem !important; margin-top: 0.2rem;"></span>
                                <small class="text-success fw-bold d-block text-uppercase letter-spacing-wide" style="font-size: 0.7rem;">Telah Disetujui</small>
                                <span class="fw-bold text-slate-dark d-block mt-1">Oleh: {{ $penjualan->approver->name ?? 'Direktur' }}</span>
                                <small class="text-slate-muted"><i class="far fa-clock me-1"></i> {{ $penjualan->approved_at ? $penjualan->approved_at->format('d M Y, H:i') : '-' }} WIB</small>
                            @elseif($penjualan->status_approval === 'ditolak')
                                <span class="position-absolute top-0 start-0 translate-middle p-2 bg-danger rounded-circle shadow-sm" style="left: -1.05rem !important; margin-top: 0.2rem;"></span>
                                <small class="text-danger fw-bold d-block text-uppercase letter-spacing-wide" style="font-size: 0.7rem;">Ditolak</small>
                                <span class="fw-bold text-slate-dark d-block mt-1">Oleh: {{ $penjualan->approver->name ?? 'Direktur' }}</span>
                                <small class="text-slate-muted"><i class="far fa-clock me-1"></i> {{ $penjualan->approved_at ? $penjualan->approved_at->format('d M Y, H:i') : '-' }} WIB</small>
                            @else
                                <span class="position-absolute top-0 start-0 translate-middle p-2 bg-warning rounded-circle shadow-sm" style="left: -1.05rem !important; margin-top: 0.2rem;"></span>
                                <small class="text-warning text-dark fw-bold d-block text-uppercase letter-spacing-wide" style="font-size: 0.7rem;">Menunggu Review</small>
                                <span class="text-slate-muted d-block mt-1 fst-italic small">Belum ada tindakan dari Direktur.</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan (Daftar Barang) -->
        <div class="col-lg-8">
            <div class="card card-custom h-100 overflow-hidden">
                <div class="card-header bg-gradient-emerald text-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 fw-bolder letter-spacing-wide"><i class="fas fa-boxes me-2 text-white"></i> Rincian Pesanan Barang</h6>
                    <span class="badge bg-white text-emerald rounded-pill shadow-sm px-3">{{ count($penjualan->details) }} Item</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-gacor mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3">Nama Barang</th>
                                    <th class="text-center py-3">Qty</th>
                                    <th class="text-end py-3">Harga Satuan</th>
                                    <th class="text-end px-4 py-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($penjualan->details as $detail)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3">
                                        <div class="fw-bold text-slate-dark">{{ $detail->barang->nama_barang }}</div>
                                        <div class="text-muted small"><i class="fas fa-barcode me-1 opacity-50"></i>{{ $detail->barang->kode_barang ?? '-' }}</div>
                                    </td>
                                    <td class="text-center py-3">
                                        <span class="badge bg-light text-slate-dark border px-3 py-2 fs-6 rounded-pill shadow-sm">{{ $detail->jumlah }}</span>
                                    </td>
                                    <td class="text-end py-3">
                                        <div class="fw-bold text-slate-dark">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</div>
                                        @if($detail->hpp > 0 || isset($detail->barang->harga_beli))
                                        <div class="text-muted" style="font-size: 0.7rem;">
                                            HPP: <span class="text-danger fw-bold">Rp {{ number_format($detail->hpp > 0 ? $detail->hpp : ($detail->barang->harga_beli ?? 0), 0, ',', '.') }}</span>
                                        </div>
                                        @endif
                                    </td>
                                    <td class="text-end px-4 py-3 fw-bolder text-emerald fs-6">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light border-top-0 py-4 px-4" style="background-color: #f8fafc !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-slate-muted fw-bold text-uppercase letter-spacing-wide">Total Nilai Transaksi</span>
                        <h2 class="mb-0 fw-bolder text-slate-dark" style="letter-spacing: -1px;">
                            <span class="text-emerald fs-4 me-1">Rp</span>{{ number_format($penjualan->total_semua, 0, ',', '.') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection