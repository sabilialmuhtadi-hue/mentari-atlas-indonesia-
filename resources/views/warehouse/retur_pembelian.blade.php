@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* Global Overrides untuk Tema Premium Mentari Atlas */
    body { background-color: #f8fafc !important; }
    
    /* Warna Merah Khusus Return Pembelian / Utang */
    .text-rose-custom { color: #e11d48 !important; }
    .bg-rose-custom { background-color: #e11d48 !important; color: #ffffff !important; }
    .btn-rose-custom { background-color: #e11d48 !important; border-color: #e11d48 !important; color: #ffffff !important; font-weight: 500; transition: all 0.2s; }
    .btn-rose-custom:hover { background-color: #be123c !important; color: #ffffff !important; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(225, 29, 72, 0.2); }

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
            <h1 class="h3 mb-0 text-slate-dark fw-bold"><i class="fas fa-truck-loading text-rose-custom me-2"></i>Return Pembelian & Debit Note</h1>
            <p class="text-slate-muted small mb-0 mt-1">Kelola pengembalian cacat fisik ke Supplier atau klaim potongan tagihan utang (Debit Note).</p>
        </div>
        <button class="btn btn-rose-custom shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahReturBeli">
            <i class="fas fa-plus me-1"></i> Catat Return ke Supplier
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
    <h6 class="fw-bold text-slate-dark mb-3"><i class="fas fa-history me-2 text-rose-custom"></i>Riwayat Return & Pemotongan Utang Dagang</h6>
    <div class="table-wrapper-mentari">
        <div class="table-responsive">
            {{-- MENGGUNAKAN TEMA MERAH KHUSUS UTANG/SUPPLIER --}}
            <table class="table table-mentari-red align-middle mb-0" style="font-size: 0.85rem; width: 100%;">
                <thead>
                    <tr>
                        <th class="ps-4">No. Klaim / Return</th>
                        <th>Nota PO & Supplier</th>
                        <th>Nama Barang</th>
                        <th class="text-center">Qty</th>
                        <th>Jenis Klaim</th>
                        <th>Dampak Stok Utama</th>
                        <th>Potongan Utang (DN)</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returs as $retur)
                    <tr>
                        <td class="ps-4 fw-bold text-rose-custom">{{ $retur->no_retur_beli }}</td>
                        <td>
                            <span class="badge badge-secondary-soft rounded-pill px-2 py-1 shadow-sm mb-1 d-inline-block">{{ $retur->pembelian->no_pembelian ?? 'N/A' }}</span><br>
                            <span class="small fw-semibold text-slate-dark"><i class="fas fa-building me-1 text-slate-muted"></i> {{ $retur->nama_supplier ?? 'N/A' }}</span>
                        </td>
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
                                @if($retur->status_retur === 'pending')
                                    <span class="text-warning small fw-bold"><i class="fas fa-pause-circle me-1"></i>Menunggu Eksekusi</span><br>
                                    <span class="text-slate-muted" style="font-size: 10px;">(Akan memotong Stok: {{ ucfirst($retur->status_kondisi) }})</span>
                                @else
                                    <span class="text-danger small fw-bold"><i class="fas fa-minus-circle me-1"></i>Keluar Gudang (-{{ $retur->qty_retur }})</span><br>
                                    <span class="text-slate-muted" style="font-size: 10px;">(Dari Stok: {{ ucfirst($retur->status_kondisi) }})</span>
                                @endif
                            @else
                                <span class="text-slate-muted small fst-italic">Tidak Mempengaruhi Stok</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-slate-dark small fw-bold">
                                <i class="fas fa-file-invoice-dollar me-1 text-rose-custom"></i>Rp {{ number_format($retur->nominal_potongan, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($retur->status_retur === 'pending')
                                <span class="badge badge-warning-soft px-2 py-1 rounded-pill"><i class="fas fa-clock me-1"></i>Tertunda</span>
                            @else
                                <span class="badge badge-success-soft px-2 py-1 rounded-pill"><i class="fas fa-check-circle me-1"></i>Selesai</span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            @if($retur->status_retur === 'pending')
                                <form action="{{ route('retur.pembelian.eksekusi', $retur->id) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-warning fw-bold text-dark shadow-sm rounded-pill" onclick="return confirm('Eksekusi Return ini? Sistem akan memotong Stok Karantina/Rusak dan menagihkan Debit Note secara otomatis ke tagihan Supplier.')">
                                        <i class="fas fa-bolt me-1"></i> Return Sekarang
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-sm btn-light border fw-bold text-secondary shadow-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#modalDetailRetur{{ $retur->id }}">
                                    <i class="fas fa-eye me-1"></i> Lihat Detail
                                </button>
                            @endif
                        </td>
                    </tr>

                    {{-- Modal Lihat Detail --}}
                    <div class="modal fade" id="modalDetailRetur{{ $retur->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg" style="border-radius: 1rem;">
                                <div class="modal-header bg-light border-0 py-3">
                                    <h6 class="modal-title fw-bold text-slate-dark"><i class="fas fa-info-circle text-rose-custom me-2"></i>Detail Klaim Return</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4 bg-white">
                                    <div class="mb-3">
                                        <label class="small text-muted fw-bold text-uppercase d-block mb-1">Nomor Klaim</label>
                                        <div class="fw-bold text-dark">{{ $retur->no_retur_beli }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small text-muted fw-bold text-uppercase d-block mb-1">Tanggal Eksekusi</label>
                                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($retur->created_at)->format('d M Y H:i:s') }}</div>
                                    </div>
                                    <div class="mb-0 p-3 bg-light rounded border border-secondary-subtle">
                                        <label class="small text-rose-custom fw-bold text-uppercase d-block mb-2"><i class="fas fa-comment-dots me-1"></i>Alasan / Keterangan</label>
                                        <div class="text-dark" style="font-size: 0.9rem; line-height: 1.5;">{{ $retur->alasan }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-slate-muted bg-white">
                            <div class="d-flex flex-column align-items-center">
                                <i class="fas fa-truck-loading d-block fa-3x mb-3 text-rose-custom opacity-25"></i>
                                <span class="fw-bold text-slate-dark mb-1">Belum Ada Data Return Supplier</span>
                                <span class="small">Data barang yang dikembalikan ke supplier akan tercatat di sini.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH KLAIM RETURN PEMBELIAN --}}
<div class="modal fade" id="modalTambahReturBeli" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> 
        <form action="{{ route('retur.pembelian.store') }}" method="POST" class="modal-content border-0 shadow-lg" style="border-radius: 1rem; overflow: hidden;">
            @csrf
            {{-- Header modal disesuaikan jadi merah --}}
            <div class="modal-header bg-rose-custom text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-undo me-2"></i>Catat Return / Debit Note ke Supplier</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-slate-dark">Pilih Nota Pembelian (PO) *</label>
                            <select name="pembelian_id" id="pembelian_id" class="form-select border-secondary-subtle select2-search" required style="width: 100%;">
                                <option value="" disabled selected>-- Ketik untuk Mencari Nota PO --</option>
                                @foreach($pembelians as $p)
                                    <option value="{{ $p->id }}">{{ $p->no_pembelian }} - {{ $p->nama_supplier }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-0">
                            <label class="form-label small fw-bold text-slate-dark">Pilih Produk Terkait *</label>
                            <select name="barang_id" id="barang_id" class="form-select border-secondary-subtle select2-search" required style="width: 100%;">
                                <option value="" disabled selected>-- Pilih PO Terlebih Dahulu --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-slate-dark">Jenis Klaim Return Supplier *</label>
                            <select name="jenis_retur" id="jenis_retur_beli" class="form-select border-secondary-subtle select2-no-search" required style="width: 100%;">
                                <option value="fisik" selected>📦 Return Fisik (Kembalikan barang dan stok)</option>
                                <option value="harga_debit_note">🏷️ Debit Note (Hanya Potong Utang)</option>
                            </select>
                        </div>

                        {{-- INPUT KONDISI FISIK (DINAMIS) --}}
                        <div class="mb-3" id="div_kondisi_fisik_beli">
                            <label class="form-label small fw-bold text-slate-dark">Kondisi Fisik *</label>
                            <select name="status_kondisi" id="status_kondisi_beli" class="form-select border-secondary-subtle select2-no-search" style="width: 100%;">
                                <option value="" disabled selected>-- Pilih Kondisi Barang --</option>
                                <option value="bagus">🟢 Bagus (Keluarkan dari Stok Bagus)</option>
                                <option value="rusak">🔴 Rusak (Keluarkan dari Stok Rusak)</option>
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

                            <div class="col-md-6 d-none" id="div_potongan_beli">
                                <label class="form-label small fw-bold text-slate-dark">Nominal Debit Note (Rp) *</label>
                                <input type="number" name="nominal_potongan" class="form-control border-secondary-subtle" placeholder="Contoh: 350000">
                                <small class="text-muted" style="font-size: 11px;">Nominal dipotong dari utang.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-bold text-slate-dark">Alasan / Deskripsi Klaim *</label>
                    <textarea name="alasan" class="form-control border-secondary-subtle shadow-sm" rows="3" placeholder="Contoh: Barang cacat pabrik, salah pengiriman varian, dll." required></textarea>
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0 py-3">
                <button type="button" class="btn btn-light fw-bold shadow-sm" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-rose-custom fw-bold shadow-sm px-4" onclick="return confirm('Konfirmasi: Proses klaim return ini? Stok dan Utang Dagang Anda akan disesuaikan otomatis oleh sistem.')">Simpan & Proses Return</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleReturBeliJenis() {
        var jenis = $('#jenis_retur_beli').val();
        
        var divPotongan = document.getElementById('div_potongan_beli');
        var inputNominal = document.getElementsByName('nominal_potongan')[0];
        
        var divKondisi = document.getElementById('div_kondisi_fisik_beli');
        var inputKondisi = $('#status_kondisi_beli');

        if (jenis === 'harga_debit_note') {
            // Tampilkan Debit Note, Sembunyikan Kondisi Fisik
            divPotongan.classList.remove('d-none');
            inputNominal.removeAttribute('readonly');
            inputNominal.setAttribute('required', 'required');
            if(inputNominal.value === '0') inputNominal.value = ''; 
            
            divKondisi.classList.add('d-none');
            inputKondisi.removeAttr('required');
            inputKondisi.val(null).trigger('change');
            
        } else if (jenis === 'fisik') {
            // Sembunyikan Debit Note, Tampilkan Kondisi Fisik
            divPotongan.classList.add('d-none');
            inputNominal.removeAttribute('required');
            inputNominal.setAttribute('readonly', 'readonly'); 
            inputNominal.value = '0'; 
            
            divKondisi.classList.remove('d-none');
            inputKondisi.attr('required', 'required');
        }
    }

    $(document).ready(function() {
        // Inisialisasi Select2 dengan Search
        $('#pembelian_id').select2({
            dropdownParent: $('#modalTambahReturBeli'),
            placeholder: "-- Ketik untuk Mencari Nota PO --",
            allowClear: true
        });
        
        $('#barang_id').select2({
            dropdownParent: $('#modalTambahReturBeli'),
            placeholder: "-- Pilih PO Terlebih Dahulu --"
        });

        // Inisialisasi Select2 TANPA Search
        $('.select2-no-search').select2({
            dropdownParent: $('#modalTambahReturBeli'),
            minimumResultsForSearch: Infinity
        });

        // Listener jika Select2 Jenis Klaim diubah
        $('#jenis_retur_beli').on('change', function() {
            toggleReturBeliJenis();
        });
        toggleReturBeliJenis(); // Panggil fungsi di awal

        // Event listener saat PO dipilih
        $('#pembelian_id').on('select2:select', function (e) {
            let po_id = $(this).val();
            let barangSelect = $('#barang_id');
            
            barangSelect.empty().append('<option value="" disabled selected>🔄 Memuat barang...</option>').trigger('change');

            if(po_id) {
                fetch('/get-items-po/' + po_id)
                    .then(response => response.json())
                    .then(data => {
                        barangSelect.empty();
                        
                        if(data.length > 0) {
                            data.forEach(item => {
                                let qty = item.jumlah_diajukan || item.qty || item.jumlah_beli || 0;
                                let newOption = new Option(item.barang.nama_barang + ' (Diterima: ' + qty + ' Pcs)', item.barang_id, false, false);
                                barangSelect.append(newOption);
                            });
                            
                            // Otomatis pilih barang pertama
                            barangSelect.val(data[0].barang_id).trigger('change');
                        } else {
                            barangSelect.append('<option value="" disabled selected>-- Tidak ada barang di PO ini --</option>').trigger('change');
                        }
                    })
                    .catch(error => {
                        barangSelect.empty().append('<option value="" disabled selected>❌ Gagal memuat data</option>').trigger('change');
                        console.error('Error:', error);
                    });
            }
        });
    });

    var modalTambahReturBeli = document.getElementById('modalTambahReturBeli');
    if (modalTambahReturBeli) {
        modalTambahReturBeli.addEventListener('shown.bs.modal', function () {
            toggleReturBeliJenis();
        });
    }
</script>
@endsection