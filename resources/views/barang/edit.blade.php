@extends('layouts.app')

@section('content')
<style>
    /* Global Overrides untuk Tema Premium Mentari Atlas */
    body { background-color: #f8fafc !important; }
    
    .text-emerald-custom { color: #10b981 !important; }
    .text-slate-dark { color: #0f172a !important; }
    .text-slate-muted { color: #64748b !important; }
    
    .bg-emerald-custom { background-color: #10b981 !important; color: #ffffff !important; }
    .btn-emerald-custom { background-color: #10b981 !important; border-color: #10b981 !important; color: #ffffff !important; font-weight: 500; transition: all 0.2s; }
    .btn-emerald-custom:hover { background-color: #059669 !important; color: #ffffff !important; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
    
    /* Card Styling */
    .card-custom { border: 1px solid #e2e8f0; border-radius: 0.75rem; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025); }
    
    /* Form Input Customization */
    .form-control:focus { border-color: #10b981; box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.15); background-color: #ffffff !important; }
    .input-group-text-custom { background-color: #f1f5f9; border-color: #e2e8f0; color: #64748b; font-weight: 600; }
</style>

<div class="container-fluid py-4" style="background-color: #f8fafc; min-height: 80vh;">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h4 mb-0 text-slate-dark fw-bold"><i class="fas fa-edit text-emerald-custom me-2"></i>Edit Master Barang</h1>
                    <p class="text-slate-muted small mb-0 mt-1">Perbarui rincian spesifikasi, harga beli (HPP), dan stok fisik.</p>
                </div>
                <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center">
                    <i class="fas fa-arrow-left me-2"></i> Batal / Kembali
                </a>
            </div>

            <div class="card card-custom bg-white overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                    <span class="badge badge-secondary-soft text-slate-dark border px-3 py-1.5 rounded-pill fw-bold letter-spacing-1" style="background-color: #f1f5f9;">
                        SKU: {{ $barang->kode_barang }}
                    </span>
                    <h6 class="m-0 ms-3 fw-bold text-slate-dark">{{ $barang->nama_barang }}</h6>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('barang.update', $barang->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label text-slate-dark fw-bold small">Kode Barang (SKU) <span class="text-danger">*</span></label>
                                <input type="text" name="kode_barang" class="form-control bg-light @error('kode_barang') is-invalid @enderror" 
                                       value="{{ old('kode_barang', $barang->kode_barang) }}" required>
                                @error('kode_barang')
                                    <div class="invalid-feedback fw-medium">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-slate-dark fw-bold small">Nama Barang <span class="text-danger">*</span></label>
                                <input type="text" name="nama_barang" class="form-control bg-light @error('nama_barang') is-invalid @enderror" 
                                       value="{{ old('nama_barang', $barang->nama_barang) }}" required>
                                @error('nama_barang')
                                    <div class="invalid-feedback fw-medium">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-slate-dark fw-bold small">Spesifikasi / Ukuran</label>
                                <input type="text" name="spesifikasi" class="form-control bg-light @error('spesifikasi') is-invalid @enderror" 
                                       value="{{ old('spesifikasi', $barang->spesifikasi) }}" placeholder="Contoh: 1 Liter, 12V">
                                @error('spesifikasi')
                                    <div class="invalid-feedback fw-medium">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-slate-dark fw-bold small">Merek / Brand</label>
                                <input type="text" name="merek" class="form-control bg-light @error('merek') is-invalid @enderror" 
                                       value="{{ old('merek', $barang->merek) }}" placeholder="Contoh: Honda, Pertamina">
                                @error('merek')
                                    <div class="invalid-feedback fw-medium">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12"><hr class="text-muted opacity-25 my-2"></div>

                            {{-- HPP / Harga Beli --}}
                            <div class="col-md-6">
                                <label class="form-label text-slate-dark fw-bold small">HPP / Harga Beli <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text input-group-text-custom">Rp</span>
                                    <input type="number" name="harga_beli" class="form-control text-end fw-bold text-danger border-warning @error('harga_beli') is-invalid @enderror" 
                                           value="{{ old('harga_beli', $barang->harga_beli) }}" min="0" required>
                                </div>
                                <div class="form-text small text-slate-muted mt-1" style="font-size: 0.65rem; line-height: 1.2;">
                                    <i class="fas fa-exclamation-circle me-1"></i>Perubahan HPP manual langsung update margin profit.
                                </div>
                                @error('harga_beli')
                                    <div class="invalid-feedback d-block fw-medium">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Harga Jual --}}
                            <div class="col-md-6">
                                <label class="form-label text-slate-dark fw-bold small">Harga Jual Baru <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text input-group-text-custom">Rp</span>
                                    <input type="number" name="harga_jual" class="form-control text-end fw-bold border-emerald-custom @error('harga_jual') is-invalid @enderror" 
                                           style="color: #10b981;" value="{{ old('harga_jual', $barang->harga_jual) }}" min="0" required>
                                </div>
                                @error('harga_jual')
                                    <div class="invalid-feedback d-block fw-medium">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- STOK MANUAL (BAGUS DAN RUSAK) --}}
                            <div class="col-md-6 mt-4">
                                <label class="form-label text-slate-dark fw-bold small">Stok Tersedia (BAGUS) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="stok_akhir" class="form-control text-center fw-bold text-emerald-custom @error('stok_akhir') is-invalid @enderror" 
                                           value="{{ old('stok_akhir', $barang->stok_akhir) }}" min="0" required>
                                    <span class="input-group-text input-group-text-custom">Pcs</span>
                                </div>
                                <div class="form-text small text-slate-muted mt-1" style="font-size: 0.65rem; line-height: 1.2;">
                                    Update manual jumlah fisik barang bagus.
                                </div>
                                @error('stok_akhir')
                                    <div class="invalid-feedback d-block fw-medium">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mt-4">
                                <label class="form-label text-slate-dark fw-bold small">Stok Cacat/RUSAK <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="stok_rusak" class="form-control text-center fw-bold text-danger @error('stok_rusak') is-invalid @enderror" 
                                           value="{{ old('stok_rusak', $barang->stok_rusak ?? 0) }}" min="0" required>
                                    <span class="input-group-text input-group-text-custom">Pcs</span>
                                </div>
                                <div class="form-text small text-slate-muted mt-1" style="font-size: 0.65rem; line-height: 1.2;">
                                    Update manual jumlah fisik barang cacat.
                                </div>
                                @error('stok_rusak')
                                    <div class="invalid-feedback d-block fw-medium">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
                            <span class="small text-slate-muted"><i class="fas fa-info-circle text-emerald-custom me-1"></i> Pastikan angka sesuai fisik gudang.</span>
                            <div class="d-flex gap-3">
                                <button type="reset" class="btn btn-light rounded-pill px-4 fw-bold border">Reset</button>
                                <button type="submit" class="btn btn-emerald-custom rounded-pill px-5 shadow-sm fw-bold"><i class="fas fa-save me-2"></i> Update Barang</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection