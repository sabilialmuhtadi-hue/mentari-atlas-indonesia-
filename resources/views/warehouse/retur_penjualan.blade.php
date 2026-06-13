@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* Global Overrides untuk Tema Premium Mentari Atlas */
    body { background-color: #f8fafc !important; }
    
    /* Warna Biru Khusus Return Penjualan */
    .text-blue-custom { color: #0284c7 !important; }
    .bg-blue-custom { background-color: #0284c7 !important; color: #ffffff !important; }
    .btn-blue-custom { background-color: #0284c7 !important; border-color: #0284c7 !important; color: #ffffff !important; font-weight: 500; transition: all 0.2s; }
    .btn-blue-custom:hover { background-color: #0369a1 !important; color: #ffffff !important; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2); }

    .text-slate-dark { color: #0f172a !important; }
    .text-slate-muted { color: #64748b !important; }
    
    /* Card & Table Styling */
    .card-custom { border: 1px solid #e2e8f0; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    
    /* Soft Badges */
    .badge-success-soft { background-color: #d1fae5 !important; color: #065f46 !important; border: 1px solid #a7f3d0; }
    .badge-danger-soft { background-color: #fee2e2 !important; color: #991b1b !important; border: 1px solid #fecaca; }
    .badge-warning-soft { background-color: #fef3c7 !important; color: #92400e !important; border: 1px solid #fde68a; }
    .badge-secondary-soft { background-color: #f1f5f9 !important; color: #475569 !important; border: 1px solid #cbd5e1; }
    .badge-info-soft { background-color: #e0f2fe !important; color: #0369a1 !important; border: 1px solid #bae6fd; }

    /* Custom Styling untuk Select2 agar serasi dengan Bootstrap 5 */
    .select2-container .select2-selection--single { height: 38px !important; border: 1px solid #dee2e6 !important; border-radius: 0.375rem !important; display: flex; align-items: center; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: #0f172a !important; line-height: normal !important; padding-left: 0.75rem !important; width: 100%; overflow: hidden; text-overflow: ellipsis; }
    .select2-search__field { border-radius: 0.25rem !important; }
</style>

<div class="container-fluid py-4" style="background-color: #f8fafc; min-height: 80vh;">
    
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-slate-dark fw-bold"><i class="fas fa-undo text-blue-custom me-2"></i>Return Penjualan & Credit Note</h1>
            <p class="text-slate-muted small mb-0 mt-1">Kelola pengembalian fisik produk atau klaim potongan harga (Credit Note) dari customer secara terintegrasi.</p>
        </div>
        <button class="btn btn-blue-custom shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahReturJual">
            <i class="fas fa-plus me-1"></i> Catat Klaim / Return Baru
        </button>
    </div>

    {{-- ALERT NOTIFIKASI --}}
    @if(session('success'))
        <div class="alert badge-success-soft alert-dismissible fade show border-0 shadow-sm rounded-3 px-4 py-3 mb-4" role="alert">
            <i class="fas fa-check-circle text-success me-2"></i><strong>Berhasil!</strong> {{ session('success') }}
            <button type="button" class="btn-close mt-1" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert badge-danger-soft alert-dismissible fade show border-0 shadow-sm rounded-3 px-4 py-3 mb-4" role="alert">
            <i class="fas fa-exclamation-triangle text-danger me-2"></i><strong>Gagal!</strong> {{ $errors->first() }}
            <button type="button" class="btn-close mt-1" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- TABEL RIWAYAT KLAIM --}}
    <h6 class="fw-bold text-slate-dark mb-3"><i class="fas fa-history me-2 text-blue-custom"></i>Riwayat Klaim Penjualan & Penyesuaian Saldo</h6>
    <div class="table-wrapper-mentari">
        <div class="table-responsive">
            <table class="table table-mentari-blue align-middle mb-0" style="font-size: 0.85rem; width: 100%;">
                <thead>
                    <tr>
                        <th class="ps-4">No. Klaim / Return</th>
                        <th>Nota SO</th>
                        <th>Nama Barang</th>
                        <th class="text-center">Qty</th>
                        <th>Jenis Klaim</th>
                        <th>Dampak Stok Utama</th>
                        <th>Potongan Piutang (CN)</th>
                        <th>Alasan</th>
                        <th class="text-center pe-4">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returs as $retur)
                    <tr>
                        <td class="ps-4 fw-bold text-blue-custom">{{ $retur->no_retur_jual }}</td>
                        <td><span class="badge badge-secondary-soft rounded-pill px-2 py-1 shadow-sm">{{ $retur->penjualan->no_so ?? 'N/A' }}</span></td>
                        <td class="fw-bold text-slate-dark">{{ $retur->barang->nama_barang ?? 'N/A' }}</td>
                        <td class="text-center fw-bold">{{ $retur->qty_retur }}</td>
                        <td>
                            @if($retur->jenis_retur == 'fisik')
                                <span class="badge badge-secondary-soft rounded-pill px-2 py-1"><i class="fas fa-box me-1"></i> Fisik Barang</span>
                            @else
                                <span class="badge badge-info-soft rounded-pill px-2 py-1"><i class="fas fa-tags me-1"></i> Koreksi Harga</span>
                            @endif
                        </td>
                        <td>
                            @if($retur->jenis_retur == 'fisik')
                                @if($retur->status_kondisi == 'bagus')
                                    <span class="text-success small fw-bold"><i class="fas fa-plus-circle me-1"></i>Ke Stok Bagus (+{{ $retur->qty_retur }})</span>
                                @else
                                    <span class="text-danger small fw-bold"><i class="fas fa-heart-broken me-1"></i>Ke Stok Rusak (+{{ $retur->qty_retur }})</span>
                                @endif
                            @else
                                <span class="text-slate-muted small fst-italic">Tidak Mempengaruhi Stok</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-slate-dark small fw-bold">
                                <i class="fas fa-file-invoice-dollar me-1 text-blue-custom"></i>Rp {{ number_format($retur->nominal_potongan, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-slate-muted text-truncate" style="max-width: 180px;" title="{{ $retur->alasan }}">
                            {{ $retur->alasan }}
                        </td>
                        <td class="text-center small text-slate-muted fw-bold pe-4">
                            {{ \Carbon\Carbon::parse($retur->created_at)->format('d M Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-slate-muted bg-white">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fas fa-box-open d-block fa-3x mb-3 text-blue-custom opacity-25"></i>
                                <span class="fw-bold text-slate-dark mb-1">Belum Ada Data Klaim Return</span>
                                <span class="small">Data klaim return pelanggan atau credit note akan muncul di sini.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH KLAIM RETURN --}}
<div class="modal fade" id="modalTambahReturJual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> 
        <form action="{{ route('retur.penjualan.store') }}" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
            @csrf
            <div class="modal-header bg-blue-custom text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-undo me-2"></i>Catat Return / Credit Note</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-slate-dark">Pilih Nota Penjualan (SO) *</label>
                            <select name="penjualan_id" id="penjualan_id" class="form-select border-secondary-subtle select2-search" required style="width: 100%;">
                                <option value="" disabled selected>-- Ketik untuk Mencari Nota SO --</option>
                                @foreach($penjualans as $p)
                                    <option value="{{ $p->id }}">{{ $p->no_so }} - {{ $p->customer->nama_customer ?? 'Umum' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-bold text-slate-dark">Pilih Produk Terkait *</label>
                            <select name="barang_id" id="barang_id" class="form-select border-secondary-subtle select2-search" required style="width: 100%;">
                                <option value="" disabled selected>-- Pilih SO Terlebih Dahulu --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-slate-dark">Jenis Klaim Return *</label>
                            <select name="jenis_retur" id="jenis_retur_jual" class="form-select border-secondary-subtle select2-no-search" required style="width: 100%;">
                                <option value="fisik" selected>📦 Return Fisik (Kembali Barang & Stok)</option>
                                <option value="harga_credit_note">🏷️ Credit Note (Hanya Potong Piutang)</option>
                            </select>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-slate-dark">Jumlah (Qty) *</label>
                                <div class="input-group">
                                    <input type="number" name="qty_retur" class="form-control border-secondary-subtle" min="1" placeholder="0" required>
                                    <span class="input-group-text bg-white border-secondary-subtle text-muted">Pcs</span>
                                </div>
                            </div>
                            
                            <div class="col-md-6" id="div_kondisi_jual">
                                <label class="form-label small fw-bold text-slate-dark">Kondisi Fisik *</label>
                                <select name="status_kondisi" id="status_kondisi" class="form-select border-secondary-subtle select2-no-search" style="width: 100%;">
                                    <option value="bagus" selected>Bagus (Masuk Stok Bagus)</option>
                                    <option value="rusak">Rusak (Masuk Stok Rusak)</option>
                                </select>
                            </div>

                            {{-- TAMBAHAN: DROPDOWN UMUR RETUR (AGING) MUNCUL JIKA KONDISI RUSAK --}}
                            <div class="col-md-12 d-none mt-3" id="div_aging_retur">
                                <label class="form-label small fw-bold text-danger"><i class="fas fa-clock me-1"></i>Umur Retur Barang (Aging Penalty) *</label>
                                <select name="aging_retur" id="aging_retur" class="form-select border-danger text-danger select2-no-search" style="width: 100%;">
                                    <option value="0_45" selected>0 - 45 Hari (Tanpa Denda / Charge 0%)</option>
                                    <option value="46_90">46 - 90 Hari (Kena Charge/Denda 10%)</option>
                                    <option value="91_135">91 - 135 Hari (Kena Charge/Denda 30%)</option>
                                </select>
                                <small class="text-muted mt-1 d-block" style="font-size: 11px;">Nilai Credit Note (Potong Piutang) akan dikurangi sesuai persentase denda.</small>
                            </div>

                            <div class="col-md-12 d-none" id="div_potongan_jual">
                                <label class="form-label small fw-bold text-slate-dark">Nominal Credit Note (Rp) *</label>
                                <input type="number" name="nominal_potongan" class="form-control border-secondary-subtle" placeholder="Contoh: 350000">
                                <small class="text-muted" style="font-size: 11px;">Nominal dipotong langsung dari piutang.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-bold text-slate-dark">Alasan / Deskripsi Klaim *</label>
                    <textarea name="alasan" class="form-control border-secondary-subtle shadow-sm" rows="3" placeholder="Contoh: Sparepart salah ukuran, kemasan penyok saat pengiriman, dll." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0 py-3">
                <button type="button" class="btn btn-light fw-bold shadow-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-blue-custom fw-bold shadow-sm px-4" onclick="return confirm('Konfirmasi: Proses klaim return ini? Stok dan piutang pelanggan akan disesuaikan otomatis oleh sistem.')">Simpan & Proses Return</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleReturJualJenis() {
        var jenis = $('#jenis_retur_jual').val();
        var kondisi = $('#status_kondisi').val();
        var divKondisi = document.getElementById('div_kondisi_jual');
        var divAging = document.getElementById('div_aging_retur');
        var divPotongan = document.getElementById('div_potongan_jual');
        var inputNominal = document.getElementsByName('nominal_potongan')[0];
        var selectKondisi = document.getElementById('status_kondisi');

        if (jenis === 'harga_credit_note') {
            divKondisi.classList.add('d-none');
            divAging.classList.add('d-none');
            divPotongan.classList.remove('d-none');

            inputNominal.removeAttribute('readonly');
            inputNominal.setAttribute('required', 'required');
            if(inputNominal.value === '0') inputNominal.value = ''; 

            selectKondisi.removeAttribute('required');
        } else {
            divKondisi.classList.remove('d-none');
            divPotongan.classList.add('d-none');
            
            // Logika untuk menampilkan/menyembunyikan Aging Dropdown
            if (kondisi === 'rusak') {
                divAging.classList.remove('d-none');
            } else {
                divAging.classList.add('d-none');
            }

            inputNominal.removeAttribute('required');
            inputNominal.setAttribute('readonly', 'readonly'); 
            inputNominal.value = '0'; 

            selectKondisi.setAttribute('required', 'required');
        }
    }

    $(document).ready(function() {
        // Inisialisasi Select2 dengan Search
        $('#penjualan_id').select2({
            dropdownParent: $('#modalTambahReturJual'),
            placeholder: "-- Ketik untuk Mencari Nota SO --",
            allowClear: true
        });
        
        $('#barang_id').select2({
            dropdownParent: $('#modalTambahReturJual'),
            placeholder: "-- Pilih SO Terlebih Dahulu --"
        });

        // Inisialisasi Select2 TANPA Search (Tampilan Rapi)
        $('.select2-no-search').select2({
            dropdownParent: $('#modalTambahReturJual'),
            minimumResultsForSearch: Infinity
        });

        // Listener jika Select2 Jenis Klaim atau Status Kondisi diubah
        $('#jenis_retur_jual, #status_kondisi').on('change', function() {
            toggleReturJualJenis();
        });
        
        toggleReturJualJenis(); // Jalankan saat awal dimuat

        // Event listener saat SO dipilih
        $('#penjualan_id').on('select2:select', function (e) {
            let so_id = $(this).val();
            let barangSelect = $('#barang_id');
            
            barangSelect.empty().append('<option value="" disabled selected>🔄 Memuat barang...</option>').trigger('change');

            if(so_id) {
                fetch('/get-items-so/' + so_id)
                    .then(response => response.json())
                    .then(data => {
                        barangSelect.empty(); 
                        
                        if(data.length > 0) {
                            data.forEach(item => {
                                let qty = item.jumlah_diajukan || item.qty || item.jumlah || 0;
                                let newOption = new Option(item.barang.nama_barang + ' (Terkirim: ' + qty + ' Pcs)', item.barang_id, false, false);
                                barangSelect.append(newOption);
                            });
                            
                            // Otomatis pilih barang pertama
                            barangSelect.val(data[0].barang_id).trigger('change');
                        } else {
                            barangSelect.append('<option value="" disabled selected>-- Tidak ada barang di SO ini --</option>').trigger('change');
                        }
                    })
                    .catch(error => {
                        barangSelect.empty().append('<option value="" disabled selected>❌ Gagal memuat data</option>').trigger('change');
                        console.error('Error:', error);
                    });
            }
        });
    });

    var modalTambahReturJual = document.getElementById('modalTambahReturJual');
    if (modalTambahReturJual) {
        modalTambahReturJual.addEventListener('shown.bs.modal', function () {
            toggleReturJualJenis();
        });
    }
</script>
@endsection