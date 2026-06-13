@extends('layouts.app')

@section('content')
<style>
    /* Global Overrides untuk Tema Premium Mentari Atlas */
    body { background-color: #f8fafc !important; }
    
    .text-emerald-custom { color: #10b981 !important; }
    .text-slate-dark { color: #0f172a !important; }
    .text-slate-muted { color: #64748b !important; }
    
    /* Card & Table Styling */
    .card-custom { border: 1px solid #e2e8f0; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    .table-custom-header th { background-color: #f1f5f9 !important; color: #334155 !important; font-weight: 600 !important; border-bottom: 2px solid #e2e8f0 !important; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
    
    /* Custom Soft Border Colors */
    .border-left-emerald { border-left: 4px solid #10b981 !important; }
    .border-left-cyan { border-left: 4px solid #06b6d4 !important; }
    .border-left-rose { border-left: 4px solid #f43f5e !important; }
    .border-left-warning { border-left: 4px solid #f59e0b !important; }
    .border-left-slate { border-left: 4px solid #64748b !important; }

    /* Soft Badges */
    .badge-secondary-soft { background-color: #f1f5f9 !important; color: #475569 !important; border: 1px solid #cbd5e1; }
    .badge-emerald-soft { background-color: #d1fae5 !important; color: #047857 !important; border: 1px solid #a7f3d0; }
    .badge-rose-soft { background-color: #ffe4e6 !important; color: #be123c !important; border: 1px solid #fecdd3; }
</style>

<div class="container-fluid py-4" style="background-color: #f8fafc; min-height: 80vh;">
    
    {{-- HEADER & TOMBOL KEMBALI --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h1 class="h4 mb-1 text-slate-dark fw-bold"><i class="fas fa-history text-emerald-custom me-2"></i>Log Pergerakan Stok</h1>
            <div class="d-flex align-items-center mt-2">
                <span class="badge badge-secondary-soft px-3 py-1.5 rounded-pill fw-bold letter-spacing-1 me-2">{{ $barang->kode_barang }}</span>
                <span class="text-slate-muted fw-medium">{{ $barang->nama_barang }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center">
                <i class="fas fa-arrow-left me-2"></i> Kembali ke Katalog
            </a>
        </div>
    </div>

    {{-- RINGKASAN STOK (5 KARTU) --}}
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-4 mb-4">
        
        <div class="col">
            <div class="card card-custom border-left-emerald shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-emerald-custom text-uppercase mb-1 tracking-wider" style="font-size: 0.7rem;">Stok Bagus</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">{{ $barang->stok_akhir }} <span class="fs-6 text-muted fw-normal">Unit</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box-open fa-2x text-emerald-custom opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="card card-custom border-left-rose shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1 tracking-wider" style="font-size: 0.7rem;">Stok Rusak</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">{{ $barang->stok_rusak ?? 0 }} <span class="fs-6 text-muted fw-normal">Unit</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-heart-broken fa-2x text-danger opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card card-custom border-left-cyan shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1 tracking-wider" style="font-size: 0.7rem;">Total Masuk (In)</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">{{ $barang->barang_masuk ?? 0 }} <span class="fs-6 text-muted fw-normal">Unit</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-alt-circle-down fa-2x text-info opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="card card-custom border-left-warning shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1 tracking-wider" style="font-size: 0.7rem;">Barang Keluar (Out)</div>
                            <div class="h4 mb-0 font-weight-bold text-slate-dark">{{ $barang->barang_keluar ?? 0 }} <span class="fs-6 text-muted fw-normal">Unit</span></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-arrow-alt-circle-up fa-2x text-warning opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="card card-custom border-left-slate shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-slate-muted text-uppercase mb-1 tracking-wider" style="font-size: 0.7rem;">Harga Jual Valid</div>
                            <div class="h5 mb-0 font-weight-bold text-slate-dark">Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-slate-muted opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL RIWAYAT JURNAL STOK --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-custom bg-white overflow-hidden shadow-sm">
                <div class="card-header py-3 bg-white border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-slate-dark d-flex align-items-center">
                        <i class="fas fa-clipboard-list text-slate-muted me-2"></i> Jurnal Pergerakan Stok Detail
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;" width="100%" cellspacing="0">
                            <thead class="table-custom-header">
                                <tr>
                                    <th class="px-4 py-3">Waktu Transaksi</th>
                                    <th class="py-3">Kategori</th>
                                    <th class="py-3">Target Fisik</th>
                                    <th class="py-3 text-center">Mutasi</th>
                                    <th class="py-3 text-center">Sisa Stok</th>
                                    <th class="py-3 pe-4">Keterangan / Catatan Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($histories as $history)
                                
                                {{-- SENSOR & PENERJEMAH OTOMATIS --}}
                                @php
                                    // 1. Sensor Barang Rusak
                                    $keterangan = strtolower($history->keterangan ?? '');
                                    $isRusak = str_contains($keterangan, 'rusak') || str_contains($keterangan, 'cacat');

                                    // 2. Penerjemah Kategori (Agar bahasa lebih profesional)
                                    $kategoriMentah = strtoupper(str_replace('_', ' ', $history->event_label));
                                    
                                    if ($kategoriMentah === 'RETUR CUSTOMER' || $kategoriMentah === 'RETURN CUSTOMER') {
                                        $kategoriTampil = 'RETURN PENJUALAN';
                                    } elseif ($kategoriMentah === 'RETUR SUPPLIER' || $kategoriMentah === 'RETURN SUPPLIER') {
                                        $kategoriTampil = 'RETURN PEMBELIAN';
                                    } else {
                                        $kategoriTampil = $kategoriMentah;
                                    }
                                @endphp

                                <tr class="{{ $isRusak ? 'bg-light' : '' }}">
                                    <td class="px-4 text-slate-dark fw-medium text-nowrap">
                                        {{ $history->created_at->format('d M Y') }} <br>
                                        <span class="text-slate-muted small">{{ $history->created_at->format('H:i:s') }} WIB</span>
                                    </td>
                                    
                                    <td>
                                        <span class="badge badge-secondary-soft px-2.5 py-1.5 rounded">{{ $kategoriTampil }}</span>
                                        <div class="small text-muted mt-1 fw-bold">{{ $history->event_reference ?? '-' }}</div>
                                    </td>
                                    
                                    <td>
                                        @if($isRusak)
                                            <span class="badge badge-rose-soft px-2 py-1"><i class="fas fa-heart-broken me-1"></i> Stok Rusak</span>
                                        @else
                                            <span class="badge badge-emerald-soft px-2 py-1"><i class="fas fa-box me-1"></i> Stok Bagus</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        @if($history->change > 0)
                                            <span class="badge badge-emerald-soft px-2 py-1 fs-6 fw-bold">+{{ $history->change }}</span>
                                        @elseif($history->change < 0)
                                            <span class="badge badge-rose-soft px-2 py-1 fs-6 fw-bold">{{ $history->change }}</span>
                                        @else
                                            <span class="text-muted fw-bold">0</span>
                                        @endif
                                    </td>
                                    
                                    <td class="text-center text-nowrap">
                                        <span class="text-slate-muted me-1">{{ $history->stock_before }}</span> 
                                        <i class="fas fa-arrow-right text-muted mx-1" style="font-size: 0.7rem;"></i> 
                                        <span class="text-slate-dark fw-bold fs-6 ms-1">{{ $history->stock_after }}</span>
                                    </td>
                                    
                                    <td class="pe-4 text-slate-muted" style="max-width: 300px;">
                                        {{ $history->keterangan ?? '-' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-slate-muted bg-white">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-clipboard fa-3x mb-3 text-muted opacity-25"></i>
                                            <span>Belum ada catatan aktivitas masuk/keluar untuk barang ini.</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection