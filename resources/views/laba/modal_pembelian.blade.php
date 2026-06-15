{{-- MODAL DETAIL PEMBELIAN (ARUS KAS KELUAR) --}}
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
            <div class="modal-header bg-light border-bottom-0 pb-0">
                <h6 class="modal-title fw-bold text-slate-dark"><i class="fas fa-money-bill-wave text-danger me-2"></i>{{ $modal_title }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                @php
                    $pengeluaran_awal = $item->pengeluaran_awal;
                    $klaim_dn = $item->potongan_cn;
                    $pengeluaran_akhir = $pengeluaran_awal - $klaim_dn;
                    
                    $nilai_asli_retur = $item->nilai_retur_asli ?? 0;
                    $kerugian_retur = $nilai_asli_retur - $klaim_dn;
                @endphp

                {{-- BAGIAN 1: PENGELUARAN AWAL --}}
                <div class="mb-3">
                    <div class="fw-bold text-slate-dark small mb-2 text-uppercase"><i class="fas fa-shopping-cart me-1"></i> 1. Rincian Belanja Awal</div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-slate-muted small">Total Barang Dipesan</span>
                        <span class="fw-medium text-slate-dark small">{{ $item->qty_awal }} pcs</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-slate-muted small">Total Tagihan Awal (HPP)</span>
                        <span class="fw-bold text-danger small">- Rp {{ number_format($pengeluaran_awal, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- BAGIAN 2: JIKA ADA BARANG DIREKLAIM/RETUR --}}
                @if($item->qty_retur > 0)
                <div class="p-3 bg-light rounded mb-3 border border-warning border-opacity-25">
                    <div class="fw-bold text-warning-emphasis small mb-2"><i class="fas fa-undo me-1"></i> 2. Proses Klaim Retur ({{ $item->qty_retur }} Barang)</div>
                    
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-slate-muted" style="font-size: 0.75rem;">Nilai Modal Barang yang Diretur</span>
                        <span class="fw-medium text-slate-dark" style="font-size: 0.75rem;">Rp {{ number_format($nilai_asli_retur, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-1 pb-1 border-bottom border-warning border-opacity-25">
                        <span class="text-slate-muted" style="font-size: 0.75rem;">Uang Diganti Supplier (Debit Note)</span>
                        <span class="fw-medium text-emerald" style="font-size: 0.75rem;">+ Rp {{ number_format($klaim_dn, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-2 pt-1">
                        <span class="fw-bold text-slate-dark" style="font-size: 0.75rem;">Kerugian karena Klaim Tidak Penuh</span>
                        <span class="fw-bold text-danger" style="font-size: 0.75rem;">- Rp {{ number_format(max(0, $kerugian_retur), 0, ',', '.') }}</span>
                    </div>
                </div>
                @endif

                {{-- BAGIAN 3: TOTAL KESELURUHAN --}}
                <div class="d-flex justify-content-between align-items-center bg-danger bg-opacity-10 p-3 rounded border border-danger border-opacity-25 mt-2">
                    <span class="fw-bold text-danger text-uppercase" style="font-size: 0.8rem;">Pengeluaran Bersih</span>
                    <h5 class="fw-bold text-danger mb-0">- Rp {{ number_format($pengeluaran_akhir, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>
