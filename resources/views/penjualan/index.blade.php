@extends('layouts.app')

@section('content')
<style>
    /* CSS Khusus untuk tombol aksi */
    .btn-action-square {
        width: 32px !important;
        height: 32px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        padding: 0 !important;
        border-radius: 0.375rem;
        flex-shrink: 0 !important;
        background-color: #f0fdfa !important; /* Warna hijau sangat muda khas emerald */
        border: 1px solid #a7f3d0 !important; /* Border hijau lembut */
        transition: all 0.2s ease;
    }
    .btn-action-square:hover {
        background-color: #10b981 !important;
        border-color: #10b981 !important;
    }
    .btn-action-square:hover i {
        color: #ffffff !important;
    }
    .btn-action-square.dropdown-toggle::after {
        display: none;
    }
    .table-mentari-compact th, .table-mentari-compact td {
        padding: 0.75rem 0.5rem !important;
    }
    .text-emerald-custom { color: #10b981 !important; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-slate-dark fw-bold">Riwayat & Laporan Sales Order</h1>
    </div>

    {{-- KOTAK NOTIFIKASI SUKSES / ERROR --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4 border-start border-success border-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4 border-start border-danger border-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <strong>Gagal!</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4 border-start border-danger border-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> <strong>Gagal!</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- TABEL PREMIUM MENTARI ATLAS --}}
    <div class="table-wrapper-mentari">
        <div class="table-responsive" style="overflow: visible;">
            <table class="table table-mentari table-mentari-compact align-middle mb-0" style="font-size: 0.85rem;">
                <thead>
                    <tr>
                        <th class="ps-3 text-nowrap" style="width: 10%;">No. SO</th>
                        <th style="width: 10%;">Tanggal</th>
                        <th style="max-width: 130px;">Customer</th>
                        <th>Sales</th>
                        <th class="text-end text-nowrap">Total Nilai</th>
                        <th class="text-center">Skor SPK</th>
                        <th class="text-center">Approval</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-3" style="width: 150px; white-space: nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pengajuan as $so)
                    <tr>
                        <td class="fw-bold text-emerald-custom ps-3 text-nowrap">{{ $so->no_so }}</td>
                        <td>
                            @if($so->sales_created_at)
                                <span class="d-block fw-bold text-dark">{{ \Carbon\Carbon::parse($so->sales_created_at)->format('d/m/y') }}</span>
                                <span class="text-muted" style="font-size: 0.7rem;"><i class="far fa-clock me-1"></i>{{ \Carbon\Carbon::parse($so->sales_created_at)->format('H:i') }}</span>
                            @else
                                <span class="d-block fw-bold text-dark">{{ date('d/m/y', strtotime($so->tanggal_order)) }}</span>
                            @endif
                        </td>
                        <td class="text-wrap" style="max-width: 130px; line-height: 1.2;">
                            <span class="fw-bold d-block text-dark">{{ $so->customer->nama_customer }}</span>
                        </td>
                        <td><span class="badge bg-white text-dark border shadow-sm">{{ $so->user->name }}</span></td>
                        <td class="text-end text-nowrap">
                            <div class="d-flex justify-content-between fw-bold text-dark">
                                <span class="text-muted fw-normal me-1">Rp</span>
                                <span>{{ number_format($so->total_semua, 0, ',', '.') }}</span>
                            </div>
                        </td>
                        
                        <td class="text-center fw-bold">
                            @php $skor = $so->skor_spk ?? 0; @endphp
                            @if($skor >= 70)
                                <span class="text-success" title="Sangat Layak"><i class="fas fa-shield-check me-1"></i>{{ number_format($skor, 0) }}%</span>
                            @elseif($skor >= 40)
                                <span class="text-warning" title="Kurang Layak"><i class="fas fa-exclamation-circle me-1"></i>{{ number_format($skor, 0) }}%</span>
                            @else
                                <span class="text-danger" title="Beresiko"><i class="fas fa-times-circle me-1"></i>{{ number_format($skor, 0) }}%</span>
                            @endif
                        </td>
                        
                        <td class="text-center align-middle">
                            @php
                                $approvalBadgeClass = match(strtolower($so->status_approval)) {
                                    'disetujui' => 'bg-success',
                                    'ditolak' => 'bg-danger',
                                    default => 'bg-warning text-dark'
                                };
                            @endphp
                            
                            <div class="position-relative d-inline-block text-center">
                                <span class="badge {{ $approvalBadgeClass }} px-3 py-1 rounded-pill shadow-sm">
                                    {{ strtoupper($so->status_approval) }}
                                </span>

                                @if($so->catatan)
                                    <div class="position-absolute w-100 text-center" style="top: 100%; left: 0; margin-top: 2px;">
                                        <button type="button" class="btn btn-sm btn-link text-decoration-none border-0 p-0 text-warning fw-bold shadow-none btn-lihat-catatan" data-catatan="{{ $so->catatan }}" style="font-size: 0.65rem;">
                                            <i class="fas fa-comment-dots"></i> Catatan
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </td>

                        <td class="text-center">
                            @if($so->status == 'draft')
                                <span class="badge bg-secondary px-2 py-1 shadow-sm"><i class="fas fa-box"></i> Packing</span>
                            @elseif($so->status == 'ready_to_invoice')
                                <span class="badge bg-primary px-2 py-1 shadow-sm"><i class="fas fa-truck"></i> Kirim</span>
                            @elseif($so->status == 'menunggu_restock')
                                <span class="badge bg-danger px-2 py-1 shadow-sm"><i class="fas fa-clock"></i> Back Order</span>
                            @else
                                <span class="badge bg-info px-2 py-1 shadow-sm">{{ ucfirst($so->status) }}</span>
                            @endif
                        </td>

                        <td class="text-center align-middle pe-3">
                            <div class="d-flex flex-nowrap justify-content-center align-items-center gap-1 mx-auto">
                                
                                {{-- Tombol Detail: Selalu muncul --}}
                                <a href="{{ route('penjualan.show', $so->id) }}" class="btn btn-action-square" title="Lihat Detail">
                                    <i class="fas fa-eye text-emerald-custom"></i>
                                </a>

                                {{-- Logika Edit & Hapus: MUNCUL JIKA STATUS PENDING --}}
                                @if($so->status_approval == 'pending' && (Auth::user()->role == 'direktur' || $so->user_id == Auth::id()))
                                    
                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('penjualan.edit', $so->id) }}" class="btn btn-action-square" title="Edit Data">
                                        <i class="fas fa-edit text-emerald-custom"></i>
                                    </a>
                                    
                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('penjualan.destroy', $so->id) }}" method="POST" class="m-0" onsubmit="return confirm('Yakin ingin menghapus SO ini? Jika ini SO terakhir, nomor SO akan ditarik kembali.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-action-square" title="Hapus SO">
                                            <i class="fas fa-trash text-emerald-custom"></i>
                                        </button>
                                    </form>

                                @endif

                                {{-- FITUR KONFIRMASI PACKING: Disembunyikan untuk role sales --}}
                                @if(strtolower(Auth::user()->role) != 'sales' && $so->status_approval == 'disetujui' && in_array($so->status, ['draft', 'menunggu_restock']))
                                    @php
                                        $totalShippable = 0;
                                        foreach($so->details as $detail) {
                                            $stok = $detail->barang->stok_akhir ?? 0;
                                            $totalShippable += max(0, min($detail->jumlah, $stok));
                                        }
                                    @endphp

                                    @if($totalShippable > 0)
                                        <form action="{{ route('penjualan.packingSelesai', $so->id) }}" method="POST" class="m-0" onsubmit="return confirm('Stok tersedia untuk dipacking. Konfirmasi: Potong stok sekarang?')">
                                            @csrf
                                            <button type="submit" class="btn btn-action-square" title="Konfirmasi Packing Selesai">
                                                <i class="fas fa-box-open text-emerald-custom"></i>
                                            </button>
                                        </form>
                                    @else
                                        @if($so->status == 'draft')
                                            <form action="{{ route('penjualan.sendToBackorder', $so->id) }}" method="POST" class="m-0" onsubmit="return confirm('Stok kosong (0). Pindahkan ke antrean Back Order agar Purchasing bisa restock?')">
                                                @csrf
                                                <button type="submit" class="btn btn-action-square border-danger" title="Stok Kosong! Masukkan ke Back Order">
                                                    <i class="fas fa-box-open text-danger"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> BO</span>
                                        @endif
                                    @endif
                                @endif

                                {{-- Tombol Cetak / Printer: Hilang untuk Sales --}}
                                @if($so->status == 'ready_to_invoice' && strtolower(Auth::user()->role) != 'sales')
                                    <div class="d-flex gap-1 justify-content-center m-0">
                                        <a href="{{ route('penjualan.printSuratJalan', $so->id) }}" target="_blank" class="btn btn-action-square" title="Cetak Surat Jalan">
                                            <i class="fas fa-truck text-emerald-custom"></i>
                                        </a>
                                        <a href="{{ route('penjualan.printFaktur', $so->id) }}" target="_blank" class="btn btn-action-square" title="Cetak Faktur">
                                            <i class="fas fa-file-invoice-dollar text-emerald-custom"></i>
                                        </a>
                                    </div>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted bg-white">
                            <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Belum ada data penjualan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnCatatans = document.querySelectorAll('.btn-lihat-catatan');
        
        btnCatatans.forEach(btn => {
            btn.addEventListener('click', function() {
                const isiCatatan = this.getAttribute('data-catatan');
                
                Swal.fire({
                    title: '<span style="font-size: 1.25rem;">Catatan Approval</span>',
                    html: `<div class="p-3 bg-light rounded text-start text-dark border shadow-sm" style="font-size: 0.95rem;">${isiCatatan}</div>`,
                    icon: 'info',
                    confirmButtonColor: '#10b981',
                    confirmButtonText: '<i class="fas fa-check me-1"></i> Tutup',
                    customClass: {
                        popup: 'rounded-4'
                    }
                });
            });
        });
    });
</script>
@endsection