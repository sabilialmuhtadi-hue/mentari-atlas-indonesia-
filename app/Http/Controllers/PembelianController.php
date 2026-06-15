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
use App\Models\ActivityLog;

class PembelianController extends Controller
{
    public function index()
    {
        $barangs = Barang::orderBy('kode_barang', 'asc')->get();
        // Tampilkan semua PO, yang paling baru di atas
        $riwayat = Pembelian::with('barang')->orderBy('created_at', 'asc')->get();
        $suppliers = Supplier::orderBy('id', 'asc')->get();
        
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

            // 2. Update HPP (harga_beli) di master Barang jika ada perubahan
            $barang = Barang::find($request->barang_id);
            if ($barang && $barang->harga_beli != $request->harga_beli_hpp) {
                $oldHpp = $barang->harga_beli;
                $newHpp = $request->harga_beli_hpp;
                
                $barang->harga_beli = $newHpp;
                $barang->save();

                // Catat perubahan HPP ke riwayat
                \App\Models\StockHistory::record(
                    $barang,
                    0, // tidak ada perubahan fisik stok
                    'edit_data',
                    $no_pembelian,
                    "Update HPP via PO: Rp " . number_format($oldHpp, 0, ',', '.') . " -> Rp " . number_format($newHpp, 0, ',', '.')
                );
            }

            // Utang Supplier ditunda sampai proses Sortir fisik barang (dipindahkan ke FASE 2)

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'TAMBAH PEMBELIAN',
                'description' => Auth::user()->name . ' membuat dokumen Purchase Order (PO) baru: ' . $no_pembelian,
                'ip_address' => request()->ip(),
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

            // 4. OTOMATISASI JURNAL UTANG SUPPLIER (Dibuat setelah barang tiba dan disortir)
            // Pastikan utang belum dibuat (untuk menghindari dobel pada PO lama)
            if (!Utang::where('pembelian_id', $pembelian->id)->exists()) {
                $noUtangJurnal = 'UTG-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                // Cari data master supplier untuk mengambil Termin Jatuh Tempo
                $supplierMaster = \App\Models\Supplier::where('nama_supplier', $pembelian->nama_supplier)->first();
                $jatuhTempoHari = $supplierMaster && $supplierMaster->jatuh_tempo_hari !== null 
                                    ? $supplierMaster->jatuh_tempo_hari 
                                    : 30; // Default fallback

                Utang::create([
                    'no_utang_jurnal'     => $noUtangJurnal,
                    'pembelian_id'        => $pembelian->id,
                    'total_utang'         => $pembelian->total_bayar,
                    'total_dibayar'       => 0,
                    'status_bayar'        => 'belum_bayar',
                    'tanggal_jatuh_tempo' => date('Y-m-d', strtotime('+' . $jatuhTempoHari . ' days')),
                ]);
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE STATUS PEMBELIAN',
                'description' => Auth::user()->name . ' menyelesaikan proses QC/Sortir untuk PO: ' . $pembelian->no_pembelian,
                'ip_address' => request()->ip(),
            ]);

            DB::commit();

            if ($request->has('potong_tagihan') && ($request->qty_rusak > 0 || $request->qty_kurang > 0)) {
                return back()->with('success', "Proses QC Selesai! Stok Bagus & Rusak telah di-update. (Draf Klaim Retur/Utang telah disiapkan dan menunggu persetujuan).");
            } else {
                return back()->with('success', "Proses QC Selesai! Semua stok bagus telah dimasukkan ke gudang.");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal Memproses Sortir: ' . $e->getMessage()]);
        }
    }
}