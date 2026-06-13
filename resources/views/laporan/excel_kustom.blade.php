@php
    // Menghitung jumlah kolom dinamis agar lebar Kop Surat dan Judul pas
    $totalCols = 8;
    if($kategori === 'penjualan') $totalCols = 10;
    elseif($kategori === 'pembelian') $totalCols = 9;
    elseif($kategori === 'backorder') $totalCols = 8;
    elseif(in_array($kategori, ['piutang', 'utang'])) $totalCols = 7;
    elseif(in_array($kategori, ['cn', 'dn'])) $totalCols = 6;
    elseif(in_array($kategori, ['retur_jual', 'retur_beli'])) $totalCols = 9;
@endphp

<table>
    <tr>
        <td colspan="{{ $totalCols }}" style="font-size: 14px; font-weight: bold;">PT. MENTARI ATLAS</td>
    </tr>
    <tr>
        <td colspan="{{ $totalCols }}" style="font-size: 12px; font-weight: bold;">{{ $judulLaporan }}</td>
    </tr>
    <tr>
        <td colspan="{{ $totalCols }}" style="font-size: 10px; color: #555555;">Periode: {{ $periodeLabel }}</td>
    </tr>
    <tr>
        <td colspan="{{ $totalCols }}" style="font-size: 10px; color: #555555;">Filter Grup: {{ empty($groupBy) ? 'Semua Data' : strtoupper(implode(', ', $groupBy)) }}</td>
    </tr>
    <tr><td colspan="{{ $totalCols }}"></td></tr> <tbody>
        @php $grandQty = 0; $grandNominal = 0; $grandSisa = 0; @endphp

        @forelse($groupedData as $groupName => $items)
            
            @if($groupName !== 'Semua Data')
            <tr><td colspan="{{ $totalCols }}"></td></tr> <tr>
                <td colspan="{{ $totalCols }}" style="font-size: 11px; font-weight: bold; color: #0f172a;">
                    ■ {{ $groupName }}
                </td>
            </tr>
            @endif

            <tr>
                @php $thStyle = "background-color: #d9d9d9; font-weight: bold; border: 1px solid #000000; text-align: center;"; @endphp
                
                <th style="{{ $thStyle }}">No</th>
                <th style="{{ $thStyle }}">Tanggal</th>
                
                @if($kategori === 'penjualan')
                    <th style="{{ $thStyle }}">No. SO</th><th style="{{ $thStyle }}">Customer</th><th style="{{ $thStyle }}">Salesman</th><th style="{{ $thStyle }}">Merek</th><th style="{{ $thStyle }}">Nama Barang</th><th style="{{ $thStyle }}">Qty</th><th style="{{ $thStyle }} text-align: right;">Nominal (Rp)</th><th style="{{ $thStyle }}">Status</th>
                @elseif($kategori === 'pembelian')
                    <th style="{{ $thStyle }}">No. PO</th><th style="{{ $thStyle }}">Supplier</th><th style="{{ $thStyle }}">Merek</th><th style="{{ $thStyle }}">Nama Barang</th><th style="{{ $thStyle }}">Qty</th><th style="{{ $thStyle }} text-align: right;">Total Harga (Rp)</th><th style="{{ $thStyle }}">Status</th>
                @elseif(in_array($kategori, ['piutang', 'utang']))
                    <th style="{{ $thStyle }}">No. Dokumen</th><th style="{{ $thStyle }}">{{ $kategori === 'piutang' ? 'Customer' : 'Supplier' }}</th><th style="{{ $thStyle }} text-align: right;">Total Transaksi (Rp)</th><th style="{{ $thStyle }} text-align: right;">Sisa Tagihan (Rp)</th><th style="{{ $thStyle }}">Status Bayar</th>
                @elseif(in_array($kategori, ['cn', 'dn']))
                    <th style="{{ $thStyle }}">No. Dokumen</th><th style="{{ $thStyle }}">{{ $kategori === 'cn' ? 'Customer' : 'Supplier' }}</th><th style="{{ $thStyle }} text-align: right;">Nominal Potongan (Rp)</th><th style="{{ $thStyle }}">Keterangan</th>
                @elseif(in_array($kategori, ['retur_jual', 'retur_beli']))
                    <th style="{{ $thStyle }}">No. Retur</th><th style="{{ $thStyle }}">{{ $kategori === 'retur_jual' ? 'Customer' : 'Supplier' }}</th><th style="{{ $thStyle }}">Merek</th><th style="{{ $thStyle }}">Nama Barang</th><th style="{{ $thStyle }}">Qty</th><th style="{{ $thStyle }} text-align: right;">Nominal (Rp)</th><th style="{{ $thStyle }}">Kondisi Fisik</th>
                @elseif($kategori === 'backorder')
                    <th style="{{ $thStyle }}">No. SO</th><th style="{{ $thStyle }}">Customer</th><th style="{{ $thStyle }}">Merek</th><th style="{{ $thStyle }}">Nama Barang</th><th style="{{ $thStyle }}">Qty Kurang</th><th style="{{ $thStyle }}">Status Antrian</th>
                @endif
            </tr>
            
            @php $subQty = 0; $subNominal = 0; $subSisa = 0; @endphp
            @foreach($items as $index => $item)
                @php 
                    $subQty += $item->qty; $subNominal += $item->nominal; 
                    $grandQty += $item->qty; $grandNominal += $item->nominal;
                    if(isset($item->sisa)) { $subSisa += $item->sisa; $grandSisa += $item->sisa; }
                    
                    $msoText = "mso-number-format:'\@'; border: 1px solid #000000; text-align: center;";
                    $tdStyle = "border: 1px solid #000000;";
                    $tdNum = "border: 1px solid #000000; text-align: right;";
                    $tdCenter = "border: 1px solid #000000; text-align: center;";
                @endphp
                <tr>
                    <td style="{{ $tdCenter }}">{{ $index + 1 }}</td>
                    <td style="{{ $msoText }}">{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                    
                    @if($kategori === 'penjualan')
                        <td style="{{ $msoText }} font-weight: bold;">{{ $item->nomor }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->customer ?? '-' }}</td>
                        <td style="{{ $tdCenter }}">{{ $item->salesman ?? '-' }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->merek }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->barang }}</td>
                        <td style="{{ $tdCenter }}">{{ $item->qty }}</td>
                        <td style="{{ $tdNum }}">{{ $item->nominal }}</td>
                        <td style="{{ $tdCenter }}">{{ strtoupper($item->keterangan ?? '-') }}</td>

                    @elseif($kategori === 'pembelian')
                        <td style="{{ $msoText }} font-weight: bold;">{{ $item->nomor }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->supplier ?? '-' }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->merek }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->barang }}</td>
                        <td style="{{ $tdCenter }}">{{ $item->qty }}</td>
                        <td style="{{ $tdNum }}">{{ $item->nominal }}</td>
                        <td style="{{ $tdCenter }}">{{ strtoupper($item->keterangan ?? '-') }}</td>

                    @elseif(in_array($kategori, ['piutang', 'utang']))
                        <td style="{{ $msoText }} font-weight: bold;">{{ $item->nomor }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->customer ?? $item->supplier ?? '-' }}</td>
                        <td style="{{ $tdNum }}">{{ $item->nominal }}</td>
                        <td style="{{ $tdNum }} color: #ff0000; font-weight: bold;">{{ $item->sisa }}</td>
                        <td style="{{ $tdCenter }}">{{ strtoupper($item->keterangan ?? '-') }}</td>

                    @elseif(in_array($kategori, ['cn', 'dn']))
                        <td style="{{ $msoText }} font-weight: bold;">{{ $item->nomor }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->customer ?? $item->supplier ?? '-' }}</td>
                        <td style="{{ $tdNum }}">{{ $item->nominal }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->keterangan ?? '-' }}</td>

                    @elseif(in_array($kategori, ['retur_jual', 'retur_beli']))
                        <td style="{{ $msoText }} font-weight: bold;">{{ $item->nomor }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->customer ?? $item->supplier ?? '-' }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->merek }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->barang }}</td>
                        <td style="{{ $tdCenter }}">{{ $item->qty }}</td>
                        <td style="{{ $tdNum }}">{{ $item->nominal }}</td>
                        <td style="{{ $tdCenter }}">{{ strtoupper($item->keterangan ?? '-') }}</td>

                    @elseif($kategori === 'backorder')
                        <td style="{{ $msoText }} font-weight: bold;">{{ $item->nomor }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->customer ?? '-' }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->merek }}</td>
                        <td style="{{ $tdStyle }}">{{ $item->barang }}</td>
                        <td style="{{ $tdCenter }} color: #ff0000; font-weight: bold;">{{ $item->qty }}</td>
                        <td style="{{ $tdCenter }}">{{ strtoupper($item->keterangan ?? '-') }}</td>
                    @endif
                </tr>
            @endforeach

            @if($groupName !== 'Semua Data')
            <tr>
                @php $subBg = "background-color: #f2f2f2; border: 1px solid #000000; font-weight: bold;" @endphp
                @if($kategori === 'penjualan')
                    <td colspan="7" style="{{ $subBg }} text-align: right;">Subtotal :</td><td style="{{ $subBg }} text-align: center;">{{ $subQty }}</td><td style="{{ $subBg }} text-align: right;">{{ $subNominal }}</td><td style="{{ $subBg }}"></td>
                @elseif(in_array($kategori, ['pembelian', 'retur_jual', 'retur_beli']))
                    <td colspan="6" style="{{ $subBg }} text-align: right;">Subtotal :</td><td style="{{ $subBg }} text-align: center;">{{ $subQty }}</td><td style="{{ $subBg }} text-align: right;">{{ $subNominal }}</td><td style="{{ $subBg }}"></td>
                @elseif(in_array($kategori, ['piutang', 'utang']))
                    <td colspan="4" style="{{ $subBg }} text-align: right;">Subtotal :</td><td style="{{ $subBg }} text-align: right;">{{ $subNominal }}</td><td style="{{ $subBg }} text-align: right; color: #ff0000;">{{ $subSisa }}</td><td style="{{ $subBg }}"></td>
                @elseif(in_array($kategori, ['cn', 'dn']))
                    <td colspan="4" style="{{ $subBg }} text-align: right;">Subtotal :</td><td style="{{ $subBg }} text-align: right;">{{ $subNominal }}</td><td style="{{ $subBg }}"></td>
                @elseif($kategori === 'backorder')
                    <td colspan="6" style="{{ $subBg }} text-align: right;">Total Antrian :</td><td style="{{ $subBg }} text-align: center; color: #ff0000;">{{ $subQty }}</td><td style="{{ $subBg }}"></td>
                @endif
            </tr>
            @endif

        @empty
            <tr><td colspan="{{ $totalCols }}" style="text-align: center;">Tidak ada data ditemukan.</td></tr>
        @endforelse
        
        @if($groupedData->count() > 0)
        <tr><td colspan="{{ $totalCols }}"></td></tr> <tr>
            @php $grandBg = "background-color: #d9d9d9; border: 1px solid #000000; font-weight: bold;" @endphp
            @if($kategori === 'penjualan')
                <td colspan="7" style="{{ $grandBg }} text-align: right;">GRAND TOTAL :</td><td style="{{ $grandBg }} text-align: center;">{{ $grandQty }}</td><td style="{{ $grandBg }} text-align: right;">{{ $grandNominal }}</td><td style="{{ $grandBg }}"></td>
            @elseif(in_array($kategori, ['pembelian', 'retur_jual', 'retur_beli']))
                <td colspan="6" style="{{ $grandBg }} text-align: right;">GRAND TOTAL :</td><td style="{{ $grandBg }} text-align: center;">{{ $grandQty }}</td><td style="{{ $grandBg }} text-align: right;">{{ $grandNominal }}</td><td style="{{ $grandBg }}"></td>
            @elseif(in_array($kategori, ['piutang', 'utang']))
                <td colspan="4" style="{{ $grandBg }} text-align: right;">GRAND TOTAL :</td><td style="{{ $grandBg }} text-align: right;">{{ $grandNominal }}</td><td style="{{ $grandBg }} text-align: right; color: #ff0000;">{{ $grandSisa }}</td><td style="{{ $grandBg }}"></td>
            @elseif(in_array($kategori, ['cn', 'dn']))
                <td colspan="4" style="{{ $grandBg }} text-align: right;">GRAND TOTAL :</td><td style="{{ $grandBg }} text-align: right;">{{ $grandNominal }}</td><td style="{{ $grandBg }}"></td>
            @elseif($kategori === 'backorder')
                <td colspan="6" style="{{ $grandBg }} text-align: right;">GRAND TOTAL ANTRIAN :</td><td style="{{ $grandBg }} text-align: center; color: #ff0000;">{{ $grandQty }}</td><td style="{{ $grandBg }}"></td>
            @endif
        </tr>
        @endif
    </tbody>
</table>