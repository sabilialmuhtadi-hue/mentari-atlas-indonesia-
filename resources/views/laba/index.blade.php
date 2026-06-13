@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f8fafc !important; }
    .text-slate-dark { color: #0f172a !important; }
    .text-slate-muted { color: #64748b !important; }
    .card-custom { border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    
    /* Styling Nav Tabs Premium */
    .nav-tabs-custom { border-bottom: 2px solid #e2e8f0; gap: 0.5rem; }
    .nav-tabs-custom .nav-link { 
        border: none; color: #64748b; font-weight: 600; padding: 0.75rem 1.5rem; 
        border-radius: 0.5rem 0.5rem 0 0; transition: all 0.2s; position: relative;
    }
    .nav-tabs-custom .nav-link:hover { color: #10b981; background-color: #f1f5f9; }
    .nav-tabs-custom .nav-link.active { 
        color: #10b981; background-color: transparent; border: none;
    }
    .nav-tabs-custom .nav-link.active::after {
        content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; 
        height: 3px; background-color: #10b981; border-radius: 3px 3px 0 0;
    }
    
    /* Tabel Laba */
    .table-laba th { background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #e2e8f0 !important; }
    .table-laba td { padding: 1rem 0.75rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; }
    .table-laba tbody tr:hover { background-color: #f8fafc !important; }
    .text-emerald { color: #10b981 !important; }
    
    /* Box Statistik */
    .stat-box { border-radius: 1rem; padding: 1.25rem; border: 1px solid rgba(255,255,255,0.2); }
    .bg-gradient-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .bg-gradient-slate { background: linear-gradient(135deg, #334155 0%, #0f172a 100%); }

    /* Filter Form */
    .filter-group { background: white; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 0.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }

    /* ============================================================ */
    /* MODE CETAK KERTAS (PRINT MEDIA QUERY) */
    /* ============================================================ */
    @media print {
        /* 1. Sembunyikan elemen yang tidak perlu di kertas */
        .sidebar, .navbar, .btn, form, header, footer, .nav-tabs-custom, .filter-group {
            display: none !important;
        }
        
        /* 2. Bersihkan background & margin agar pas di kertas A4 */
        body { background-color: white !important; margin: 0; padding: 0; }
        .container-fluid { padding: 0 !important; width: 100% !important; max-width: 100% !important; }
        .card-custom { border: none !important; box-shadow: none !important; }
        
        /* Box statistik penyesuaian untuk print (4 Kolom) */
        .row.g-3 { display: flex !important; flex-wrap: nowrap !important; }
        .col-lg-3 { width: 25% !important; flex: 0 0 25% !important; max-width: 25% !important; padding: 0 8px !important; }
        .bg-gradient-emerald { background: #10b981 !important; color: black !important; -webkit-print-color-adjust: exact; }
        .text-white { color: black !important; }
        
        /* 3. PAKSA SEMUA TAB UNTUK TAMPIL BERSAMAAN DI KERTAS */
        .tab-content > .tab-pane {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            page-break-inside: avoid;
            margin-bottom: 2rem !important;
        }

        /* 4. Beri Judul otomatis di atas tiap tabel saat dicetak */
        #sales::before { content: "LAPORAN LABA KOTOR: PER SALES"; font-weight: bold; font-size: 1.1rem; color: #0f172a; display: block; margin-bottom: 10px; border-bottom: 2px solid #10b981; padding-bottom: 5px; }
        #produk::before { content: "LAPORAN LABA KOTOR: PER PRODUK"; font-weight: bold; font-size: 1.1rem; color: #0f172a; display: block; margin-bottom: 10px; border-bottom: 2px solid #10b981; padding-bottom: 5px; }
        #merek::before { content: "LAPORAN LABA KOTOR: PER MEREK"; font-weight: bold; font-size: 1.1rem; color: #0f172a; display: block; margin-bottom: 10px; border-bottom: 2px solid #10b981; padding-bottom: 5px; }
        #customer::before { content: "LAPORAN LABA KOTOR: PER CUSTOMER"; font-weight: bold; font-size: 1.1rem; color: #0f172a; display: block; margin-bottom: 10px; border-bottom: 2px solid #10b981; padding-bottom: 5px; }
    }
</style>

<div class="container-fluid py-4">
    
    {{-- HEADER & FORM FILTER --}}
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-0 text-slate-dark fw-bold"><i class="fas fa-chart-line text-emerald me-2"></i>Analitik Profitabilitas</h1>
            <p class="text-slate-muted small mb-0 mt-1">Laporan Laba Bersih (Net Profit) dan Status Arus Kas Perusahaan.</p>
        </div>
        
        <div class="d-flex flex-column flex-md-row gap-2 align-items-md-center">
            {{-- FORM PENCARIAN RENTANG WAKTU --}}
            <form action="{{ route('laba.index') }}" method="GET" class="filter-group d-flex flex-column flex-md-row align-items-center gap-2 m-0">
                <select name="filter_tipe" id="filter_tipe" class="form-select form-select-sm border-0 fw-bold text-slate-dark shadow-none" style="background-color: #f8fafc; cursor:pointer;" onchange="toggleCustomDate()">
                    <option value="semua" {{ $filter_tipe == 'semua' ? 'selected' : '' }}>Semua Waktu</option>
                    <option value="bulan_ini" {{ $filter_tipe == 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="tahun_ini" {{ $filter_tipe == 'tahun_ini' ? 'selected' : '' }}>Tahun Ini</option>
                    <option value="custom" {{ $filter_tipe == 'custom' ? 'selected' : '' }}>Custom Tanggal</option>
                </select>

                <div id="custom_date_wrapper" class="d-flex gap-2 align-items-center" style="display: {{ $filter_tipe == 'custom' ? 'flex' : 'none !important' }};">
                    <input type="date" name="start_date" class="form-control form-control-sm border-0" style="background-color: #f8fafc;" value="{{ $start_date }}">
                    <span class="text-muted small">s/d</span>
                    <input type="date" name="end_date" class="form-control form-control-sm border-0" style="background-color: #f8fafc;" value="{{ $end_date }}">
                </div>

                <button type="submit" class="btn btn-sm btn-emerald text-white fw-bold px-3" style="background-color: #10b981;">Filter</button>
            </form>

            <button class="btn btn-light shadow-sm fw-bold border py-2" onclick="window.print()"><i class="fas fa-print me-2 text-slate-muted"></i>Cetak</button>
        </div>
    </div>

    {{-- KOTAK STATISTIK GRAND TOTAL (4 KOLOM) --}}
    <div class="row g-3 mb-4">
        {{-- Box 1: Omzet --}}
        <div class="col-md-6 col-lg-3">
            <div class="stat-box bg-white shadow-sm h-100 border-start border-4 border-success">
                <span class="d-block text-slate-muted small fw-bold text-uppercase mb-2"><i class="fas fa-wallet me-1"></i> Total Omzet Bersih</span>
                <h4 class="fw-bold text-slate-dark mb-0">Rp {{ number_format($grandTotalPendapatan, 0, ',', '.') }}</h4>
                <small class="text-slate-muted d-block mt-1">Setelah potong retur customer</small>
            </div>
        </div>
        
        {{-- Box 2: Modal (HPP) --}}
        <div class="col-md-6 col-lg-3">
            <div class="stat-box bg-white shadow-sm h-100 border-start border-4 border-warning">
                <span class="d-block text-slate-muted small fw-bold text-uppercase mb-2"><i class="fas fa-boxes me-1"></i> Modal (HPP)</span>
                <h4 class="fw-bold text-slate-dark mb-0">Rp {{ number_format($grandTotalHpp, 0, ',', '.') }}</h4>
                <small class="text-slate-muted d-block mt-1">Harga pokok barang terjual</small>
            </div>
        </div>

        {{-- Box 3: Utang vs Piutang (Status Cash Flow) --}}
        <div class="col-md-6 col-lg-3">
            <div class="stat-box bg-white shadow-sm h-100 border-start border-4 border-primary" style="background-color: #f8fafc !important;">
                <span class="d-block text-slate-muted small fw-bold text-uppercase mb-2"><i class="fas fa-balance-scale me-1"></i> Utang vs Piutang</span>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-slate-muted">Piutang (Masuk):</small>
                    <small class="fw-bold text-success">+ Rp {{ number_format($totalPiutang ?? 0, 0, ',', '.') }}</small>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-slate-muted">Utang (Keluar):</small>
                    <small class="fw-bold text-danger">- Rp {{ number_format($totalUtang ?? 0, 0, ',', '.') }}</small>
                </div>
                {{-- Indikator Peringatan Dini jika Utang lebih besar dari Piutang --}}
                @if(($totalUtang ?? 0) > ($totalPiutang ?? 0))
                    <hr class="my-1 border-danger" style="opacity: 0.2">
                    <small class="text-danger fw-bold d-block text-center" style="font-size: 0.7rem;"><i class="fas fa-exclamation-circle"></i> Awas: Defisit Arus Kas</small>
                @endif
            </div>
        </div>

        {{-- Box 4: Laba Bersih / Kerugian (Dinamic Color) --}}
        <div class="col-md-6 col-lg-3">
            <div class="stat-box {{ $grandTotalLabaBersih >= 0 ? 'bg-gradient-emerald' : 'bg-danger' }} text-white shadow-sm h-100 d-flex flex-column justify-content-center border-0">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="d-block small fw-bold text-uppercase" style="opacity: 0.9;">
                        <i class="fas {{ $grandTotalLabaBersih >= 0 ? 'fa-hand-holding-usd' : 'fa-skull-crossbones' }} me-1"></i> 
                        {{ $grandTotalLabaBersih >= 0 ? 'Laba Bersih Akhir' : 'Kerugian Perusahaan' }}
                    </span>
                    @if($grandTotalLabaBersih >= 0 && $grandTotalPendapatan > 0)
                        <span class="badge bg-white text-success fw-bold px-2 py-1 rounded-pill shadow-sm" style="font-size: 0.7rem;">
                            Margin: {{ number_format(($grandTotalLabaBersih / $grandTotalPendapatan) * 100, 1) }}%
                        </span>
                    @endif
                </div>
                <h3 class="fw-bold text-white mb-0" style="letter-spacing: -0.5px;">
                    {{ $grandTotalLabaBersih < 0 ? '-' : '' }} Rp {{ number_format(abs($grandTotalLabaBersih), 0, ',', '.') }}
                </h3>
            </div>
        </div>
    </div>

    {{-- KONTEN TAB UNTUK ANALITIK LABA KOTOR --}}
    <div class="card card-custom bg-white border-0">
        <div class="card-header bg-white pt-3 pb-0 border-bottom-0">
            <ul class="nav nav-tabs nav-tabs-custom" id="labaTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab"><i class="fas fa-user-tie me-2"></i>Per Sales</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="produk-tab" data-bs-toggle="tab" data-bs-target="#produk" type="button" role="tab"><i class="fas fa-box me-2"></i>Per Produk</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="merek-tab" data-bs-toggle="tab" data-bs-target="#merek" type="button" role="tab"><i class="fas fa-tags me-2"></i>Per Merek</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="customer-tab" data-bs-toggle="tab" data-bs-target="#customer" type="button" role="tab"><i class="fas fa-building me-2"></i>Per Customer</button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="labaTabsContent">
                
                {{-- TAB 1: PER SALES --}}
                <div class="tab-pane fade show active p-0" id="sales" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-laba w-100 mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4" width="5%">Peringkat</th>
                                    <th>Nama Sales / Staff</th>
                                    <th class="text-center">Qty Bersih</th>
                                    <th class="text-end">Omzet (Rp)</th>
                                    <th class="text-end">Laba Kotor (Rp)</th>
                                    <th class="text-center pe-4" width="10%">Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($labaSales as $index => $item)
                                <tr>
                                    <td class="ps-4 text-center fw-bold text-slate-muted">#{{ $index + 1 }}</td>
                                    <td class="fw-bold text-slate-dark">{{ $item->nama }}</td>
                                    <td class="text-center text-slate-muted">{{ $item->total_qty }} pcs</td>
                                    <td class="text-end fw-medium text-slate-dark">{{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-emerald">+ {{ number_format($item->total_laba, 0, ',', '.') }}</td>
                                    <td class="text-center pe-4">
                                        <span class="badge bg-light text-slate-dark border px-2 py-1">{{ $item->total_pendapatan > 0 ? number_format(($item->total_laba / $item->total_pendapatan) * 100, 1) : 0 }}%</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada data penjualan pada rentang waktu ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB 2: PER PRODUK --}}
                <div class="tab-pane fade p-0" id="produk" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-laba w-100 mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4" width="5%">Top</th>
                                    <th>Kode - Nama Barang</th>
                                    <th class="text-center">Qty Bersih</th>
                                    <th class="text-end">Total Omzet</th>
                                    <th class="text-end">Laba Kotor (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($labaProduk as $index => $item)
                                <tr>
                                    <td class="ps-4 text-center fw-bold text-slate-muted">{{ $index + 1 }}</td>
                                    <td class="fw-bold text-slate-dark"><span class="badge bg-light text-secondary border me-2">{{ $item->kode_barang }}</span> {{ $item->nama }}</td>
                                    <td class="text-center text-slate-muted">{{ $item->total_qty }} pcs</td>
                                    <td class="text-end fw-medium text-slate-dark">{{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-emerald">+ {{ number_format($item->total_laba, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada data penjualan pada rentang waktu ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB 3: PER MEREK --}}
                <div class="tab-pane fade p-0" id="merek" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-laba w-100 mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4" width="5%">No</th>
                                    <th>Merek / Brand Produk</th>
                                    <th class="text-center">Qty Bersih</th>
                                    <th class="text-end">Laba Kotor (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($labaMerek as $index => $item)
                                <tr>
                                    <td class="ps-4 text-center fw-bold text-slate-muted">{{ $index + 1 }}</td>
                                    <td class="fw-bold text-slate-dark"><i class="fas fa-tag text-emerald me-2"></i> {{ $item->nama ?: 'Tanpa Merek' }}</td>
                                    <td class="text-center text-slate-muted">{{ $item->total_qty }} pcs</td>
                                    <td class="text-end fw-bold text-emerald">+ {{ number_format($item->total_laba, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data penjualan pada rentang waktu ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB 4: PER CUSTOMER --}}
                <div class="tab-pane fade p-0" id="customer" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-laba w-100 mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4" width="5%">Top</th>
                                    <th>Nama Customer / Toko</th>
                                    <th class="text-center">Total Item Bersih</th>
                                    <th class="text-end">Keuntungan untuk Kita (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($labaCustomer as $index => $item)
                                <tr>
                                    <td class="ps-4 text-center fw-bold text-slate-muted">{{ $index + 1 }}</td>
                                    <td class="fw-bold text-slate-dark"><i class="fas fa-store text-emerald me-2"></i> {{ $item->nama }}</td>
                                    <td class="text-center text-slate-muted">{{ $item->total_qty }} item</td>
                                    <td class="text-end fw-bold text-emerald">+ {{ number_format($item->total_laba, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada data penjualan pada rentang waktu ini.</td></tr>
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
    // JS Sederhana untuk memunculkan input tanggal jika memilih "Custom Tanggal"
    function toggleCustomDate() {
        var tipe = document.getElementById('filter_tipe').value;
        var wrapper = document.getElementById('custom_date_wrapper');
        
        if(tipe === 'custom') {
            wrapper.style.setProperty('display', 'flex', 'important');
        } else {
            wrapper.style.setProperty('display', 'none', 'important');
        }
    }
</script>
@endsection