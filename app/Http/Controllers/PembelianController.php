<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembelian;
use App\Models\Barang;
use App\Models\Utang;
use App\Models\Supplier;
use App\Models\StockHistory;
use App\Models\PembayaranUtang;
use App\Models\CreditNote;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PembelianController extends Controller
{
    public function index()
    {
        $barangs = Barang::orderBy('nama_barang', 'asc')->get();
        // Tampilkan semua PO, yang paling baru di atas
        $riwayat = Pembelian::with('barang')->orderBy('created_at', 'asc')->get();
        $suppliers = Supplier::orderBy('nama_supplier', 'asc')->get();
        
        return view('pembelian.index', compact('barangs', 'riwayat', 'suppliers'));
    }

    // FASE 1: BUAT PURCHASE ORDER (TIDAK MENAMBAH STOK)
    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier'  => 'required|string|max:255',
            'barang_id'      => 'required|exists:barangs,id',
            'jumlah_beli'    => 'required|integer|min:1',
            'harga_beli_hpp' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $datePrefix = 'PO-' . date('Ymd') . '-';
            $lastPo = Pembelian::where('no_pembelian', 'like', $datePrefix . '%')->orderBy('no_pembelian', 'desc')->first();
            $newPoNum = $lastPo ? intval(substr($lastPo->no_pembelian, -4)) + 1 : 1;
            $no_pembelian = $datePrefix . str_pad($newPoNum, 4, '0', STR_PAD_LEFT);

            $total_bayar = $request->jumlah_beli * $request->harga_beli_hpp;

            // 1. Simpan data PO dengan status pending
            $pembelian = Pembelian::create([
                'no_pembelian'   => $no_pembelian,
                'nama_supplier'  => strtoupper(trim($request->nama_supplier)),
                'barang_id'      => $request->barang_id,
                'jumlah_beli'    => $request->jumlah_beli,
                'harga_beli_hpp' => $request->harga_beli_hpp,
                'total_bayar'    => $total_bayar,
                'tanggal_beli'   => date('Y-m-d'),
                'status_barang'  => 'pending', // <--- STOK TERTATAHAN DI SINI
            ]);

            // 2. OTOMATISASI JURNAL UTANG SUPPLIER (Utuh 100%)
            $noUtangJurnal = 'UTG-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            Utang::create([
                'no_utang_jurnal'     => $noUtangJurnal,
                'pembelian_id'        => $pembelian->id,
                'total_utang'         => $total_bayar,
                'total_dibayar'       => 0,
                'status_bayar'        => 'belum_bayar',
                'tanggal_jatuh_tempo' => date('Y-m-d', strtotime('+30 days')),
            ]);

            DB::commit();
            return back()->with('success', "PO {$no_pembelian} berhasil dibuat. Silakan lakukan proses SORTIR saat fisik barang tiba di gudang!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // FASE 2: QUALITY CONTROL & FINALISASI STOK
    public function prosesSortir(Request $request, $id)
    {
        $request->validate([
            'qty_bagus'  => 'required|integer|min:0',
            'qty_rusak'  => 'required|integer|min:0',
            'qty_kurang' => 'required|integer|min:0',
        ]);

        $pembelian = Pembelian::findOrFail($id);
        
        if ($pembelian->status_barang === 'selesai') {
            return back()->withErrors(['error' => 'Gagal! PO ini sudah disortir sebelumnya.']);
        }

        $totalCek = $request->qty_bagus + $request->qty_rusak + $request->qty_kurang;
        if ($totalCek !== $pembelian->jumlah_beli) {
            return back()->withErrors(['error' => "Total sortir ({$totalCek}) tidak sama dengan jumlah PO awal ({$pembelian->jumlah_beli}). Pastikan perhitungannya pas!"]);
        }

        try {
            DB::beginTransaction();

            // 1. Update Status Pembelian
            $pembelian->update([
                'status_barang' => 'selesai',
                'qty_bagus'     => $request->qty_bagus,
                'qty_rusak'     => $request->qty_rusak,
                'qty_kurang'    => $request->qty_kurang,
            ]);

            $barang = Barang::findOrFail($pembelian->barang_id);
            $hargaBeliBaru = $pembelian->harga_beli_hpp;

            // 2. EKSEKUSI STOK FISIK
            if ($request->qty_bagus > 0) {
                $stokAkhirLama = $barang->stok_akhir ?? 0;
                $barang->update([
                    'barang_masuk' => $barang->barang_masuk + $request->qty_bagus,
                    'stok_akhir'   => $stokAkhirLama + $request->qty_bagus,
                    'harga_beli'   => $hargaBeliBaru // UPDATE HPP LATEST PRICE
                ]);
                StockHistory::record($barang, $request->qty_bagus, 'purchase', $pembelian->no_pembelian, 'Penerimaan QC: Barang Bagus.', $stokAkhirLama);
            }

            // PERUBAHAN KRUSIAL: Barang rusak masuk ke karantina (stok_rusak bertambah, siap diretur)
            if ($request->qty_rusak > 0) {
                $stokRusakLama = $barang->stok_rusak ?? 0;
                $barang->update([
                    'stok_rusak' => $stokRusakLama + $request->qty_rusak
                ]);
                StockHistory::record($barang, $request->qty_rusak, 'purchase', $pembelian->no_pembelian, 'Penerimaan QC: Barang Cacat/Rusak.', $stokRusakLama);
            }

            // 3. AUTO-DEBIT NOTE: HANYA BUAT DRAF (PENDING), TIDAK MEMOTONG UTANG
            if ($request->has('potong_tagihan') && ($request->qty_rusak > 0 || $request->qty_kurang > 0)) {
                $totalQtyBermasalah = $request->qty_rusak + $request->qty_kurang;
                $nominalPotongan = $totalQtyBermasalah * $hargaBeliBaru;
                
                $noReturAuto = 'RE-QC-' . date('Ymd') . rand(100, 999);
                $alasan = "Klaim Otomatis Hasil QC: Terdapat {$request->qty_rusak} rusak, {$request->qty_kurang} kurang/hilang dari total order {$pembelian->jumlah_beli} Pcs.";

                // Buat Data Retur dengan status PENDING (Belum dieksekusi potongannya)
                // Jenisnya kita set 'fisik' khusus untuk rusak, agar nanti saat dieksekusi, stok rusak berkurang.
                DB::table('returs')->insert([
                    'no_retur' => $noReturAuto, 
                    'tipe' => 'pembelian', 
                    'jenis_retur' => ($request->qty_rusak > 0) ? 'fisik' : 'harga_debit_note', // Pintar: Jika ada yang rusak, wajib retur fisik
                    'referensi_id' => $pembelian->id, 
                    'barang_id' => $barang->id, 
                    'qty' => $totalQtyBermasalah,
                    'kondisi' => ($request->qty_rusak > 0) ? 'rusak' : 'tidak_mempengaruhi', 
                    'nominal_potongan' => $nominalPotongan,
                    'status_retur' => 'pending', // <--- MASUK KARANTINA (DRAF)
                    'alasan' => $alasan, 
                    'created_at' => now(), 
                    'updated_at' => now(),
                ]);

                // Credit Note dan Pemotongan Utang DITANGGUHKAN sampai tombol "Return Sekarang" diklik.
            }

            DB::commit();
            return back()->with('success', "Proses QC Selesai! Stok Bagus & Rusak telah di-update. (Draf Klaim Retur/Utang telah disiapkan dan menunggu persetujuan).");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal Memproses Sortir: ' . $e->getMessage()]);
        }
    }
}