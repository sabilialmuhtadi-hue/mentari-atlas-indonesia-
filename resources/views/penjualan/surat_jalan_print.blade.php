<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $penjualan->no_so }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 14px; color: #000; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { vertical-align: top; padding: 3px 0; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 10px 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center !important; }
        .signature { width: 100%; margin-top: 50px; }
        .signature td { text-align: center; width: 33%; }
        .signature-line { margin-top: 70px; border-top: 1px solid #000; display: inline-block; width: 160px; }
        
        /* Pewarnaan Khusus Notifikasi Tahap Pengiriman */
        .bo-note { font-size: 11px; font-style: italic; margin-top: 4px; display: block; color: #b45309; }
        .bo-success { font-size: 11px; font-style: italic; margin-top: 4px; display: block; color: #10b981; font-weight: bold; }
        
        /* CSS UNTUK FITUR EDITABLE */
        .editable { transition: background-color 0.2s; border-bottom: 1px dashed transparent; }
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
            <p style="margin: 0 0 10px 0; color: #b45309; font-size: 12px; font-family: sans-serif;">💡 <strong>Tips:</strong> Klik pada teks alamat, nama pengirim/supir, atau plat nomor untuk mengubahnya secara langsung sebelum dicetak.</p>
            <button onclick="window.print()" style="padding: 10px 20px; background: #000; color: #fff; cursor: pointer; border: none; border-radius: 4px;">Cetak Sekarang</button>
            <a href="{{ route('penjualan.index') }}" style="padding: 10px 20px; background: #ccc; color: #000; text-decoration: none; margin-left: 10px; border-radius: 4px;">Kembali</a>
        </div>

        <div class="header">
            <h2 style="margin: 0; letter-spacing: 2px;">SURAT JALAN</h2>
            <p style="margin: 5px 0 0 0;">Harap periksa kelengkapan barang sebelum ditandatangani</p>
        </div>

        <table class="info-table">
            <tr>
                <td width="18%"><strong>No. Surat Jalan</strong></td>
                <td width="32%">: SJ-{{ str_replace('SO-', '', $penjualan->no_so) }}</td>
                <td width="15%"><strong>Kepada Yth.</strong></td>
                <td width="35%">: <strong>{{ $penjualan->customer->nama_customer }}</strong></td>
            </tr>
            <tr>
                <td><strong>Tanggal Kirim</strong></td>
                <td>: <span class="editable" contenteditable="true">{{ date('d-M-Y') }}</span></td>
                <td><strong>Alamat</strong></td>
                <td>: <span class="editable" contenteditable="true">{{ $penjualan->customer->alamat ?? 'Tulis alamat tujuan di sini...' }}</span></td>
            </tr>
            <tr>
                <td><strong>No. Referensi (SO)</strong></td>
                <td>: {{ $penjualan->no_so }}</td>
                <td><strong>No. Telepon</strong></td>
                <td>: <span class="editable" contenteditable="true">{{ $penjualan->customer->no_telepon ?? '-' }}</span></td>
            </tr>
            <tr>
                <td><strong>Plat Kendaraan</strong></td>
                <td>: <span class="editable" contenteditable="true">[Ketik Plat No / Ekspedisi]</span></td>
                <td><strong>Catatan</strong></td>
                <td>: <span class="editable" contenteditable="true">-</span></td>
            </tr>
        </table>

        @php
            $backorders = \App\Models\BackOrder::where('penjualan_id', $penjualan->id)->get()->keyBy('barang_id');
        @endphp

        <table class="items-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="55%" style="text-align: left;">Nama Barang / Deskripsi</th>
                    <th width="20%" class="text-center">Dipesan Awal</th>
                    <th width="20%" class="text-center">Dikirim Saat Ini</th>
                </tr>
            </thead>
            <tbody>
                @foreach($penjualan->details as $index => $detail)
                @php
                    $bo = $backorders->get($detail->barang_id);
                    $qtyPesan = $detail->jumlah;
                    
                    // LOGIKA CERDAS PENGIRIMAN PARSIAL VS PELUNASAN
                    if ($bo) {
                        if (strtolower($bo->status_bo) === 'terpenuhi' || strtolower($bo->status_bo) === 'selesai') {
                            // TAHAP 2: Jika BO sudah lunas, yang dikirim di mobil HANYA barang sisa BO
                            $qtyKirim = $bo->jumlah_kurang;
                            $statusTeks = 'pelunasan';
                        } else {
                            // TAHAP 1: Jika BO masih menggantung, yang dikirim HANYA partisi awal
                            $qtyKirim = $qtyPesan - $bo->jumlah_kurang;
                            $statusTeks = 'parsial';
                        }
                    } else {
                        // NORMAL: Stok dari awal memang cukup
                        $qtyKirim = $qtyPesan;
                        $statusTeks = 'normal';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $detail->barang->nama_barang }}</strong>
                        
                        @if($statusTeks === 'parsial')
                            <span class="bo-note editable" contenteditable="true">*Catatan: Pengiriman Parsial Tahap 1. Sisa {{ $bo->jumlah_kurang }} unit menyusul via Back Order.</span>
                        @elseif($statusTeks === 'pelunasan')
                            <span class="bo-success editable" contenteditable="true">*Catatan: Pengiriman Tahap 2 (Pelunasan Kuantitas Back Order sejumlah {{ $bo->jumlah_kurang }} unit).</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $qtyPesan }} Pcs</td>
                    <td class="text-center" style="font-size: 16px;">
                        <strong>{{ $qtyKirim > 0 ? $qtyKirim . ' Pcs' : '-' }}</strong>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="signature">
            <tr>
                <td>Penerima,<br>(Customer)<br><br><br><div class="signature-line"></div></td>
                <td>Pengirim,<br><span class="editable" contenteditable="true" title="Ubah Nama Supir">(Nama Supir)</span><br><br><br><div class="signature-line"></div></td>
                <td>Disiapkan Oleh,<br><span class="editable" contenteditable="true" title="Ubah Nama Admin Gudang">({{ Auth::user()->name }})</span><br><br><br><div class="signature-line"></div></td>
            </tr>
        </table>
    </div>
</body>
</html>