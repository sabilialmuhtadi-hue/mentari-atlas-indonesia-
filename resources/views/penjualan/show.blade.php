@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Sales Order: {{ $penjualan->no_so }}</h1>
        <a href="{{ route('penjualan.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Order</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr><td class="text-muted">Customer:</td><td class="fw-bold">{{ $penjualan->customer->nama_customer }}</td></tr>
                        <tr><td class="text-muted">Tanggal:</td><td>{{ \Carbon\Carbon::parse($penjualan->tanggal_order)->format('d F Y') }}</td></tr>
                        <tr><td class="text-muted">Sales:</td><td>{{ $penjualan->user->name }}</td></tr>
                        <tr><td class="text-muted">Status:</td><td>
                            <span class="badge {{ $penjualan->status_approval == 'disetujui' ? 'bg-success' : ($penjualan->status_approval == 'pending' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                {{ ucfirst($penjualan->status_approval) }}
                            </span>
                        </td></tr>
                        <tr><td class="text-muted">Probabilitas:</td><td class="fw-bold text-primary">{{ $penjualan->peluang }}%</td></tr>
                    </table>
                </div>
            </div>

            <div class="card shadow mb-4 border-left-info">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-history fa-sm"></i> Audit Trail Transaksi</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Dibuat Oleh Sales:</small>
                        <span class="font-weight-bold text-gray-800">{{ $penjualan->sales_created_by ?? $penjualan->user->name }}</span>
                        <small class="d-block text-gray-600">
                            <i class="fas fa-clock fa-sm"></i> {{ $penjualan->sales_created_at ? $penjualan->sales_created_at->format('d-m-Y H:i:s') : '-' }} WIB
                        </small>
                    </div>
                    
                    <hr class="my-2">

                    <div>
                        <small class="text-muted d-block">Status Kelayakan / Approval:</small>
                        @if($penjualan->status_approval === 'disetujui')
                            <span class="badge bg-success mb-1"><i class="fas fa-check-circle"></i> Disetujui</span>
                            <small class="d-block text-gray-800">Oleh: <strong>{{ $penjualan->approver->name ?? 'Direktur' }}</strong></small>
                            <small class="d-block text-gray-600">
                                <i class="fas fa-clock fa-sm"></i> {{ $penjualan->approved_at ? $penjualan->approved_at->format('d-m-Y H:i:s') : '-' }} WIB
                            </small>
                        @elseif($penjualan->status_approval === 'ditolak')
                            <span class="badge bg-danger mb-1"><i class="fas fa-times-circle"></i> Ditolak</span>
                            <small class="d-block text-gray-800">Oleh: <strong>{{ $penjualan->approver->name ?? 'Direktur' }}</strong></small>
                            <small class="d-block text-gray-600">
                                <i class="fas fa-clock fa-sm"></i> {{ $penjualan->approved_at ? $penjualan->approved_at->format('d-m-Y H:i:s') : '-' }} WIB
                            </small>
                        @else
                            <span class="badge bg-warning text-dark mb-1"><i class="fas fa-hourglass-half"></i> Menunggu Review</span>
                            <small class="d-block text-muted-italic"><em>Belum ada tindakan dari Direktur.</em></small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">Daftar Barang</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-3">Nama Barang</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th class="text-end px-3">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($penjualan->details as $detail)
                                <tr>
                                    <td class="px-3">{{ $detail->barang->nama_barang }}</td>
                                    <td class="text-center">{{ $detail->jumlah }}</td>
                                    <td class="text-end">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                    <td class="text-end px-3 fw-bold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">TOTAL NILAI SO:</td>
                                    <td class="text-end px-3 text-primary" style="font-size: 1.1rem;">Rp {{ number_format($penjualan->total_semua, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection