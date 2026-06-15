@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<style>
    /* Global Overrides untuk Tema Premium Mentari Atlas */
    body { background-color: #f8fafc !important; }
    
    .text-emerald-custom { color: #10b981 !important; }
    .text-slate-dark { color: #0f172a !important; }
    .text-slate-muted { color: #64748b !important; }
    
    .bg-emerald-custom { background-color: #10b981 !important; color: #ffffff !important; }
    .btn-emerald-custom { background-color: #10b981 !important; border-color: #10b981 !important; color: #ffffff !important; font-weight: 500; transition: all 0.2s; }
    .btn-emerald-custom:hover { background-color: #059669 !important; color: #ffffff !important; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
    
    /* Card & Table Styling */
    .card-custom { border: 1px solid #e2e8f0; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    
    /* Diet Ketat Tabel */
    .table-mentari-compact th, .table-mentari-compact td { padding: 0.75rem 0.5rem !important; }

    /* Soft Badges & Focus Elements */
    .badge-success-soft { background-color: #d1fae5 !important; color: #065f46 !important; border: 1px solid #a7f3d0; }
    .badge-danger-soft { background-color: #fee2e2 !important; color: #991b1b !important; border: 1px solid #fecaca; }
    .badge-warning-soft { background-color: #fef3c7 !important; color: #92400e !important; border: 1px solid #fde68a; }
    .badge-secondary-soft { background-color: #f1f5f9 !important; color: #475569 !important; border: 1px solid #cbd5e1; }
    .form-control:focus { border-color: #10b981; box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.15); background-color: #ffffff !important; }

    /* Nominal Fonts */
    .font-monospace-custom { font-family: 'Courier New', Courier, monospace; font-weight: 700; letter-spacing: -0.5px; }

    /* Styling Select2 Emerald Theme */
    .select2-container--bootstrap-5 .select2-selection {
        border-color: #e2e8f0 !important; border-radius: 0.375rem !important;
        padding: 0.375rem 0.75rem !important; height: auto !important;
        font-size: 0.8rem; background-color: #f8fafc;
    }
    .select2-container--bootstrap-5 .select2-selection:focus,
    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: #10b981 !important; box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.15) !important; background-color: #ffffff;
    }
</style>

<div class="container-fluid py-4" style="background-color: #f8fafc; min-height: 80vh;">
    
    {{-- HEADER --}}
    <div class="d-flex align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-slate-dark fw-bold"><i class="fas fa-shopping-cart text-emerald-custom me-2"></i>Pembelian & Restock Barang</h1>
            <p class="text-slate-muted small mb-0 mt-1">Catat transaksi pembelian (PO) dan lakukan Quality Control (QC) saat barang tiba.</p>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert badge-success-soft alert-dismissible fade show border-0 shadow-sm rounded-3 px-4 py-3 mb-4">
            <i class="fas fa-check-circle text-success me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close mt-1" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert badge-danger-soft alert-dismissible fade show border-0 shadow-sm rounded-3 px-4 py-3 mb-4">
            <i class="fas fa-exclamation-triangle text-danger me-2"></i><strong>Gagal!</strong>
            <ul class="mb-0 mt-1 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close mt-1" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- KOLOM KIRI: FORM INPUT PEMBELIAN (BUAT PO) --}}
        <div class="col-lg-4 col-xl-3">
            <div class="card card-custom bg-white overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold text-slate-dark d-flex align-items-center">
                        <i class="fas fa-file-invoice text-emerald-custom me-2"></i> Buat Order (PO)
                    </h6>
                </div>

                <div class="card-body p-4">
                    <form action="{{ url('/pembelian') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="small fw-bold text-slate-dark mb-1">Nama Supplier <span class="text-danger">*</span></label>
                            <select name="nama_supplier" id="nama_supplier" class="form-select select2-supplier" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->nama_supplier }}">{{ $s->kode_supplier }} - {{ $s->nama_supplier }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="small fw-bold text-slate-dark mb-1">Pilih Item Barang <span class="text-danger">*</span></label>
                            <select name="barang_id" id="barang_id" class="form-select select2-search" onchange="updateHPP(this)" required>
                                <option value="">-- Cari SKU/Nama Barang --</option>
                                @foreach($barangs as $b)
                                    @php
                                        $kurangBO = \App\Models\BackOrder::where('barang_id', $b->id)
                                                        ->where(function($query) {
                                                            $query->where('status_bo', 'antrean')
                                                                  ->orWhere('status_bo', 'pending');
                                                        })
                                                        ->sum('jumlah_kurang');
                                        
                                        $infoBO = $kurangBO > 0 ? " | ⚠️ Restock: $kurangBO" : "";
                                    @endphp
                                    <option value="{{ $b->id }}" data-hpp="{{ $b->harga_beli ?? 0 }}">
                                        [{{ $b->kode_barang }}] {{ $b->nama_barang }} (Sisa: {{ $b->stok_akhir ?? $b->stok ?? 0 }}{{ $infoBO }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3 align-items-end">
                            <div class="col-4">
                                <label class="small fw-bold text-slate-dark mb-1 d-block text-nowrap">Qty <span class="text-danger">*</span></label>
                                <input type="number" name="jumlah_beli" id="jumlah_beli" class="form-control text-center bg-light fw-bold px-1" min="1" placeholder="0" oninput="hitungTotal()" required>
                            </div>
                            <div class="col-8">
                                <label class="small fw-bold text-slate-dark mb-1 d-block text-nowrap">HPP / Pcs (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="harga_beli_hpp" id="harga_beli_hpp" class="form-control text-end bg-light fw-bold" min="0" placeholder="0" oninput="hitungTotal()" required>
                            </div>
                        </div>

                        <div class="p-3 mb-4 mt-2 rounded-3 border-0 badge-success-soft shadow-sm d-flex flex-column align-items-center justify-content-center">
                            <span class="small fw-bold text-success text-uppercase tracking-wider mb-1">Estimasi Total Utang</span>
                            <h4 class="fw-extrabold mb-0 text-success" id="label-total">Rp 0</h4>
                        </div>

                        <button type="submit" class="btn btn-emerald-custom w-100 fw-bold py-2 rounded-pill shadow-sm">
                            <i class="fas fa-paper-plane me-1"></i> Ajukan Pembelian (PO)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: TABEL RIWAYAT PEMBELIAN & QC --}}
        <div class="col-lg-8 col-xl-9">
            <h6 class="fw-bold text-slate-dark mb-3"><i class="fas fa-boxes text-emerald-custom me-2"></i> Jurnal Order & Quality Control</h6>
            <div class="table-wrapper-mentari">
                <div class="table-responsive">
                    <table class="table table-mentari table-mentari-compact align-middle" style="width: 100%; font-size: 0.8rem;">
                        <thead>
                            <tr>
                                <th class="ps-3 text-center text-nowrap">No PO</th>
                                <th style="max-width: 130px;">Supplier</th>
                                <th style="max-width: 160px;">Item Barang</th>
                                <th class="text-center">Order</th>
                                <th class="text-center">Status QC</th>
                                <th class="text-center pe-3">Aksi / Hasil Sortir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $lastPO = ''; @endphp
                            @forelse($riwayat as $r)
                            @php 
                                $isDuplicate = ($r->no_pembelian == $lastPO);
                                $lastPO = $r->no_pembelian;
                            @endphp
                            <tr>
                                @if($isDuplicate)
                                    <td class="ps-3 text-center fw-medium text-muted" style="opacity: 0.3; white-space: nowrap !important;">
                                        <i class="fas fa-link me-1" style="font-size: 0.6rem;"></i> {{ $r->no_pembelian }}
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary-soft px-2 py-1 text-wrap text-start" style="max-width: 130px; opacity: 0.3;">
                                            <i class="fas fa-building me-1 opacity-50"></i>{{ $r->nama_supplier }}
                                        </span>
                                    </td>
                                @else
                                    <td class="ps-3 text-center fw-bold text-emerald-custom" style="white-space: nowrap !important;">
                                        {{ $r->no_pembelian }}<br>
                                        <small class="text-slate-muted fw-normal">{{ \Carbon\Carbon::parse($r->tanggal_beli)->format('d/m/y') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary-soft px-2 py-1 text-wrap text-start shadow-sm" style="max-width: 130px; line-height: 1.4;">
                                            <i class="fas fa-building me-1 opacity-50"></i>{{ $r->nama_supplier }}
                                        </span>
                                    </td>
                                @endif
                                
                                <td class="fw-bold text-slate-dark text-wrap" style="max-width: 160px; line-height: 1.3;">
                                    {{ $r->barang->nama_barang }}<br>
                                    <span class="text-muted fw-normal" style="font-size: 0.7rem;">Rp {{ number_format($r->harga_beli_hpp, 0, ',', '.') }} / pcs</span>
                                </td>
                                
                                <td class="text-center fw-bold text-dark fs-6">
                                    {{ $r->jumlah_beli }}
                                </td>
                                
                                <td class="text-center">
                                    {{-- PERUBAHAN STATUS QC DINAMIS --}}
                                    @if($r->status_barang === 'pending')
                                        <span class="badge badge-warning-soft px-2 py-1 rounded-pill"><i class="fas fa-clock me-1"></i>Menunggu Sortir</span>
                                    @else
                                        <span class="badge badge-success-soft px-2 py-1 rounded-pill"><i class="fas fa-check-circle me-1"></i>Selesai QC</span>
                                    @endif
                                </td>

                                <td class="text-center pe-3">
                                    {{-- PERUBAHAN TOMBOL AKSI MENJADI LIHAT DETAIL --}}
                                    @if($r->status_barang === 'pending')
                                        <button class="btn btn-sm btn-warning shadow-sm fw-bold rounded-pill" data-bs-toggle="modal" data-bs-target="#modalQC{{ $r->id }}">
                                            <i class="fas fa-box-open me-1"></i> Mulai Sortir
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-light border fw-bold text-secondary shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalDetailQC{{ $r->id }}">
                                            <i class="fas fa-eye me-1"></i> Lihat Detail
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-slate-muted bg-white">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-clipboard-list fa-3x mb-3 text-muted opacity-25"></i>
                                        <span>Belum ada riwayat transaksi pembelian dari supplier.</span>
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

{{-- MODAL QUALITY CONTROL & DETAIL QC (DI LUAR TABEL) --}}
@foreach($riwayat as $r)
    {{-- 1. MODAL UNTUK PROSES SORTIR (STATUS PENDING) --}}
    @if($r->status_barang === 'pending')
    <div class="modal fade" id="modalQC{{ $r->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('pembelian.sortir', $r->id) }}" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
                @csrf
                <div class="modal-header bg-light border-0 py-3">
                    <h6 class="modal-title fw-bold text-slate-dark"><i class="fas fa-tasks text-warning me-2"></i>Quality Control (QC) Kedatangan Barang</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="alert alert-info border-0 shadow-sm py-2 px-3 mb-4 d-flex align-items-center">
                        <i class="fas fa-info-circle fs-4 me-3 text-info"></i>
                        <div>
                            <span class="d-block small text-muted">Total Order di PO ini:</span>
                            <strong class="text-dark fs-5">{{ $r->jumlah_beli }} Pcs <span class="fs-6 fw-normal text-muted">({{ $r->barang->nama_barang }})</span></strong>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-success"><i class="fas fa-check-circle me-1"></i> Qty Bagus (Siap Jual)</label>
                            <input type="number" name="qty_bagus" class="form-control form-control-lg text-center fw-bold text-success border-success" value="{{ $r->jumlah_beli }}" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-danger"><i class="fas fa-times-circle me-1"></i> Qty Cacat / Rusak</label>
                            <input type="number" name="qty_rusak" class="form-control text-center fw-bold text-danger border-danger" value="0" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-warning" style="color: #d97706 !important;"><i class="fas fa-minus-circle me-1"></i> Qty Kurang / Hilang</label>
                            <input type="number" name="qty_kurang" class="form-control text-center fw-bold border-warning" value="0" min="0" required style="color: #d97706;">
                        </div>
                    </div>

                    <div class="form-check form-switch mt-4 bg-light p-3 rounded border">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="potong_tagihan" id="potongTagihan{{ $r->id }}" value="1" checked style="cursor: pointer;">
                        <label class="form-check-label small fw-bold text-slate-dark" for="potongTagihan{{ $r->id }}" style="cursor: pointer;">
                            Auto-Debit Note (Klaim Utang) & Return Pembelian
                        </label>
                        <div class="small text-muted mt-1" style="font-size: 0.7rem; margin-left: 2.2rem;">
                            Jika diaktifkan, barang yang Rusak/Kurang akan otomatis memotong total utang tagihan supplier ke kita dan masuk ke return pembelian.
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-secondary shadow-sm px-4 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald-custom shadow-sm px-4 rounded-pill fw-bold" onclick="return confirm('Sudah yakin dengan hitungan QC-nya? Stok akan difinalisasi dan tidak dapat diubah lagi.')">
                        Selesai & Masukkan ke Stok
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- 2. MODAL BARU UNTUK LIHAT DETAIL HASIL SORTIR & KALKULASI TOTAL (STATUS SELESAI) --}}
    @if($r->status_barang === 'selesai')
    <div class="modal fade" id="modalDetailQC{{ $r->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
                <div class="modal-header bg-light border-0 py-3">
                    <h6 class="modal-title fw-bold text-slate-dark"><i class="fas fa-clipboard-check text-emerald-custom me-2"></i>Rincian Hasil Pemilahan QC</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="mb-3">
                        <label class="small text-slate-muted fw-bold text-uppercase d-block mb-1">No. Purchase Order</label>
                        <div class="fw-bold text-dark fs-6">{{ $r->no_pembelian }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-slate-muted fw-bold text-uppercase d-block mb-1">Nama Barang / SKU</label>
                        <div class="fw-bold text-slate-dark">{{ $r->barang->nama_barang }}</div>
                    </div>
                    
                    <hr class="border-secondary-subtle my-3">
                    
                    <label class="small text-slate-muted fw-bold text-uppercase d-block mb-2">Komposisi Hasil Fisik Sortiran</label>
                    <div class="p-3 bg-light rounded-3 border border-secondary-subtle mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Barang Bagus (Murni Stok):</span>
                            <span class="fw-bold text-success fs-6">{{ $r->qty_bagus }} Pcs</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-danger fw-bold"><i class="fas fa-times-circle me-1"></i> Cacat / Rusak (Karantina):</span>
                            <span class="fw-bold text-danger fs-6">{{ $r->qty_rusak }} Pcs</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-warning fw-bold" style="color: #d97706 !important;"><i class="fas fa-minus-circle me-1"></i> Kurang / Selisih Hilang:</span>
                            <span class="fw-bold fs-6" style="color: #d97706 !important;">{{ $r->qty_kurang }} Pcs</span>
                        </div>
                    </div>

                    {{-- BAGIAN KALKULASI PRESET SESUAI PERMINTAAN USER --}}
                    <div class="p-3 rounded-3 shadow-sm border-0 badge-success-soft">
                        <div class="small fw-bold text-success text-uppercase tracking-wider mb-2 text-center">Kalkulasi Nilai Pembelian Transaksi</div>
                        <div class="d-flex justify-content-between align-items-center" style="font-size: 0.85rem;">
                            <span class="text-slate-muted fw-medium">Rincian Nota Awal:</span>
                            <span class="fw-bold text-slate-dark">Rp {{ number_format($r->harga_beli_hpp, 0, ',', '.') }} / pcs x {{ $r->jumlah_beli }}</span>
                        </div>
                        <hr class="my-2 border-success opacity-25">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-success">Total Biaya Jurnal PO:</span>
                            <span class="fw-extrabold text-success font-monospace-custom fs-5">Rp {{ number_format($r->total_bayar, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-secondary btn-sm shadow-sm px-4 rounded-pill" data-bs-dismiss="modal">Tutup Detail</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

<script>
    $(document).ready(function() {
        $('.select2-search, .select2-supplier').select2({
            theme: 'bootstrap-5',
            placeholder: $(this).data('placeholder'),
            allowClear: true,
            width: '100%'
        });

        $('#nama_supplier').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Cari/Pilih Nama Supplier --',
            allowClear: true,
            width: '100%'
        });
    });

    function updateHPP(selectElement) {
        if (!selectElement || selectElement.selectedIndex === -1) {
            document.getElementById('harga_beli_hpp').value = '';
            hitungTotal();
            return;
        }
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        var hpp = selectedOption.getAttribute('data-hpp');
        if (hpp !== null && hpp !== '') {
            document.getElementById('harga_beli_hpp').value = hpp;
        } else {
            document.getElementById('harga_beli_hpp').value = '';
        }
        hitungTotal();
    }

    function hitungTotal() {
        const qty = document.getElementById('jumlah_beli').value || 0;
        const hpp = document.getElementById('harga_beli_hpp').value || 0;
        const total = qty * hpp;
        document.getElementById('label-total').innerText = "Rp " + parseInt(total).toLocaleString('id-ID');
    }
</script>
@endsection