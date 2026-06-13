@extends('layouts.app')

@section('content')
<style>
    /* Unified Green Action Buttons (Sama persis dengan Customer) */
    .btn-action-mentari { 
        width: 34px; 
        height: 34px; 
        padding: 0; 
        display: inline-flex; 
        align-items: center; 
        justify-content: center; 
        border-radius: 0.5rem; 
        background-color: #ecfdf5; /* Hijau pudar */
        color: #10b981; /* Ikon Hijau Emerald */
        border: 1px solid #a7f3d0;
        transition: all 0.2s ease; 
        flex-shrink: 0; 
    }
    .btn-action-mentari:hover { 
        background-color: #10b981; 
        color: #ffffff; 
        transform: translateY(-2px); 
        box-shadow: 0 4px 8px rgba(16, 185, 129, 0.25); 
    }

    .table-mentari-compact th, .table-mentari-compact td { padding: 0.75rem 0.5rem !important; }
</style>

<div class="container-fluid py-4" style="background-color: #f8fafc; min-height: 80vh;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-slate-dark fw-bold"><i class="fas fa-truck-loading text-emerald-custom me-2"></i>Manajemen Data Supplier</h1>
            <p class="text-slate-muted small mb-0 mt-1">Kelola daftar pemasok/vendor untuk kebutuhan pembelian stok barang Anda.</p>
        </div>
        <button class="btn btn-emerald-custom shadow-sm rounded-pill px-4 fw-bold" style="background-color: #10b981; color: white; border: none;" data-bs-toggle="modal" data-bs-target="#modalTambahSupplier">
            <i class="fas fa-plus me-2"></i> Tambah Supplier
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4 border-start border-success border-4 rounded-3 px-4 py-3" role="alert">
            <i class="fas fa-check-circle text-success me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close mt-1" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4 border-start border-danger border-4 rounded-3 px-4 py-3" role="alert">
            <i class="fas fa-exclamation-circle text-danger me-2"></i><strong>Gagal memproses data!</strong> {{ $errors->first() }}
            <button type="button" class="btn-close mt-1" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TABEL TEMA PREMIUM (Kembar dengan Data Customer) --}}
    <div class="table-wrapper-mentari">
        <div class="table-responsive">
            <table class="table table-mentari table-mentari-compact align-middle mb-0" style="font-size: 0.85rem; width: 100%;">
                <thead>
                    <tr>
                        <th class="ps-3 text-nowrap" style="width: 1%;">No</th>
                        <th class="text-nowrap" style="width: 1%;">Kode Supplier</th>
                        <th>Nama Perusahaan / Supplier</th>
                        <th>No. Telepon / WA</th>
                        <th>No. KTP</th>
                        <th>No. NPWP</th>
                        <th>Alamat Lengkap</th>
                        <th class="text-center pe-3" style="width: 1%; white-space: nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $index => $s)
                    <tr>
                        <td class="ps-3 text-center fw-bold text-slate-muted">{{ $index + 1 }}</td>
                        <td class="fw-bold text-emerald-custom text-nowrap">{{ $s->kode_supplier }}</td>
                        <td class="fw-bold text-slate-dark">{{ $s->nama_supplier }}</td>
                        <td class="text-slate-muted">{{ $s->telepon ?: '-' }}</td>
                        <td class="text-slate-muted">{{ $s->ktp ?: '-' }}</td>
                        <td class="text-slate-muted">{{ $s->npwp ?: '-' }}</td>
                        <td class="text-truncate" style="max-width: 150px;" title="{{ $s->alamat }}">{{ $s->alamat ?: '-' }}</td>
                        
                        <td class="pe-3 text-center align-middle" style="white-space: nowrap;">
                            <div class="d-flex justify-content-center align-items-center gap-2 flex-nowrap">
                                
                                {{-- Tombol Edit --}}
                                <button type="button" class="btn-action-mentari" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $s->id }}" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </button>

                                {{-- Tombol Hapus --}}
                                <form action="{{ route('supplier.destroy', $s->id) }}" method="POST" class="m-0 ms-1 d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action-mentari" title="Hapus Supplier" onclick="return confirm('Apakah Anda yakin ingin menghapus supplier {{ $s->nama_supplier }}?')">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-slate-muted bg-white">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fas fa-truck-loading fa-3x mb-3 text-muted opacity-25"></i>
                                <span>Belum ada data supplier yang terdaftar.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- =========================================================================
    KUMPULAN MODAL (DILETAKKAN DI LUAR TABEL AGAR LAYOUT TIDAK HANCUR)
========================================================================== --}}

@foreach($suppliers as $s)
    {{-- MODAL EDIT SUPPLIER --}}
    <div class="modal fade" id="modalEdit{{ $s->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('supplier.update', $s->id) }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                @csrf @method('PUT')
                <div class="modal-header bg-light border-bottom py-3">
                    <h6 class="modal-title fw-bold text-slate-dark"><i class="fas fa-edit text-warning me-2"></i>Edit Data: {{ $s->nama_supplier }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-white">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-slate-dark">Kode Supplier <span class="text-danger">*</span></label>
                            <input type="text" name="kode_supplier" class="form-control bg-light" value="{{ $s->kode_supplier }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-slate-dark">Nama Supplier / PT <span class="text-danger">*</span></label>
                            <input type="text" name="nama_supplier" class="form-control bg-light" value="{{ $s->nama_supplier }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-slate-dark">Nomor KTP</label>
                            <input type="text" name="ktp" class="form-control bg-light" value="{{ $s->ktp }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-slate-dark">Nomor NPWP</label>
                            <input type="text" name="npwp" class="form-control bg-light" value="{{ $s->npwp }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-slate-dark">No. Telepon / WA</label>
                            <input type="text" name="telepon" class="form-control bg-light" value="{{ $s->telepon }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-slate-dark">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control bg-light" rows="2">{{ $s->alamat }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-emerald-custom rounded-pill px-4 fw-bold" style="background-color: #10b981; color: white; border: none;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- MODAL TAMBAH SUPPLIER BARU --}}
<div class="modal fade" id="modalTambahSupplier" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('supplier.store') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <div class="modal-header text-white border-bottom-0 py-3" style="background-color: #10b981;">
                <h6 class="modal-title fw-bold"><i class="fas fa-truck-loading me-2"></i>Registrasi Data Supplier</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-slate-dark">Kode Supplier <span class="text-danger">*</span></label>
                        <input type="text" name="kode_supplier" class="form-control bg-light" placeholder="Contoh: SUP-01" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-slate-dark">Nama Supplier / PT <span class="text-danger">*</span></label>
                        <input type="text" name="nama_supplier" class="form-control bg-light" placeholder="Contoh: PT. Sumber Makmur" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-slate-dark">Nomor KTP</label>
                        <input type="text" name="ktp" class="form-control bg-light" placeholder="16 digit NIK">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-slate-dark">Nomor NPWP</label>
                        <input type="text" name="npwp" class="form-control bg-light" placeholder="Nomor NPWP perusahaan/pribadi">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-slate-dark">No. Telepon / WA</label>
                        <input type="text" name="telepon" class="form-control bg-light" placeholder="0812xxxx">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold text-slate-dark">Alamat Lengkap</label>
                        <textarea name="alamat" class="form-control bg-light" rows="2" placeholder="Alamat gudang supplier..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-3 border-top">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-emerald-custom rounded-pill px-4 fw-bold" style="background-color: #10b981; color: white; border: none;">Simpan Supplier Baru</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
</script>
@endsection