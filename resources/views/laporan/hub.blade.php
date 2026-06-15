@extends('layouts.app')

@section('content')
<style>
    /* Sinkronisasi Tema Mentari Atlas */
    .card-custom { border-radius: 1rem; border: none; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1); background-color: #ffffff; }
    .bg-emerald-custom { background-color: #10b981 !important; color: #ffffff; }
    .text-slate-dark { color: #0f172a; }
    .text-slate-muted { color: #64748b; }
    
    .form-label-custom { font-weight: 800; color: #0f172a; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
    
    .form-select-custom, .form-control-custom { 
        border-radius: 0.5rem; border: 1px solid #cbd5e1; box-shadow: none; 
        transition: border-color 0.2s; font-size: 0.95rem; padding: 0.6rem 1rem;
    }
    .form-select-custom:focus, .form-control-custom:focus { 
        border-color: #10b981; box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25); 
    }
    
    .group-box { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 0.5rem; padding: 1.25rem; }
    .custom-control-label { cursor: pointer; font-weight: 500; color: #334155; font-size: 0.9rem; margin-top: 0.1rem;}
    
    .btn-emerald-custom { background-color: #10b981; color: white; border: none; transition: all 0.2s; }
    .btn-emerald-custom:hover { background-color: #059669; color: white; transform: translateY(-1px); }
</style>

{{-- PERUBAHAN DI SINI: Mengganti py-4 menjadi pt-2 pb-5 agar lebih naik ke atas mendekati navbar --}}
<div class="container-fluid pt-2 pb-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card card-custom overflow-hidden">
                {{-- Header Hijau Mentari Atlas --}}
                <div class="card-header bg-emerald-custom p-4" style="border-bottom: none;">
                    <h5 class="mb-0 fw-bold fs-4"><i class="fas fa-file-invoice-dollar me-2"></i> Parameter Cetak Laporan</h5>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('laporan.generate') }}" method="POST" target="_blank">
                        @csrf
                        
                        {{-- 1. PILIH TIPE LAPORAN --}}
                        <div class="mb-4">
                            <label class="form-label-custom">Kategori Data Laporan</label>
                            <select name="kategori_laporan" id="kategori_laporan" class="form-select form-select-custom" required>
                                <option value="" disabled selected>-- Pilih Kategori Laporan --</option>
                                <option value="penjualan">1. Sales Order (Penjualan)</option>
                                <option value="pembelian">2. Purchase Order (Pembelian)</option>
                                <option value="piutang">3. Keuangan (Piutang Customer)</option>
                                <option value="utang">4. Keuangan (Utang Supplier)</option>
                                <option value="cn">5. Credit Note (Potongan Penjualan)</option>
                                <option value="dn">6. Debit Note (Potongan Pembelian)</option>
                                <option value="retur_jual">7. Riwayat Retur Penjualan</option>
                                <option value="retur_beli">8. Riwayat Retur Pembelian</option>
                                <option value="backorder">9. Backorder (Tunggu Stok)</option>
                            </select>
                        </div>

                        {{-- 2. RENTANG WAKTU --}}
                        <div class="mb-4">
                            <label class="form-label-custom">Pilih Rentang Waktu</label>
                            <select name="periode" id="periode" class="form-select form-select-custom mb-3">
                                <option value="custom">Per Periode Tanggal (Custom)</option>
                                <option value="bulan_ini">Bulan Ini</option>
                                <option value="tahun_ini">Tahun Ini</option>
                                <option value="semua">Semua Transaksi (Total)</option>
                            </select>

                            <div class="row g-2 d-none" id="custom_date_wrapper">
                                <div class="col-6">
                                    <label class="text-slate-muted fw-bold small mb-1">Tanggal Mulai</label>
                                    <input type="date" name="start_date" class="form-control form-control-custom">
                                </div>
                                <div class="col-6">
                                    <label class="text-slate-muted fw-bold small mb-1">Tanggal Selesai</label>
                                    <input type="date" name="end_date" class="form-control form-control-custom">
                                </div>
                            </div>
                        </div>

                        {{-- 3. FILTER GROUPING (DINAMIS) --}}
                        <div class="mb-4 d-none" id="grouping_section">
                            <label class="form-label-custom text-primary">Parameter Kelompok (Opsional)</label>
                            <div class="group-box" id="grouping_options">
                                <!-- Checkbox akan di-generate oleh JavaScript di bawah -->
                            </div>
                            <div class="form-text small mt-2"><i class="fas fa-info-circle text-info me-1"></i> Centang filter di atas untuk memecah laporan per kelompok.</div>
                        </div>

                        {{-- 4. FORMAT EXPORT --}}
                        <div class="mb-5">
                            <label class="form-label-custom">Format Dokumen</label>
                            <div class="d-flex gap-4 mt-2">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input" type="radio" name="format_export" id="format_excel" value="excel" checked style="transform: scale(1.3);">
                                    <label class="form-check-label fw-bold text-slate-dark ms-2" role="button" for="format_excel">
                                        <i class="fas fa-file-excel text-success me-1 fs-5 align-middle"></i> Excel (.xls)
                                    </label>
                                </div>
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input" type="radio" name="format_export" id="format_pdf" value="pdf" style="transform: scale(1.3);">
                                    <label class="form-check-label fw-bold text-slate-dark ms-2" role="button" for="format_pdf">
                                        <i class="fas fa-file-pdf text-danger me-1 fs-5 align-middle"></i> PDF (.pdf)
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Aksi Bawah --}}
                        <div class="d-flex justify-content-end border-top pt-4">
                            <a href="{{ url()->previous() }}" class="btn btn-light rounded-pill px-4 fw-bold me-2">Batal</a>
                            <button type="submit" class="btn btn-emerald-custom rounded-pill px-4 fw-bold shadow-sm">
                                <i class="fas fa-print me-2"></i> Proses Ekspor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectKategori = document.getElementById('kategori_laporan');
        const selectPeriode = document.getElementById('periode');

        // Fungsi Memunculkan Rentang Waktu Custom
        function toggleCustomDate() {
            var wrapper = document.getElementById('custom_date_wrapper');
            if (selectPeriode.value === 'custom') {
                wrapper.classList.remove('d-none');
            } else {
                wrapper.classList.add('d-none');
            }
        }

        // Fungsi Ajaib Memunculkan Form ke-3 (Grouping)
        function updateGroupingOptions() {
            var kategori = selectKategori.value;
            var section = document.getElementById('grouping_section');
            var container = document.getElementById('grouping_options');
            
            container.innerHTML = ''; 
            var options = [];

            if(kategori === 'penjualan') {
                options = [
                    {val: 'salesman', label: 'Per Salesman (Staf)'},
                    {val: 'merek', label: 'Per Merek / Brand'},
                    {val: 'customer', label: 'Per Customer'}
                ];
            } else if(kategori === 'pembelian' || kategori === 'utang' || kategori === 'dn') {
                options = [
                    {val: 'supplier', label: 'Per Supplier'},
                    {val: 'merek', label: 'Per Merek Barang'}
                ];
            } else if(kategori === 'piutang' || kategori === 'cn') {
                options = [
                    {val: 'customer', label: 'Per Customer'}
                ];
            } else if(kategori === 'retur_jual') {
                options = [
                    {val: 'customer', label: 'Per Customer'},
                    {val: 'merek', label: 'Per Merek Barang'}
                ];
            } else if(kategori === 'retur_beli') {
                options = [
                    {val: 'supplier', label: 'Per Supplier'}
                ];
            } else if(kategori === 'backorder') {
                options = [
                    {val: 'customer', label: 'Per Customer (Prioritas Kirim)'},
                    {val: 'barang', label: 'Per Barang (Prioritas Restock)'}
                ];
            }

            if (options.length > 0) {
                section.classList.remove('d-none'); // Munculkan Form
                options.forEach(function(opt) {
                    var html = `
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="group_by[]" id="chk_${opt.val}" value="${opt.val}" style="transform: scale(1.15);">
                        <label class="form-check-label custom-control-label ms-1" for="chk_${opt.val}">${opt.label}</label>
                    </div>`;
                    container.innerHTML += html;
                });
            } else {
                section.classList.add('d-none'); // Sembunyikan Form
            }
        }

        // Jalankan trigger saat opsi diubah
        selectKategori.addEventListener('change', updateGroupingOptions);
        selectPeriode.addEventListener('change', toggleCustomDate);

        // Jalankan sekali saat halaman baru di-load (untuk mencegah bug back-button browser)
        updateGroupingOptions();
        toggleCustomDate();
    });
</script>
@endsection