@extends('layouts.app')

@section('content')
<style>
    /* Global Overrides untuk Tema Keuangan Mentari Atlas */
    body { background-color: #f8fafc !important; }
    
    /* Custom Soft Border Colors */
    .border-left-emerald { border-left: 4px solid #10b981 !important; }
    .border-left-indigo { border-left: 4px solid #6366f1 !important; }
    .border-left-rose { border-left: 4px solid #f43f5e !important; }
    .border-left-cyan { border-left: 4px solid #06b6d4 !important; }
    
    /* Text Color Utilities */
    .text-emerald-custom { color: #10b981 !important; }
    .text-slate-dark { color: #0f172a !important; }
    .text-slate-muted { color: #64748b !important; }
    
    /* Component Styles */
    .card-custom {
        background-color: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 0.5rem !important;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out !important;
    }
    .card-custom:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -4px rgba(0, 0, 0, 0.05) !important;
    }
    
    .table-custom-header th {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        font-weight: 600 !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }
    
    /* Status Badges */
    .badge-success-soft { background-color: #d1fae5 !important; color: #065f46 !important; }
    .badge-warning-soft { background-color: #fef3c7 !important; color: #92400e !important; }
    .badge-danger-soft { background-color: #fee2e2 !important; color: #991b1b !important; }
    .badge-secondary-soft { background-color: #f1f5f9 !important; color: #334155 !important; border: 1px solid #cbd5e1 !important; }
</style>

<div class="container-fluid py-4" style="background-color: #f8fafc;">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-slate-dark fw-bold">Ruang Kontrol Keuangan</h1>
            <p class="text-slate-muted small mb-0 mt-1">Pantau arus kas, tagihan piutang, dan kewajiban utang perusahaan.</p>
        </div>
        <span class="text-slate-muted fw-medium bg-white px-3 py-1.5 rounded border small shadow-sm d-none d-sm-inline-block">
            <i class="far fa-clock text-emerald-custom me-2"></i>{{ \Carbon\Carbon::now()->format('d F Y, H:i') }}
        </span>
    </div>

    <div class="row mb-4">
        {{-- KARTU 1: TOTAL OMZET VALID --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-custom border-left-emerald shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-emerald-custom text-uppercase mb-1 tracking-wider">Total Omzet Valid</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">Rp {{ number_format($totalOmzet ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-emerald-custom opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: PIUTANG BERJALAN --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-custom border-left-cyan shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1 tracking-wider">Piutang Berjalan</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">Rp {{ number_format($piutangBerjalan ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-info opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 3: KEWAJIBAN UTANG --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-custom border-left-rose shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1 tracking-wider">Kewajiban Utang</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">Rp {{ number_format($kewajibanUtang ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-danger opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 4: SO DISETUJUI --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-custom border-left-indigo shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1 tracking-wider" style="color: #6366f1 !important;">SO Disetujui (Aktif)</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">{{ $soDisetujui ?? 0 }} <span class="small text-muted" style="font-size: 0.8rem;">Pesanan</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-basket fa-2x opacity-40" style="color: #6366f1;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden h-100">
                <div class="card-header py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-slate-dark d-flex align-items-center">
                        <i class="fas fa-exchange-alt text-emerald-custom me-2"></i> Log Transaksi Terakhir Masuk
                    </h6>
                </div>
                <div class="card-body p-0 bg-white flex-grow-1">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-custom-header">
                                <tr>
                                    <th class="px-4 py-3">Waktu Input</th>
                                    <th class="py-3">No Referensi</th>
                                    <th class="py-3">Entitas</th>
                                    <th class="py-3">Nilai Rupiah</th>
                                    <th class="text-center py-3">Status Filter</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayat ?? [] as $trx)
                                <tr>
                                    <td class="px-4 text-slate-muted small fw-medium">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="fw-bold text-slate-dark">{{ $trx->no_invoice ?? ('#INV-'.$trx->id) }}</td>
                                    <td>
                                        {{-- FIX AMAN: Menggunakan null-safe operator (?->) agar tidak error jika relasi kosong --}}
                                        <div class="fw-medium text-slate-800">{{ $trx->penjualan?->customer?->nama_customer ?? 'Umum' }}</div>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">Sales: {{ $trx->penjualan?->user?->name ?? '-' }}</small>
                                    </td>
                                    <td class="fw-bold text-emerald-custom">Rp {{ number_format($trx->total_tagihan ?? 0, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        <span class="badge px-3 py-1.5 rounded-pill fw-bold text-uppercase tracking-wider {{ strtolower($trx->status_bayar ?? '') == 'lunas' ? 'badge-success-soft' : 'badge-warning-soft' }}" style="font-size: 0.65rem;">
                                            {{ $trx->status_bayar ?? 'BELUM BAYAR' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-slate-muted bg-white">Belum ada transaksi piutang/sales order yang tercatat.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden h-100 bg-white">
                <div class="card-header py-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-slate-dark d-flex align-items-center">
                        <i class="fas fa-bolt text-warning me-2"></i> Tindakan Finansial Cepat
                    </h6>
                </div>
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <div class="p-3 rounded-3 border" style="background-color: #f8fafc;">
                        <h6 class="fw-bold text-slate-dark mb-1"><i class="fas fa-file-invoice-dollar text-info me-2"></i>Kelola Piutang Masuk</h6>
                        <p class="small text-slate-muted mb-3">Catat pembayaran dari customer untuk SO yang sudah selesai.</p>
                        <a href="{{ route('keuangan.piutang.index') }}" class="btn btn-sm btn-outline-info w-100 fw-medium">Buka Modul Piutang</a>
                    </div>

                    <div class="p-3 rounded-3 border" style="background-color: #fff1f2; border-color: #fecdd3 !important;">
                        <h6 class="fw-bold text-danger mb-1"><i class="fas fa-money-bill-wave me-2"></i>Bayar Utang Supplier</h6>
                        <p class="small text-slate-muted mb-3">Selesaikan kewajiban tagihan pengadaan stok barang gudang.</p>
                        <a href="{{ route('keuangan.utang.index') }}" class="btn btn-sm btn-outline-danger w-100 fw-medium">Buka Modul Utang</a>
                    </div>

                    <div class="mt-auto pt-3 border-top text-center">
                        <small class="text-slate-muted"><i class="fas fa-shield-alt text-emerald-custom me-1"></i> Data dienkripsi & dipantau secara real-time.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection