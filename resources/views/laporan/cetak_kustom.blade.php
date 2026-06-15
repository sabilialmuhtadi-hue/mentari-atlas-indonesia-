<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judulLaporan }}</title>
    <style>
        /* TEMA LAPORAN KELAS EKSEKUTIF PREMIUM */
        body { font-family: 'Inter', 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #1e293b; margin: 0; padding: 15px; }
        
        .kop-surat { text-align: center; border-bottom: 3px solid #10b981; padding-bottom: 12px; margin-bottom: 20px; position: relative; }
        .kop-surat h1 { margin: 0; font-size: 24px; color: #059669; letter-spacing: 2px; font-weight: 900; text-transform: uppercase; }
        .kop-surat p { margin: 5px 0 0 0; color: #475569; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;}
        
        .info-laporan { margin-bottom: 15px; font-size: 10px; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .info-laporan table { width: 100%; }
        .info-laporan td { padding: 3px 0; }
        
        /* JUDUL KELOMPOK DATA DI ATAS TABEL */
        .grup-header { font-size: 11px; font-weight: bold; color: #0f172a; margin-top: 25px; margin-bottom: 8px; padding: 6px 10px; background-color: #d1fae5; border-left: 4px solid #10b981; border-radius: 4px; display: inline-block; }
        
        /* TABEL UTAMA DENGAN DUKUNGAN PERFECT ALIGNMENT */
        .tabel-data { width: 100%; border-collapse: collapse; margin-bottom: 15px; table-layout: fixed; }
        .tabel-data th, .tabel-data td { border: 1px solid #cbd5e1; padding: 8px 6px; vertical-align: middle; word-wrap: break-word; }
        .tabel-data th { background-color: #10b981; color: #ffffff; font-weight: bold; text-transform: uppercase; font-size: 9px; text-align: center; letter-spacing: 0.5px; border-color: #059669; }
        .tabel-data td { font-size: 9px; }
        
        /* Zebra Striping */
        .tabel-data tbody tr:nth-child(even) { background-color: #f8fafc; }
        
        /* Mencegah pemotongan baris yang jelek saat cetak PDF */
        tr { page-break-inside: avoid; }
        .grup-wrapper { page-break-inside: avoid; margin-bottom: 20px; }
        
        /* STYLE NOTA TOTALAN */
        .bg-subtotal td { background-color: #e2e8f0 !important; font-weight: bold; color: #0f172a; border-top: 2px solid #94a3b8; font-style: italic; }
        .bg-grandtotal td { background-color: #0f172a !important; color: #ffffff !important; font-weight: 800; font-size: 11px; padding: 10px 6px; border: none; }
        .bg-grandtotal .text-success { color: #34d399 !important; }
        .bg-grandtotal .text-danger { color: #f87171 !important; }
        
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .fw-bold { font-weight: bold; }
        .text-danger { color: #dc2626; }
        .text-success { color: #16a34a; }

        @page { size: A4 landscape; margin: 10mm; }
        @media print { body { padding: 0; } .info-laporan { border: none; } }
    </style>
</head>
<body onload="{{ $format === 'pdf' ? 'window.print()' : '' }}">

    <div class="kop-surat">
        <h1>PT. MENTARI ATLAS INDONESIA</h1>
        <div style="color: #475569; font-size: 10px; margin-top: 5px; line-height: 1.4;">
            Jl. Jenderal Sudirman No. 123, Pontianak, Kalimantan Barat<br>
            Telp: (0561) 123456 | Email: info@mentariprimasemesta.com
        </div>
        <p style="margin-top: 15px;">{{ $judulLaporan }}</p>
    </div>

    <div class="info-laporan">
        <table>
            <tr>
                <td width="12%" class="fw-bold">Periode Waktu</td><td width="2%">:</td><td width="50%">{{ $periodeLabel }}</td>
                <td width="12%" class="fw-bold text-right">Dicetak Oleh</td><td width="2%">:</td><td>{{ $user->name }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Filter Grup</td><td>:</td><td>{{ empty($groupBy) ? 'Semua Data (Buku Besar)' : strtoupper(implode(', ', $groupBy)) }}</td>
                <td class="fw-bold text-right">Waktu Cetak</td><td>:</td><td>{{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    @php $grandQty = 0; $grandNominal = 0; $grandSisa = 0; @endphp

    @forelse($groupedData as $groupName => $items)
        <div class="grup-wrapper">
            
            {{-- 1. TAMPILAN NAMA GRUP DI ATAS TABEL (SAMA SEPERTI EXCEL) --}}
            @if($groupName !== 'Semua Data')
                <div class="grup-header">
                    <i class="fas fa-layer-group"></i> {{ $groupName }}
                </div>
            @endif

            {{-- 2. TABEL MANDIRI PER KELOMPOK --}}
            <table class="tabel-data">
                <thead>
                    <tr>
                        {{-- PERSENTASE LEBAR KOLOM DIKUNCI MATI AGAR ANTAR TABEL TETAP LURUS KONSISTEN --}}
                        <th width="3%">No</th>
                        <th width="8%">Tanggal</th>
                        
                        @if($kategori === 'penjualan')
                            <th width="12%">No. SO</th><th width="16%">Customer</th><th width="10%">Salesman</th><th width="23%">Merek | Barang</th><th width="5%">Qty</th><th width="14%" class="text-right">Nominal (Rp)</th><th width="9%">Status</th>
                        @elseif($kategori === 'pembelian')
                            <th width="12%">No. PO</th><th width="18%">Supplier</th><th width="25%">Merek | Barang</th><th width="5%">Qty</th><th width="16%" class="text-right">Total Harga (Rp)</th><th width="13%">Status</th>
                        @elseif(in_array($kategori, ['piutang', 'utang']))
                            <th width="15%">No. Dokumen</th><th width="30%">{{ $kategori === 'piutang' ? 'Customer' : 'Supplier' }}</th><th width="16%" class="text-right">Total Transaksi (Rp)</th><th width="16%" class="text-right">Sisa Tagihan (Rp)</th><th width="12%">Status Bayar</th>
                        @elseif(in_array($kategori, ['cn', 'dn']))
                            <th width="15%">No. Dokumen</th><th width="30%">{{ $kategori === 'cn' ? 'Customer' : 'Supplier' }}</th><th width="16%" class="text-right">Nominal Potongan (Rp)</th><th width="28%">Keterangan</th>
                        @elseif(in_array($kategori, ['retur_jual', 'retur_beli']))
                            <th width="12%">No. Retur</th><th width="20%">{{ $kategori === 'retur_jual' ? 'Customer' : 'Supplier' }}</th><th width="25%">Merek | Barang</th><th width="5%">Qty</th><th width="14%" class="text-right">Nominal (Rp)</th><th width="13%">Kondisi Fisik</th>
                        @elseif($kategori === 'backorder')
                            <th width="12%">No. SO</th><th width="20%">Customer</th><th width="30%">Merek | Barang</th><th width="10%">Qty Kurang</th><th width="17%">Status Antrian</th>
                        @endif
                    </tr>
                </thead>
                
                <tbody>
                    @php $subQty = 0; $subNominal = 0; $subSisa = 0; @endphp
                    @foreach($items as $index => $item)
                        @php 
                            $subQty += $item->qty; $subNominal += $item->nominal; 
                            $grandQty += $item->qty; $grandNominal += $item->nominal;
                            if(isset($item->sisa)) { $subSisa += $item->sisa; $grandSisa += $item->sisa; }
                        @endphp
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                            
                            @if($kategori === 'penjualan')
                                <td class="text-center fw-bold">{{ $item->nomor }}</td><td>{{ $item->customer ?? '-' }}</td><td class="text-center">{{ $item->salesman ?? '-' }}</td>
                                <td><span class="fw-bold">{{ $item->merek }}</span> <span style="color:#64748b;">| {{ $item->barang }}</span></td>
                                <td class="text-center fw-bold">{{ $item->qty }}</td><td class="text-right">{{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td class="text-center">{{ strtoupper(str_replace('_', ' ', $item->keterangan ?? '-')) }}</td>

                            @elseif($kategori === 'pembelian')
                                <td class="text-center fw-bold">{{ $item->nomor }}</td><td>{{ $item->supplier ?? '-' }}</td>
                                <td><span class="fw-bold">{{ $item->merek }}</span> <span style="color:#64748b;">| {{ $item->barang }}</span></td>
                                <td class="text-center fw-bold">{{ $item->qty }}</td><td class="text-right">{{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td class="text-center">{{ strtoupper(str_replace('_', ' ', $item->keterangan ?? '-')) }}</td>

                            @elseif(in_array($kategori, ['piutang', 'utang']))
                                <td class="text-center fw-bold">{{ $item->nomor }}</td><td>{{ $item->customer ?? $item->supplier ?? '-' }}</td>
                                <td class="text-right">{{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td class="text-right fw-bold text-danger">{{ number_format($item->sisa, 0, ',', '.') }}</td>
                                <td class="text-center">{{ strtoupper(str_replace('_', ' ', $item->keterangan ?? '-')) }}</td>

                            @elseif(in_array($kategori, ['cn', 'dn']))
                                <td class="text-center fw-bold">{{ $item->nomor }}</td><td>{{ $item->customer ?? $item->supplier ?? '-' }}</td>
                                <td class="text-right fw-bold">{{ number_format($item->nominal, 0, ',', '.') }}</td><td>{{ $item->keterangan ?? '-' }}</td>

                            @elseif(in_array($kategori, ['retur_jual', 'retur_beli']))
                                <td class="text-center fw-bold">{{ $item->nomor }}</td><td>{{ $item->customer ?? $item->supplier ?? '-' }}</td>
                                <td><span class="fw-bold">{{ $item->merek }}</span> <span style="color:#64748b;">| {{ $item->barang }}</span></td>
                                <td class="text-center fw-bold">{{ $item->qty }}</td><td class="text-right">{{ number_format($item->nominal, 0, ',', '.') }}</td>
                                <td class="text-center">{{ strtoupper(str_replace('_', ' ', $item->keterangan ?? '-')) }}</td>

                            @elseif($kategori === 'backorder')
                                <td class="text-center fw-bold">{{ $item->nomor }}</td><td>{{ $item->customer ?? '-' }}</td>
                                <td><span class="fw-bold">{{ $item->merek }}</span> <span style="color:#64748b;">| {{ $item->barang }}</span></td>
                                <td class="text-center fw-bold text-danger">{{ $item->qty }}</td>
                                <td class="text-center">{{ strtoupper(str_replace('_', ' ', $item->keterangan ?? '-')) }}</td>
                            @endif
                        </tr>
                    @endforeach

                    {{-- 3. BARIS SUBTOTAL KELOMPOK --}}
                    @if($groupName !== 'Semua Data')
                    <tr class="bg-subtotal">
                        @if($kategori === 'penjualan')
                            <td colspan="6" class="text-right">Subtotal :</td><td class="text-center">{{ $subQty }}</td><td class="text-right">Rp {{ number_format($subNominal, 0, ',', '.') }}</td><td></td>
                        @elseif(in_array($kategori, ['pembelian', 'retur_jual', 'retur_beli']))
                            <td colspan="5" class="text-right">Subtotal :</td><td class="text-center">{{ $subQty }}</td><td class="text-right">Rp {{ number_format($subNominal, 0, ',', '.') }}</td><td></td>
                        @elseif(in_array($kategori, ['piutang', 'utang']))
                            <td colspan="4" class="text-right">Subtotal :</td><td class="text-right">Rp {{ number_format($subNominal, 0, ',', '.') }}</td><td class="text-right text-danger">Rp {{ number_format($subSisa, 0, ',', '.') }}</td><td></td>
                        @elseif(in_array($kategori, ['cn', 'dn']))
                            <td colspan="4" class="text-right">Subtotal :</td><td class="text-right">Rp {{ number_format($subNominal, 0, ',', '.') }}</td><td></td>
                        @elseif($kategori === 'backorder')
                            <td colspan="5" class="text-right">Total Antrian :</td><td class="text-center text-danger">{{ $subQty }}</td><td></td>
                        @endif
                    </tr>
                    @endif

                    {{-- 4. GRAND TOTAL UTAMA DI PALING BAWAH LAPORAN (PASTI SINKRON KARENA DI DALAM TABEL YANG SAMA) --}}
                    @if($loop->last)
                    <tr class="bg-grandtotal">
                        @if($kategori === 'penjualan')
                            <td colspan="6" class="text-right">GRAND TOTAL KESELURUHAN :</td>
                            <td class="text-center text-success">{{ $grandQty }}</td>
                            <td class="text-right text-success">Rp {{ number_format($grandNominal, 0, ',', '.') }}</td>
                            <td></td>
                        @elseif($kategori === 'pembelian')
                            <td colspan="5" class="text-right">GRAND TOTAL KESELURUHAN :</td>
                            <td class="text-center text-success">{{ $grandQty }}</td>
                            <td class="text-right text-success">Rp {{ number_format($grandNominal, 0, ',', '.') }}</td>
                            <td></td>
                        @elseif(in_array($kategori, ['retur_jual', 'retur_beli']))
                            <td colspan="5" class="text-right">GRAND TOTAL KESELURUHAN :</td>
                            <td class="text-center text-success">{{ $grandQty }}</td>
                            <td class="text-right text-success">Rp {{ number_format($grandNominal, 0, ',', '.') }}</td>
                            <td></td>
                        @elseif(in_array($kategori, ['piutang', 'utang']))
                            <td colspan="4" class="text-right">GRAND TOTAL KESELURUHAN :</td>
                            <td class="text-right text-success">Rp {{ number_format($grandNominal, 0, ',', '.') }}</td>
                            <td class="text-right text-danger">Rp {{ number_format($grandSisa, 0, ',', '.') }}</td>
                            <td></td>
                        @elseif(in_array($kategori, ['cn', 'dn']))
                            <td colspan="4" class="text-right">GRAND TOTAL :</td>
                            <td class="text-right text-success">Rp {{ number_format($grandNominal, 0, ',', '.') }}</td>
                            <td></td>
                        @elseif($kategori === 'backorder')
                            <td colspan="5" class="text-right">GRAND TOTAL ANTRIAN :</td>
                            <td class="text-center text-danger" style="font-size: 10px; font-weight: bold;">{{ $grandQty }}</td>
                            <td></td>
                        @endif
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @empty
        <table class="tabel-data">
            <tbody>
                <tr><td class="text-center" style="padding: 30px; font-size: 11px;">Tidak ada data transaksi yang ditemukan.</td></tr>
    @endforelse

    {{-- 5. KOTAK TANDA TANGAN (SIGNATURE BOX) --}}
    <div style="margin-top: 50px; width: 100%; page-break-inside: avoid;">
        <table style="width: 100%; border: none;">
            <tr style="border: none;">
                <td style="width: 35%; border: none; text-align: center; font-size: 11px;">
                    <br>
                    Dicetak Oleh,<br><br><br><br><br>
                    ( <b>{{ $user->name }}</b> )<br>
                    Admin / Staff
                </td>
                <td style="width: 30%; border: none;"></td>
                <td style="width: 35%; border: none; text-align: center; font-size: 11px;">
                    Pontianak, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                    Mengetahui,<br><br><br><br><br>
                    ( _______________________ )<br>
                    <b>Direktur</b>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>