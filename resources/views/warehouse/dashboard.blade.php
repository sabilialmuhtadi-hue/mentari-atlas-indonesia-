@extends('layouts.app')

@section('content')
<style>
    /* Global Overrides untuk Tema Keuangan Mentari Atlas */
    body { background-color: #f8fafc !important; }
    
    /* Custom Soft Border Colors (Persis Keuangan) */
    .border-left-emerald { border-left: 4px solid #10b981 !important; }
    .border-left-cyan { border-left: 4px solid #06b6d4 !important; }
    .border-left-rose { border-left: 4px solid #f43f5e !important; }
    .border-left-warning-custom { border-left: 4px solid #f59e0b !important; } /* Warna kuning elegan */
    
    /* Text Color Utilities */
    .text-emerald-custom { color: #10b981 !important; }
    .text-cyan-custom { color: #06b6d4 !important; }
    .text-rose-custom { color: #f43f5e !important; }
    .text-warning-custom { color: #f59e0b !important; }
    
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
    .badge-warning-soft { background-color: #fef3c7 !important; color: #92400e !important; }
</style>

<div class="container-fluid py-4" style="background-color: #f8fafc;">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-slate-dark fw-bold">Ruang Kontrol Warehouse</h1>
            <p class="text-slate-muted small mb-0 mt-1">Pantau antrean packing, status backorder, dan pergerakan stok inventaris.</p>
        </div>
        <span class="text-slate-muted fw-medium bg-white px-3 py-1.5 rounded border small shadow-sm d-none d-sm-inline-block">
            <i class="far fa-clock text-emerald-custom me-2"></i>{{ \Carbon\Carbon::now()->format('d F Y, H:i') }}
        </span>
    </div>

    <div class="row mb-4">
        {{-- KARTU 1: ANTREAN PACKING --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-custom border-left-warning-custom shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning-custom text-uppercase mb-1 tracking-wider">Antrean Packing</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">{{ $pesananMenungguPacking ?? 0 }} <span class="small text-muted" style="font-size: 0.8rem;">SO</span></div>
                        </div>
                        <div class="col-auto">
                            {{-- opacity-40 DIBUANG agar ikon menyala tajam --}}
                            <i class="fas fa-boxes fa-2x text-warning-custom"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 2: ANTREAN BACK ORDER --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-custom border-left-cyan shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-cyan-custom text-uppercase mb-1 tracking-wider">Antrean Back Order</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">{{ $antreanBackOrder ?? 0 }} <span class="small text-muted" style="font-size: 0.8rem;">Item</span></div>
                        </div>
                        <div class="col-auto">
                            {{-- opacity-40 DIBUANG --}}
                            <i class="fas fa-truck-loading fa-2x text-cyan-custom"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 3: TOTAL ITEM INVENTARIS --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-custom border-left-emerald shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-emerald-custom text-uppercase mb-1 tracking-wider">Item Inventaris</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">{{ number_format($totalProduk ?? 0, 0, ',', '.') }} <span class="small text-muted" style="font-size: 0.8rem;">Master</span></div>
                        </div>
                        <div class="col-auto">
                            {{-- opacity-40 DIBUANG --}}
                            <i class="fas fa-clipboard-list fa-2x text-emerald-custom"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KARTU 4: STOK KRITIS --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-custom border-left-rose shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-rose-custom text-uppercase mb-1 tracking-wider">Peringatan Stok Kritis</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">{{ $stokKritis ?? 0 }} <span class="small text-muted" style="font-size: 0.8rem;">Item</span></div>
                        </div>
                        <div class="col-auto">
                            {{-- opacity-40 DIBUANG --}}
                            <i class="fas fa-exclamation-triangle fa-2x text-rose-custom"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- KIRI: TABEL PRIORITAS PACKING --}}
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden h-100">
                <div class="card-header py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-slate-dark d-flex align-items-center">
                        <i class="fas fa-clipboard-check text-emerald-custom me-2"></i> Prioritas Packing Hari Ini
                    </h6>
                    <a href="{{ route('penjualan.index') }}" class="btn btn-sm btn-light text-slate-muted border rounded-pill px-3 py-1" style="font-size: 0.75rem;">Lihat Semua</a>
                </div>
                <div class="card-body p-0 bg-white flex-grow-1">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-custom-header">
                                <tr>
                                    <th class="px-4 py-3">Waktu Masuk</th>
                                    <th class="py-3">No Referensi</th>
                                    <th class="py-3">Pelanggan</th>
                                    <th class="text-center py-3">Status Filter</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tabelPacking as $tp)
                                <tr>
                                    <td class="px-4 text-slate-muted small fw-medium">{{ $tp->updated_at->diffForHumans() }}</td>
                                    <td class="fw-bold text-slate-dark">{{ $tp->no_so }}</td>
                                    <td>
                                        <div class="fw-medium text-slate-800">{{ $tp->customer ? $tp->customer->nama_customer : '-' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge px-3 py-1.5 rounded-pill fw-bold text-uppercase tracking-wider badge-warning-soft" style="font-size: 0.65rem;">
                                            MENUNGGU
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-slate-muted bg-white">
                                        Belum ada antrean packing untuk hari ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- KANAN: TINDAKAN OPERASIONAL CEPAT --}}
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden h-100 bg-white">
                <div class="card-header py-3 bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-slate-dark d-flex align-items-center">
                        <i class="fas fa-bolt text-warning-custom me-2"></i> Tindakan Operasional Cepat
                    </h6>
                </div>
                <div class="card-body p-4 d-flex flex-column gap-3">
                    
                    <div class="p-3 rounded-3 border" style="background-color: #f8fafc;">
                        <h6 class="fw-bold text-slate-dark mb-1"><i class="fas fa-box text-emerald-custom me-2"></i>Kelola Packing</h6>
                        <p class="small text-slate-muted mb-3">Siapkan dan kemas barang untuk pesanan yang sudah disetujui Direktur.</p>
                        <a href="{{ route('penjualan.index') }}" class="btn btn-sm btn-outline-success w-100 fw-medium">Buka Modul Packing</a>
                    </div>

                    <div class="p-3 rounded-3 border" style="background-color: #f0f9ff; border-color: #bae6fd !important;">
                        <h6 class="fw-bold text-cyan-custom mb-1"><i class="fas fa-dolly me-2"></i>Proses Back Order</h6>
                        <p class="small text-slate-muted mb-3">Penuhi kekurangan barang pesanan dari stok yang baru direstock.</p>
                        <a href="{{ url('/backorder') }}" class="btn btn-sm btn-outline-info w-100 fw-medium">Buka Modul Backorder</a>
                    </div>

                    <div class="p-3 rounded-3 border" style="background-color: #fff1f2; border-color: #fecdd3 !important;">
                        <h6 class="fw-bold text-rose-custom mb-1"><i class="fas fa-exclamation-circle me-2"></i>Barang Stok Kritis</h6>
                        <p class="small text-slate-muted mb-3">Terdapat <b>{{ $stokKritis }} item</b> menipis. Laporkan untuk pengadaan.</p>
                        <a href="{{ url('/barang') }}" class="btn btn-sm btn-outline-danger w-100 fw-medium">Cek Master Barang</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection