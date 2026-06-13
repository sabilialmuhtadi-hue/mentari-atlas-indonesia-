<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Penjualan - {{ str_replace('SO', 'INV', $penjualan->no_so) }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; border-bottom: 3px double #333; padding-bottom: 15px; margin-bottom: 25px; }
        .info-table { width: 100%; margin-bottom: 25px; }
        .info-table td { vertical-align: top; padding: 4px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        .items-table th { background-color: #f8f9fa; text-align: center; font-weight: bold; }
        .text-center { text-align: center !important; }
        .text-end { text-align: right !important; }
        .totals-table { width: 40%; float: right; border-collapse: collapse; margin-bottom: 40px; }
        .totals-table td { padding: 8px; border: 1px solid #ccc; }
        .totals-table .grand-total { font-weight: bold; font-size: 15px; background-color: #f8f9fa; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .signature { width: 100%; margin-top: 30px; }
        .signature td { text-align: center; width: 50%; }
        .signature-line { margin-top: 80px; border-top: 1px solid #333; display: inline-block; width: 200px; }
        
        .bo-note { font-size: 11px; color: #d97706; font-style: italic; display: block; margin-top: 4px; }
        .bo-success { font-size: 11px; color: #10b981; font-style: italic; display: block; margin-top: 4px; font-weight: bold; }
        
        /* CSS UNTUK FITUR EDITABLE */
        .editable { transition: background-color 0.2s; border-bottom: 1px dashed transparent; border-radius: 3px; padding: 0 3px;}
        .editable:hover { background-color: #fef08a; cursor: text; border-bottom: 1px dashed #ca8a04; }
        .editable:focus { outline: none; background-color: #fef9c3; border-bottom: 1px solid #ca8a04; }
        
        @media print {
            .no-print { display: none; }
            .editable:hover, .editable:focus { background-color: transparent; border-bottom: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print text-right" style="text-align: right; margin-bottom: 20px; background: #fffbeb; padding: 10px; border-radius: 5px; border: 1px solid #fde68a;">
            <p style="margin: 0 0 10px 0; color: #b45309; font-size: 12px; font-family: sans-serif;">💡 <strong>Tips:</strong> Klik pada Jatuh Tempo, Rekening, atau Keterangan untuk mengubah teks invoice secara instan.</p>
            <button onclick="window.print()" style="padding: 10px 20px; background: #10b981; color: #fff; cursor: pointer; border: none; border-radius: 4px; font-weight: bold;">Cetak Faktur</button>
            <a href="{{ route('penjualan.index') }}" style="padding: 10px 20px; background: #e2e8f0; color: #333; text-decoration: none; margin-left: 10px; border-radius: 4px;">Kembali</a>
        </div>

        <div class="header">
            <h2 style="margin: 0; letter-spacing: 1px;">FAKTUR PENJUALAN (INVOICE)</h2>
            <p style="margin: 5px 0 0 0; color: #666;">Tagihan resmi untuk pesanan Anda</p>
        </div>

        <table class="info-table">
            <tr>
                <td width="15%"><strong>No. Invoice</strong></td>
                <td width="35%">: <strong>{{ str_replace('SO', 'INV', $penjualan->no_so) }}</strong></td>
                <td width="15%"><strong>Kepada Yth.</strong></td>
                <td width="35%">: <strong>{{ $penjualan->customer->nama_customer }}</strong></td>
            </tr>
            <tr>
                <td><strong>Tanggal Tagihan</strong></td>
                <td>: <span class="editable" contenteditable="true">{{ date('d-M-Y') }}</span></td>
                <td><strong>Alamat</strong></td>
                <td>: <span class="editable" contenteditable="true">{{ $penjualan->customer->alamat ?? 'Isi alamat di sini...' }}</span></td>
            </tr>
            <tr>
                <td><strong>Jatuh Tempo</strong></td>
                <td>: <span class="editable" contenteditable="true">{{ \Carbon\Carbon::now()->addDays(30)->format('d-M-Y') }} (30 Hari)</span></td>
                <td><strong>No. Telepon</strong></td>
                <td>: <span class="editable" contenteditable="true">{{ $penjualan->customer->no_telepon ?? '-' }}</span></td>
            </tr>
        </table>

        @php
            $backorders = \App\Models\BackOrder::where('penjualan_id', $penjualan->id)->get()->keyBy('barang_id');
        @endphp

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="45%" style="text-align: left;">Nama Barang & Keterangan</th>
                    <th width="10%">Qty</th>
                    <th width="20%" class="text-end">Harga Satuan (Rp)</th>
                    <th width="20%" class="text-end">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualan->details as $index => $detail)
                @php
                    $bo = $backorders->get($detail->barang_id);
                    $qtyPesan = $detail->jumlah;
                    $statusTeks = 'normal';
                    
                    if($bo) {
                        $statusTeks = (strtolower($bo->status_bo) === 'terpenuhi' || strtolower($bo->status_bo) === 'selesai') ? 'lunas' : 'hutang';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $detail->barang->nama_barang }}</strong>
                        
                        @if($statusTeks === 'hutang')
                            <span class="bo-note editable" contenteditable="true">*Info: Pengiriman parsial berjalan (Dikirim awal: {{ $qtyPesan - $bo->jumlah_kurang }}, Sisa menunggu dikirim: {{ $bo->jumlah_kurang }} unit). Tagihan meliputi seluruh kuantitas pesanan.</span>
                        @elseif($statusTeks === 'lunas')
                            <span class="bo-success editable" contenteditable="true">*Info: Sisa Back Order sejumlah {{ $bo->jumlah_kurang }} unit telah dikirim. Seluruh item lengkap diterima.</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $qtyPesan }}</td>
                    <td class="text-end"><span class="editable" contenteditable="true">{{ number_format($detail->harga_satuan, 0, ',', '.') }}</span></td>
                    <td class="text-end"><strong><span class="editable" contenteditable="true">{{ number_format($detail->subtotal, 0, ',', '.') }}</span></strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="clearfix">
            <table class="totals-table">
                <tr class="grand-total">
                    <td>TOTAL TAGIHAN</td>
                    <td class="text-end">Rp <span class="editable" contenteditable="true">{{ number_format($penjualan->total_semua, 0, ',', '.') }}</span></td>
                </tr>
            </table>
        </div>

        <div style="clear: both; margin-bottom: 20px;">
            <p class="editable" contenteditable="true" style="font-size: 12px; color: #666; font-style: italic; line-height: 1.5;">
                * Pembayaran harap ditransfer ke rekening: <strong>BCA 123456789 a.n PT Mentari Atlas</strong>.<br>
                * Dokumen ini sah sebagai bukti tagihan final komoditas pesanan Anda.
            </p>
        </div>

        <table class="signature">
            <tr>
                <td>Hormat Kami,<br><strong>PT Mentari Atlas</strong><br><br><br><div class="signature-line"></div><br><span class="editable" contenteditable="true">(Bagian Keuangan)</span></td>
                <td>Menyetujui,<br><strong>{{ $penjualan->customer->nama_customer }}</strong><br><br><br><div class="signature-line"></div><br>(Tanda Tangan & Cap Perusahaan)</td>
            </tr>
        </table>
    </div>
</body>
</html>