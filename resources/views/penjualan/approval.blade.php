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
    
    /* Card & Table Styling */
    .card-custom { border: 1px solid #e2e8f0; border-radius: 0.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    .table-custom-header th { background-color: #f1f5f9 !important; color: #334155 !important; font-weight: 600 !important; border-bottom: 2px solid #e2e8f0 !important; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
    
    /* Soft Badges */
    .badge-success-soft { background-color: #d1fae5 !important; color: #065f46 !important; border: 1px solid #a7f3d0; }
    .badge-warning-soft { background-color: #fef3c7 !important; color: #92400e !important; border: 1px solid #fde68a; }
    .badge-danger-soft { background-color: #fee2e2 !important; color: #991b1b !important; border: 1px solid #fecaca; }
    .badge-secondary-soft { background-color: #f1f5f9 !important; color: #475569 !important; border: 1px solid #cbd5e1; }

    /* Custom Progress Bar */
    .progress-wrapper { background-color: #e2e8f0; border-radius: 999px; height: 18px; overflow: hidden; position: relative; }
    .progress-fill { height: 100%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: bold; color: white; transition: width 0.5s ease; }
    .fill-safe { background-color: #10b981; }
    .fill-warn { background-color: #f59e0b; }
    .fill-risk { background-color: #ef4444; }

    /* Custom Input Edit Harga & QTY (Diet Mode) */
    .input-harga-edit { background-color: #f0fdfa !important; border: 1px solid #a7f3d0 !important; color: #047857 !important; font-size: 0.85rem !important; padding: 0.25rem 0.5rem !important; height: auto !important; }
    .input-harga-edit:focus { border-color: #10b981 !important; box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25) !important; }
    
    .input-qty-edit { background-color: #fffbeb !important; border: 1px solid #fde68a !important; color: #b45309 !important; width: 60px !important; margin: 0 auto; font-size: 0.85rem !important; padding: 0.25rem !important; height: auto !important; }
    .input-qty-edit:focus { border-color: #f59e0b !important; box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.25) !important; }

    /* Tombol Pilihan Harga */
    .btn-apply-harga { cursor: pointer; transition: transform 0.1s ease, box-shadow 0.1s ease; font-size: 0.65rem !important; padding: 0.2rem 0.4rem !important; }
    .btn-apply-harga:hover { transform: translateY(-2px); box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
    .badge-bronze { background-color: #cd7f32 !important; color: white !important; }

    /* Modal Minimalist Overrides */
    .modal-compact-body { padding: 1rem !important; }
    .table-compact td, .table-compact th { padding: 0.5rem !important; font-size: 0.8rem !important; }
    
    /* Plafon Warning Box */
    .plafon-warning-box { background-color: #fff1f2; border: 1px dashed #fecaca; color: #be123c; border-radius: 0.5rem; padding: 0.5rem; font-size: 0.75rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; }
</style>

<div class="container-fluid py-4" style="background-color: #f8fafc; min-height: 80vh;">
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-slate-dark fw-bold">Otorisasi Sales Order</h1>
            <p class="text-slate-muted small mb-0 mt-1">Review rincian, periksa sisa limit, dan berikan persetujuan pesanan.</p>
        </div>
        
        <div>
            <a href="{{ route('penjualan.create') }}" class="btn btn-emerald-custom rounded-pill fw-bold shadow-sm px-4">
                <i class="fas fa-plus me-2"></i> Buat Order
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-custom bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
                    <i class="fas fa-clipboard-check fs-4 text-emerald-custom me-3"></i>
                    <h6 class="mb-0 fw-bold text-slate-dark">Antrean Dokumen Approval</h6>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-custom-header text-center">
                                <tr>
                                    <th class="py-3 px-3">No SO</th>
                                    <th class="py-3">Waktu Input</th>
                                    <th class="py-3 text-start">Customer</th>
                                    <th class="py-3">Sales</th>
                                    <th class="py-3 text-end px-4">Nilai Order</th>
                                    <th class="py-3" style="width: 140px;">Kelayakan (SPK)</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pengajuan as $item)
                                <tr>
                                    <td class="fw-bold text-emerald-custom text-center px-3">{{ $item->no_so }}</td>
                                    
                                    <td class="text-center text-slate-muted small fw-medium">
                                        @if($item->sales_created_at)
                                            {{ $item->sales_created_at->format('d/m/Y') }}<br>
                                            <span style="font-size: 0.7rem;">{{ $item->sales_created_at->format('H:i') }} WIB</span>
                                        @else
                                            {{ \Carbon\Carbon::parse($item->tanggal_order)->format('d/m/Y') }}
                                        @endif
                                    </td>
                                    
                                    <td class="text-start">
                                        <span class="fw-bold text-slate-dark d-block">{{ $item->customer->nama_customer }}</span>
                                        <span class="text-slate-muted" style="font-size: 0.75rem;"><i class="fas fa-id-card me-1"></i>ID: {{ $item->customer->id_cust }}</span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <span class="badge badge-secondary-soft px-2 py-1 rounded">{{ $item->user->name }}</span>
                                    </td>
                                    
                                    <td class="text-end px-4 fw-bold text-slate-dark" style="font-size: 0.95rem;">
                                        Rp {{ number_format($item->total_semua, 0, ',', '.') }}
                                    </td>
                                    
                                    <td class="text-center px-2">
                                        @php 
                                            $skor = $item->skor_spk ?? 0;
                                            $warna = ($skor >= 70) ? 'fill-safe' : (($skor >= 40) ? 'fill-warn' : 'fill-risk');
                                        @endphp
                                        <div class="progress-wrapper shadow-sm">
                                            <div class="progress-fill {{ $warna }}" style="width: {{ $skor }}%;">
                                                @if($skor >= 30) {{ number_format($skor, 0) }}% @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        @if($item->status_approval == 'pending')
                                            <span class="badge badge-warning-soft px-3 py-1.5 rounded-pill fw-bold">PENDING</span>
                                        @elseif($item->status_approval == 'disetujui')
                                            <span class="badge badge-success-soft px-3 py-1.5 rounded-pill fw-bold">DISETUJUI</span>
                                        @else
                                            <span class="badge badge-danger-soft px-3 py-1.5 rounded-pill fw-bold">DITOLAK</span>
                                        @endif
                                    </td>
                                    
                                    <td class="text-center px-3">
                                        @if($item->status_approval == 'pending')
                                            <button type="button" class="btn btn-sm btn-emerald-custom px-3 shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalApprove{{ $item->id }}">
                                                <i class="fas fa-search me-1"></i> Rincian
                                            </button>

                                            {{-- MODAL MINIMALIS --}}
                                            <div class="modal fade" id="modalApprove{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-centered text-start">
                                                    <form action="{{ url('/penjualan/approve/'.$item->id) }}" method="POST" class="w-100">
                                                        @csrf
                                                        <div class="modal-content premium-modal border-0 shadow-lg">
                                                            
                                                            <div class="modal-header bg-emerald-soft py-2">
                                                                <h6 class="modal-title fw-bold text-slate-dark mb-0 fs-6">
                                                                    <i class="fas fa-file-signature text-emerald-custom me-2"></i> Approval: {{ $item->no_so }}
                                                                </h6>
                                                                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal"></button>
                                                            </div>

                                                            <div class="modal-body modal-compact-body bg-slate-50">
                                                                
                                                                @php
                                                                    $tier = strtolower($item->customer->tingkat_customer ?? 'reguler');
                                                                    $plafon = $item->customer->plafon ?? 0;
                                                                    $piutang = $item->customer->piutang_berjalan ?? 0;
                                                                    $sisa_limit = $item->customer->sisa_plafon ?? 0;
                                                                @endphp
                                                                
                                                                {{-- INFO CUSTOMER COMPACT TERINTEGRASI PLAFON DENGAN KARTU METRIK --}}
                                                                <div class="mb-3 bg-white p-3 rounded border shadow-sm">
                                                                    <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                                                                        <div class="bg-emerald-soft rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                                            <i class="fas fa-store text-emerald-custom fs-5"></i>
                                                                        </div>
                                                                        <div class="flex-grow-1">
                                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                                <span class="fw-bold text-slate-dark fs-6">{{ $item->customer->nama_customer }}</span>
                                                                                @if($tier == 'gold')
                                                                                    <span class="badge bg-warning text-dark border border-warning shadow-sm" style="font-size: 0.6rem;"><i class="fas fa-crown text-danger"></i> GOLD</span>
                                                                                @elseif($tier == 'silver')
                                                                                    <span class="badge bg-light text-dark border shadow-sm" style="font-size: 0.6rem;"><i class="fas fa-award text-secondary"></i> SILVER</span>
                                                                                @elseif($tier == 'bronze')
                                                                                    <span class="badge badge-bronze shadow-sm" style="font-size: 0.6rem;"><i class="fas fa-medal"></i> BRONZE</span>
                                                                                @else
                                                                                    <span class="badge bg-secondary text-white shadow-sm" style="font-size: 0.6rem;"><i class="fas fa-user"></i> REGULER</span>
                                                                                @endif
                                                                            </div>
                                                                            <div class="d-flex gap-3 small text-slate-muted" style="font-size: 0.7rem;">
                                                                                <span><i class="fas fa-user-tag me-1"></i>Sales: {{ $item->user->name }}</span>
                                                                                <span><i class="fas fa-calendar-alt me-1"></i>Order: {{ \Carbon\Carbon::parse($item->tanggal_order)->format('d/m/Y') }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    {{-- METRIK PLAFON (3 KOLOM) --}}
                                                                    <div class="d-flex justify-content-between align-items-center bg-light p-2 rounded">
                                                                        <div class="text-center w-100 px-2 border-end">
                                                                            <span class="d-block small text-slate-muted mb-1" style="font-size: 0.65rem;">Plafon Maksimal</span>
                                                                            <span class="fw-bold text-slate-dark" style="font-size: 0.8rem;">Rp {{ number_format($plafon, 0, ',', '.') }}</span>
                                                                        </div>
                                                                        <div class="text-center w-100 px-2 border-end">
                                                                            <span class="d-block small text-slate-muted mb-1" style="font-size: 0.65rem;">Hutang Berjalan</span>
                                                                            <span class="fw-bold text-warning" style="font-size: 0.8rem;">Rp {{ number_format($piutang, 0, ',', '.') }}</span>
                                                                        </div>
                                                                        <div class="text-center w-100 px-2">
                                                                            <span class="d-block small text-slate-muted mb-1" style="font-size: 0.65rem;">Sisa Limit Tersedia</span>
                                                                            <span class="fw-bold fs-6 {{ $sisa_limit < $item->total_semua ? 'text-danger' : 'text-emerald-custom' }}">
                                                                                Rp {{ number_format($sisa_limit, 0, ',', '.') }}
                                                                            </span>
                                                                        </div>
                                                                    </div>

                                                                    {{-- WARNING JIKA ORDER MELEBIHI SISA LIMIT --}}
                                                                    @if($sisa_limit < $item->total_semua)
                                                                        <div class="plafon-warning-box">
                                                                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                                                                            <span>Perhatian: Nilai order (Rp {{ number_format($item->total_semua, 0, ',', '.') }}) melebihi sisa limit kredit pelanggan. Disarankan ditolak atau bayar lunas/DP.</span>
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                {{-- TABEL RINCIAN COMPACT --}}
                                                                <div class="bg-white border rounded shadow-sm overflow-hidden mb-3">
                                                                    <div class="bg-light p-2 border-bottom fw-bold text-slate-dark" style="font-size: 0.75rem;">
                                                                        <i class="fas fa-box-open text-emerald-custom me-1"></i> Rincian & Opsi Harga
                                                                    </div>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-compact table-hover mb-0 align-middle border-0">
                                                                            <thead class="bg-white text-slate-muted border-bottom text-uppercase" style="font-size: 0.7rem;">
                                                                                <tr>
                                                                                    <th class="ps-2">Barang</th>
                                                                                    <th class="text-center" width="80px">Qty</th>
                                                                                    <th class="text-end" width="180px">Harga (Rp)</th>
                                                                                    <th class="text-end pe-2" width="120px">Subtotal</th>
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach($item->details as $detail)
                                                                                @php 
                                                                                    $hargaNormal = $detail->barang->harga_jual ?? 0;
                                                                                    $hargaBronze = $hargaNormal - ($hargaNormal * 0.02);
                                                                                    $hargaSilver = $hargaNormal - ($hargaNormal * 0.05);
                                                                                    $hargaGold   = $hargaNormal - ($hargaNormal * 0.10);
                                                                                    $stok_real   = $detail->barang->stok_akhir ?? 0;
                                                                                @endphp
                                                                                <tr>
                                                                                    <td class="ps-2 border-bottom-0">
                                                                                        <div class="fw-bold text-slate-dark text-truncate" style="max-width: 200px;">{{ $detail->barang->nama_barang }}</div>
                                                                                        <div class="text-slate-muted" style="font-size: 0.65rem;">
                                                                                            Stok: <span class="{{ $stok_real < $detail->jumlah ? 'text-danger fw-bold' : 'text-emerald-custom' }}">{{ $stok_real }}</span>
                                                                                        </div>
                                                                                    </td>
                                                                                    
                                                                                    <td class="text-center border-bottom-0">
                                                                                        <input type="number" name="jumlah_edit[{{ $detail->id }}]" 
                                                                                               id="input-qty-{{ $item->id }}-{{ $detail->id }}"
                                                                                               class="form-control text-center fw-bold input-qty-edit input-qty-{{ $item->id }}" 
                                                                                               value="{{ $detail->jumlah }}" 
                                                                                               data-detail-id="{{ $detail->id }}" min="0" required>
                                                                                        @if($stok_real < $detail->jumlah)
                                                                                            <div class="text-danger mt-1" style="font-size: 0.6rem;"><i class="fas fa-exclamation-triangle"></i> Kurang</div>
                                                                                        @endif
                                                                                    </td>
                                                                                    
                                                                                    <td class="text-end border-bottom-0">
                                                                                        <input type="number" name="harga_edit[{{ $detail->id }}]" 
                                                                                               id="input-harga-{{ $item->id }}-{{ $detail->id }}"
                                                                                               class="form-control text-end fw-bold input-harga-edit input-harga-{{ $item->id }} mb-1" 
                                                                                               value="{{ $detail->harga_jual ?? $detail->harga_satuan }}" 
                                                                                               data-detail-id="{{ $detail->id }}" min="0" required>
                                                                                        
                                                                                        <div class="d-flex flex-wrap justify-content-end gap-1">
                                                                                            <span class="badge bg-secondary border-0 btn-apply-harga {{ $tier == 'reguler' ? 'border border-dark shadow-sm' : '' }}" data-target="input-harga-{{ $item->id }}-{{ $detail->id }}" data-harga="{{ $hargaNormal }}" title="Harga Reguler">R: {{ number_format($hargaNormal, 0, ',', '.') }}</span>
                                                                                            <span class="badge badge-bronze border-0 btn-apply-harga {{ $tier == 'bronze' ? 'border border-dark shadow-sm' : '' }}" data-target="input-harga-{{ $item->id }}-{{ $detail->id }}" data-harga="{{ $hargaBronze }}" title="Harga Bronze">B: {{ number_format($hargaBronze, 0, ',', '.') }}</span>
                                                                                            <span class="badge bg-light text-dark border btn-apply-harga {{ $tier == 'silver' ? 'border-dark shadow-sm bg-white' : '' }}" data-target="input-harga-{{ $item->id }}-{{ $detail->id }}" data-harga="{{ $hargaSilver }}" title="Harga Silver">S: {{ number_format($hargaSilver, 0, ',', '.') }}</span>
                                                                                            <span class="badge bg-warning text-dark border-0 btn-apply-harga {{ $tier == 'gold' ? 'border border-dark shadow-sm' : '' }}" data-target="input-harga-{{ $item->id }}-{{ $detail->id }}" data-harga="{{ $hargaGold }}" title="Harga Gold">G: {{ number_format($hargaGold, 0, ',', '.') }}</span>
                                                                                        </div>
                                                                                    </td>
                                                                                    
                                                                                    <td class="text-end pe-2 fw-bold text-slate-dark border-bottom-0 subtotal-text-{{ $item->id }}-{{ $detail->id }}">
                                                                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                                                                    </td>
                                                                                </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                            <tfoot class="bg-light border-top">
                                                                                <tr>
                                                                                    <td colspan="3" class="text-end fw-bold px-2 py-2 text-slate-muted" style="font-size: 0.75rem;">GRAND TOTAL ORDER INI:</td>
                                                                                    <td class="text-end pe-2 py-2 fw-extrabold text-emerald-custom fs-6" id="grand-total-{{ $item->id }}">
                                                                                        Rp {{ number_format($item->total_semua, 0, ',', '.') }}
                                                                                    </td>
                                                                                </tr>
                                                                            </tfoot>
                                                                        </table>
                                                                    </div>
                                                                </div>

                                                                {{-- CATATAN EKSEKUTIF COMPACT --}}
                                                                <div>
                                                                    <label class="form-label fw-bold text-slate-dark mb-1" style="font-size: 0.75rem;"><i class="fas fa-pen-alt text-emerald-custom me-1"></i> Catatan Persetujuan (Opsional)</label>
                                                                    <textarea name="catatan" class="form-control bg-white shadow-sm border" rows="1" placeholder="Instruksi ACC / Penolakan..." style="font-size: 0.8rem;"></textarea>
                                                                </div>

                                                            </div>

                                                            {{-- FOOTER MODAL --}}
                                                            <div class="modal-footer bg-white border-top py-2 d-flex justify-content-between">
                                                                <div class="small text-slate-muted ms-2" style="font-size: 0.7rem;"><i class="fas fa-info-circle me-1"></i> Sisa Limit otomatis dihitung dari total hutang berjalan.</div>
                                                                <div>
                                                                    <button type="submit" name="status" value="ditolak" class="btn btn-sm btn-outline-danger fw-bold px-3 rounded-pill shadow-sm me-1">Tolak</button>
                                                                    <button type="submit" name="status" value="disetujui" class="btn btn-sm btn-emerald-custom fw-bold px-3 rounded-pill shadow-sm"><i class="fas fa-check me-1"></i> Setujui</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center p-2 rounded bg-light border" style="line-height: 1.2;">
                                                <span class="text-slate-muted" style="font-size: 0.65rem;">Diproses oleh:</span><br>
                                                <strong class="text-slate-dark" style="font-size: 0.75rem;">{{ $item->approver->name ?? 'Direktur' }}</strong>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-slate-muted bg-white">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-inbox fa-3x mb-3 text-muted opacity-25"></i>
                                            <span>Tidak ada dokumen Sales Order yang perlu direview saat ini.</span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // FUNGSI UMUM UNTUK MENGHITUNG ULANG SUBTOTAL & GRAND TOTAL DI DALAM SATU MODAL
    function hitungUlangModal(modalId) {
        let grandTotal = 0;
        const semuaHarga = document.querySelectorAll(`.input-harga-${modalId}`);
        
        semuaHarga.forEach(function(inputHarga) {
            const detailId = inputHarga.dataset.detailId;
            const inputQty = document.getElementById(`input-qty-${modalId}-${detailId}`);
            const qty = parseFloat(inputQty.value) || 0;
            const harga = parseFloat(inputHarga.value) || 0;
            const subtotal = qty * harga;
            
            const subtotalEl = document.querySelector(`.subtotal-text-${modalId}-${detailId}`);
            if(subtotalEl) { subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID'); }
            
            grandTotal += subtotal;
        });
        
        const grandTotalEl = document.getElementById(`grand-total-${modalId}`);
        if(grandTotalEl) { grandTotalEl.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID'); }
    }

    const inputHargas = document.querySelectorAll('input[class*="input-harga-edit"]');
    inputHargas.forEach(function(input) {
        input.addEventListener('input', function() {
            const modalIdMatch = this.className.match(/input-harga-(\d+)/);
            if (modalIdMatch) hitungUlangModal(modalIdMatch[1]);
        });
    });

    const inputQtys = document.querySelectorAll('input[class*="input-qty-edit"]');
    inputQtys.forEach(function(input) {
        input.addEventListener('input', function() {
            const modalIdMatch = this.className.match(/input-qty-(\d+)/);
            if (modalIdMatch) hitungUlangModal(modalIdMatch[1]);
        });
    });

    const btnApplyHargas = document.querySelectorAll('.btn-apply-harga');
    btnApplyHargas.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const hargaTier = this.dataset.harga;
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                targetInput.value = hargaTier;
                targetInput.dispatchEvent(new Event('input'));
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl) })
});
</script>
@endsection