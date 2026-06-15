{{-- MODAL DETAIL LABA (REUSABLE) --}}
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header bg-light border-bottom-0 pb-0">
                <h6 class="modal-title fw-bold text-slate-dark"><i class="fas fa-calculator text-info me-2"></i>{{ $modal_title }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                @php
                    $omzet_akhir = $item->omzet_awal - $item->omzet_retur;
                    $hpp_retur = $item->hpp_awal - $item->total_hpp;
                    $hpp_akhir = $item->hpp_awal - $hpp_retur;
                    $laba_penjualan = $omzet_akhir - $hpp_akhir;
                    
                    $cuan_penalti = $item->omzet_retur - $item->potongan_cn;
                @endphp

                {{-- BAGIAN 1: LABA PENJUALAN MURNI (BARANG YANG JADI TERJUAL) --}}
                <div class="mb-3">
                    <div class="fw-bold text-emerald small mb-2 text-uppercase"><i class="fas fa-shopping-cart me-1"></i> 1. Laba dari Barang Terjual</div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-slate-muted small">Total Omzet Final</span>
                        <span class="fw-medium text-slate-dark small">Rp {{ number_format($omzet_akhir, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-slate-muted small">Total Modal (HPP) Final</span>
                        <span class="fw-medium text-danger small">- Rp {{ number_format($hpp_akhir, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold text-slate-dark small">Laba Kotor Penjualan</span>
                        <span class="fw-bold text-slate-dark small">Rp {{ number_format($laba_penjualan, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- BAGIAN 2: JIKA ADA BARANG RETUR (PENALTI) --}}
                @if($item->qty_retur > 0)
                <div class="p-3 bg-light rounded mb-3 border border-warning border-opacity-25">
                    <div class="fw-bold text-warning-emphasis small mb-2"><i class="fas fa-undo me-1"></i> 2. Keuntungan dari Penalti Retur ({{ $item->qty_retur }} Barang)</div>
                    
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-slate-muted" style="font-size: 0.75rem;">Nilai Jual Asli Barang Batal</span>
                        <span class="fw-medium text-slate-dark" style="font-size: 0.75rem;">Rp {{ number_format($item->omzet_retur, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-1 pb-1 border-bottom border-warning border-opacity-25">
                        <span class="text-slate-muted" style="font-size: 0.75rem;">Uang Dikembalikan ke Customer (CN)</span>
                        <span class="fw-medium text-danger" style="font-size: 0.75rem;">- Rp {{ number_format($item->potongan_cn, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-2 pt-1">
                        <span class="fw-bold text-slate-dark" style="font-size: 0.75rem;">Cuan dari Penalti Retur (Charge)</span>
                        <span class="fw-bold text-emerald" style="font-size: 0.75rem;">+ Rp {{ number_format($cuan_penalti, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif

                {{-- BAGIAN 3: TOTAL KESELURUHAN --}}
                <div class="d-flex justify-content-between align-items-center bg-emerald bg-opacity-10 p-3 rounded border border-success border-opacity-25 mt-2">
                    <span class="fw-bold text-success text-uppercase" style="font-size: 0.8rem;">Total Laba Bersih</span>
                    <h5 class="fw-bold text-success mb-0">Rp {{ number_format($item->total_laba, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>
