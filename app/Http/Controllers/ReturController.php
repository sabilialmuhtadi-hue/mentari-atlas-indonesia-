<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\Piutang;
use App\Models\StockHistory;
use App\Models\PembayaranPiutang;
use App\Models\Utang;
use App\Models\PembayaranUtang;
use App\Models\CreditNote;
use App\Models\BackOrder; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReturController extends Controller
{
    // ==========================================
    // AREA RETUR PENJUALAN (DARI CUSTOMER)
    // ==========================================
    
    public function penjualanIndex()
    {
        $penjualans = Penjualan::with('customer')->orderBy('id', 'desc')->get();
        $barangs = Barang::all();
        
        $returs = DB::table('returs')
            ->leftJoin('penjualans', 'returs.referensi_id', '=', 'penjualans.id')
            ->leftJoin('barangs', 'returs.barang_id', '=', 'barangs.id')
            ->where('returs.tipe', 'penjualan')
            ->select('returs.*', 'penjualans.no_so', 'barangs.nama_barang')
            ->orderBy('returs.id', 'asc')
            ->get()
            ->map(function($item) {
                $item->no_retur_jual = $item->no_retur ?? 'RE-'.$item->id;
                $item->qty_retur = $item->qty;
                $item->jenis_retur = $item->jenis_retur ?? 'fisik'; 
                $item->status_kondisi = $item->kondisi;
                $item->nominal_potongan = $item->nominal_potongan ?? 0;
                
                $item->penjualan = (object) ['no_so' => $item->no_so ?? 'N/A'];
                $item->barang = (object) ['nama_barang' => $item->nama_barang ?? 'N/A'];
                $item->created_at = Carbon::parse($item->created_at);
                return $item;
            });

        return view('warehouse.retur_penjualan', compact('penjualans', 'barangs', 'returs'));
    }

    public function getItemsSO($id)
    {
        $items = DB::table('penjualan_details')
            ->join('barangs', 'penjualan_details.barang_id', '=', 'barangs.id')
            ->where('penjualan_details.penjualan_id', $id)
            ->select('penjualan_details.*', 'barangs.nama_barang')
            ->get()
            ->map(function($item) {
                return [
                    'barang_id' => $item->barang_id,
                    'jumlah_diajukan' => $item->jumlah,
                    'barang' => [
                        'nama_barang' => $item->nama_barang
                    ]
                ];
            });

        return response()->json($items);
    }

    public function penjualanStore(Request $request)
    {
        $request->validate([
            'penjualan_id' => 'required',
            'barang_id' => 'required',
            'jenis_retur' => 'required|in:fisik,harga_credit_note', 
            'qty_retur' => 'required|integer|min:1',
            'status_kondisi' => 'required_if:jenis_retur,fisik|nullable|in:bagus,rusak',
            'aging_retur' => 'nullable|string', 
            'nominal_potongan' => 'nullable|numeric|min:0', 
            'alasan' => 'required|string'
        ]);

        DB::beginTransaction();
        try {
            $penjualan = Penjualan::find($request->penjualan_id);
            if (!$penjualan) {
                return redirect()->back()->withErrors(['error' => 'Data Penjualan tidak ditemukan.']);
            }
            $noSO = $penjualan->no_so;

            $detail = DB::table('penjualan_details')
                ->where('penjualan_id', $request->penjualan_id)
                ->where('barang_id', $request->barang_id)
                ->first();

            if (!$detail) {
                return redirect()->back()->withErrors(['error' => 'Barang tidak ditemukan dalam nota penjualan ini.']);
            }

            $totalReturSebelumnya = DB::table('returs')
                ->where('tipe', 'penjualan')
                ->where('referensi_id', $request->penjualan_id)
                ->where('barang_id', $request->barang_id)
                ->sum('qty');

            $sisaKapasitasRetur = $detail->jumlah - $totalReturSebelumnya;

            if ($request->qty_retur > $sisaKapasitasRetur) {
                return redirect()->back()->withErrors(['error' => "Batas retur terlampaui! Maksimal kuantitas yang masih bisa diretur untuk item ini adalah {$sisaKapasitasRetur} Pcs."]);
            }

            $noReturAuto = 'RE-JUAL-' . date('Ymd') . rand(100, 999);
            
            // -------------------------------------------------------------
            // LOGIKA HITUNG CREDIT NOTE & DENDA AGING
            // -------------------------------------------------------------
            $nominalPotonganAwal = $request->jenis_retur === 'harga_credit_note' 
                ? (float) $request->nominal_potongan 
                : ((float) $request->qty_retur * (float) $detail->harga_satuan);

            $nominalTerpakaiReturs = $nominalPotonganAwal;
            $teksAlasan = $request->alasan;

            if ($request->jenis_retur === 'fisik' && strtolower($request->status_kondisi) === 'rusak') {
                if ($request->aging_retur === '46_90') {
                    $denda = $nominalPotonganAwal * 0.10;
                    $nominalTerpakaiReturs = $nominalPotonganAwal - $denda;
                    $teksAlasan = $request->alasan . ' [Retur 46-90 Hari: Kena charge denda 10%]';
                } elseif ($request->aging_retur === '91_135') {
                    $denda = $nominalPotonganAwal * 0.30;
                    $nominalTerpakaiReturs = $nominalPotonganAwal - $denda;
                    $teksAlasan = $request->alasan . ' [Retur >90 Hari: Kena charge denda 30%]';
                }
            }

            DB::table('returs')->insert([
                'tipe' => 'penjualan',
                'referensi_id' => $request->penjualan_id,
                'barang_id' => $request->barang_id,
                'qty' => $request->qty_retur,
                'nominal_potongan' => $nominalTerpakaiReturs,
                'jenis_retur' => $request->jenis_retur,
                'kondisi' => $request->jenis_retur === 'fisik' ? $request->status_kondisi : 'tidak_mempengaruhi',
                'alasan' => $teksAlasan,
                'no_retur' => $noReturAuto,
                'status_retur' => 'completed', // Retur Jual selalu langsung selesai
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $nomorCN = 'CN-JUAL-' . date('Ymd') . rand(100, 999);
            CreditNote::create([
                'nomor_cn' => $nomorCN,
                'tipe' => 'penjualan',
                'referensi_id' => $request->penjualan_id,
                'nominal' => $nominalTerpakaiReturs,
                'keterangan' => 'Credit Note terbit dari klaim (' . $request->jenis_retur . '): ' . $teksAlasan,
            ]);

            if ($request->jenis_retur === 'fisik') {
                $barang = Barang::find($request->barang_id);
                if ($barang) {
                    if (strtolower($request->status_kondisi) === 'bagus') {
                        $stokSebelumnya = $barang->stok_akhir;
                        $barang->update([
                            'stok_akhir' => $barang->stok_akhir + $request->qty_retur,
                            'barang_masuk' => $barang->barang_masuk + $request->qty_retur
                        ]);

                        StockHistory::record(
                            $barang,
                            $request->qty_retur, 
                            'return_customer',
                            $noReturAuto . ' / ' . $noSO, 
                            'Retur fisik customer (BAGUS). Stok ditarik kembali ke sistem gudang utama. [CN: ' . $nomorCN . ']',
                            $stokSebelumnya
                        );
                    } else {
                        $stokRusakSebelumnya = $barang->stok_rusak ?? 0;
                        $barang->update([
                            'stok_rusak' => $stokRusakSebelumnya + $request->qty_retur
                        ]);

                        StockHistory::record(
                            $barang,
                            $request->qty_retur, 
                            'return_customer',
                            $noReturAuto . ' / ' . $noSO, 
                            'Retur fisik customer (RUSAK). Barang otomatis dialokasikan ke Stok Rusak. [CN: ' . $nomorCN . ']',
                            $stokRusakSebelumnya
                        );
                    }
                }
            }

            $piutang = Piutang::where('penjualan_id', $request->penjualan_id)->first();
            if ($piutang) {
                $nominalTerpakaiPiutang = $nominalTerpakaiReturs;
                $piutang->total_tagihan = max(0, (float) $piutang->total_tagihan - $nominalTerpakaiPiutang);
                $sisa = (float) $piutang->total_tagihan - (float) $piutang->total_dibayar;

                if ($sisa <= 0) {
                    $piutang->status_bayar = 'lunas';
                    $piutang->total_tagihan = 0;
                } elseif ((float) $piutang->total_dibayar > 0) {
                    $piutang->status_bayar = 'cicil';
                } else {
                    $piutang->status_bayar = 'belum_bayar';
                }

                $piutang->save();

                PembayaranPiutang::create([
                    'piutang_id' => $piutang->id,
                    'jumlah_bayar' => (float) $nominalTerpakaiPiutang,
                    'tanggal_bayar' => Carbon::now(),
                    'metode_pembayaran' => 'Retur Customer / Credit Note',
                    'diterima_oleh' => Auth::id(),
                    'keterangan' => 'Retur otomatis mengurangi tagihan [' . $noReturAuto . ']: ' . $teksAlasan,
                    'bukti_bayar' => null,
                ]);
            }

            DB::commit();
            return redirect()->back()->with('success', 'Klaim Retur Penjualan & Credit Note berhasil diproses.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Gagal Memproses Retur Penjualan: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // AREA RETUR PEMBELIAN (KE SUPPLIER)
    // ==========================================
    
    public function pembelianIndex()
    {
        $pembelians = Pembelian::orderBy('id', 'desc')->get();
        $barangs = Barang::all();
        
        $returs = DB::table('returs')
            ->leftJoin('pembelians', 'returs.referensi_id', '=', 'pembelians.id')
            ->leftJoin('barangs', 'returs.barang_id', '=', 'barangs.id')
            ->where('returs.tipe', 'pembelian')
            ->select('returs.*', 'pembelians.no_pembelian', 'pembelians.nama_supplier', 'barangs.nama_barang')
            ->orderBy('returs.id', 'asc')
            ->get()
            ->map(function($item) {
                $item->no_retur_beli = $item->no_retur ?? 'RB-'.$item->id;
                $item->qty_retur = $item->qty;
                $item->jenis_retur = $item->jenis_retur ?? 'fisik'; 
                $item->status_kondisi = $item->kondisi;
                $item->nominal_potongan = $item->nominal_potongan ?? 0;
                $item->status_retur = $item->status_retur ?? 'completed'; // Draf / Selesai
                
                $item->pembelian = (object) ['no_pembelian' => $item->no_pembelian ?? 'N/A'];
                $item->nama_supplier = $item->nama_supplier ?? 'N/A';
                $item->barang = (object) ['nama_barang' => $item->nama_barang ?? 'N/A'];
                $item->created_at = Carbon::parse($item->created_at);
                return $item;
            });

        return view('warehouse.retur_pembelian', compact('pembelians', 'barangs', 'returs'));
    }

    public function getItemsPO($id)
    {
        $items = DB::table('pembelians')
            ->join('barangs', 'pembelians.barang_id', '=', 'barangs.id')
            ->where('pembelians.id', $id)
            ->select('pembelians.*', 'barangs.nama_barang')
            ->get()
            ->map(function($item) {
                return [
                    'barang_id' => $item->barang_id,
                    'jumlah_diajukan' => $item->jumlah_beli,
                    'barang' => [
                        'nama_barang' => $item->nama_barang
                    ]
                ];
            });

        return response()->json($items);
    }

    public function pembelianStore(Request $request)
    {
        $request->validate([
            'pembelian_id' => 'required',
            'barang_id' => 'required',
            'jenis_retur' => 'required|in:fisik,harga_debit_note', 
            'qty_retur' => 'required|integer|min:1',
            'status_kondisi' => 'required_if:jenis_retur,fisik|nullable|in:bagus,rusak', 
            'nominal_potongan' => 'nullable|numeric|min:0', 
            'alasan' => 'required|string'
        ]);

        $barang = Barang::findOrFail($request->barang_id);

        // LOGIKA PENGECEKAN STOK (Bagus vs Rusak) SEBELUM DIRETUR KE SUPPLIER
        if ($request->jenis_retur === 'fisik') {
            $stokTersedia = ($request->status_kondisi === 'rusak') ? ($barang->stok_rusak ?? 0) : ($barang->stok_akhir ?? 0);
            
            if ($stokTersedia < $request->qty_retur) {
                return redirect()->back()->withErrors([
                    'error' => "Gagal Retur Fisik! Sisa stok ".ucfirst($request->status_kondisi)." '{$barang->nama_barang}' di gudang hanya ({$stokTersedia} pcs), tidak cukup untuk diretur."
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $pembelian = Pembelian::find($request->pembelian_id);

            if (!$pembelian) {
                return redirect()->back()->withErrors(['error' => 'Pembelian tidak ditemukan.']);
            }
            $noPO = $pembelian->no_pembelian;

            // Karena input Retur Manual, langsung eksekusi sebagai "Completed"
            $noReturAuto = 'RE-BELI-' . date('Ymd') . rand(100, 999);
            
            $nominalPotongan = ($request->jenis_retur === 'harga_debit_note' && !is_null($request->nominal_potongan))
                ? (float) $request->nominal_potongan 
                : ((float) $request->qty_retur * (float) $pembelian->harga_beli_hpp);

            DB::table('returs')->insert([
                'no_retur' => $noReturAuto,
                'tipe' => 'pembelian',
                'jenis_retur' => $request->jenis_retur,
                'referensi_id' => $request->pembelian_id,
                'barang_id' => $request->barang_id,
                'qty' => $request->qty_retur,
                'kondisi' => $request->jenis_retur === 'fisik' ? $request->status_kondisi : 'tidak_mempengaruhi',
                'nominal_potongan' => $nominalPotongan,
                'status_retur' => 'completed', // Retur Manual langsung selesai
                'alasan' => $request->alasan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $nomorDN = 'DN-BELI-' . date('Ymd') . rand(100, 999);
            CreditNote::create([
                'nomor_cn' => $nomorDN, 
                'tipe' => 'pembelian',
                'referensi_id' => $request->pembelian_id,
                'nominal' => $nominalPotongan,
                'keterangan' => 'Debit Note terbit akibat klaim retur pembelian ke supplier: ' . $request->alasan,
            ]);

            // MANAJEMEN FISIK GUDANG (PINTAR & SELEKTIF MEMOTONG STOK)
            if ($request->jenis_retur === 'fisik') {
                $statusKondisi = $request->status_kondisi;
                
                if ($statusKondisi === 'rusak') {
                    $stokRusakSebelumnya = $barang->stok_rusak ?? 0;
                    $barang->update(['stok_rusak' => $stokRusakSebelumnya - $request->qty_retur]);
                    
                    StockHistory::record(
                        $barang, -$request->qty_retur, 'return_supplier', $noReturAuto . ' / ' . $noPO, 
                        'Retur fisik BARANG RUSAK ke supplier. [DN: '.$nomorDN.']', $stokRusakSebelumnya
                    );
                } else {
                    $stokSebelumnya = $barang->stok_akhir;
                    $barang->update([
                        'stok_akhir' => $barang->stok_akhir - $request->qty_retur,
                        'barang_keluar' => $barang->barang_keluar + $request->qty_retur,
                    ]);
                    
                    StockHistory::record(
                        $barang, -$request->qty_retur, 'return_supplier', $noReturAuto . ' / ' . $noPO, 
                        'Retur fisik BARANG BAGUS ke supplier. [DN: '.$nomorDN.']', $stokSebelumnya
                    );
                }
            }

            // PERUBAHAN AKUNTANSI: Mengisi kolom potongan_dn BUKAN tabel pembayaran tunai
            $utang = Utang::where('pembelian_id', $request->pembelian_id)->first();
            if ($utang) {
                // Tambahkan nilai ke kolom potongan DN agar terpisah dari pembayaran fisik
                $utang->potongan_dn = $utang->potongan_dn + $nominalPotongan;
                
                $sisaTagihan = $utang->total_utang - $utang->potongan_dn - $utang->total_dibayar;
                if ($sisaTagihan <= 0) {
                    $utang->status_bayar = 'lunas';
                } elseif ($utang->total_dibayar > 0) {
                    $utang->status_bayar = 'cicil';
                } else {
                    $utang->status_bayar = 'belum_bayar';
                }
                $utang->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'Retur Manual & Debit Note berhasil diproses. Beban utang ke supplier berhasil dipotong secara sah!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Gagal Memproses Retur Pembelian: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // FUNGSI EKSEKUSI TOMBOL "RETURN SEKARANG" (DARI QC)
    // ==========================================
    public function eksekusiReturPending(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Ambil data retur yang masih pending
            $returData = DB::table('returs')->where('id', $id)->first();
            
            if (!$returData || $returData->status_retur !== 'pending') {
                return redirect()->back()->withErrors(['error' => 'Klaim Retur tidak valid atau sudah dieksekusi sebelumnya.']);
            }

            $pembelian = Pembelian::find($returData->referensi_id);
            $barang = Barang::find($returData->barang_id);

            // 1. Eksekusi Potong Stok Rusak (Jika jenis retur fisik / RMA)
            if ($returData->jenis_retur === 'fisik' && strtolower($returData->kondisi) === 'rusak') {
                $stokRusakSekarang = $barang->stok_rusak ?? 0;
                
                // Pastikan stok rusak di gudang cukup untuk dikembalikan
                // Jika kurang, artinya barang rusak sudah terpakai/terbuang tanpa prosedur
                if ($stokRusakSekarang < $returData->qty) {
                    // Jika stok kurang, potong seadanya agar tidak minus
                    $qtyYangBisaDipotong = $stokRusakSekarang;
                    $barang->update(['stok_rusak' => 0]);
                } else {
                    $qtyYangBisaDipotong = $returData->qty;
                    $barang->update(['stok_rusak' => $stokRusakSekarang - $returData->qty]);
                }

                // Catat di kartu stok
                if ($qtyYangBisaDipotong > 0) {
                    StockHistory::record(
                        $barang, -$qtyYangBisaDipotong, 'return_supplier', $returData->no_retur . ' / ' . ($pembelian->no_pembelian ?? '-'), 
                        'Eksekusi RMA. Fisik BARANG RUSAK dari hasil QC telah dikirim ke supplier.', $stokRusakSekarang
                    );
                }
            }

            // 2. Terbitkan Debit Note Resmi
            $nomorDN = 'DN-QC-' . date('Ymd') . rand(100, 999);
            CreditNote::create([
                'nomor_cn' => $nomorDN, 
                'tipe' => 'pembelian',
                'referensi_id' => $returData->referensi_id,
                'nominal' => $returData->nominal_potongan,
                'keterangan' => 'Eksekusi Debit Note dari QC: ' . $returData->alasan,
            ]);

            // 3. Eksekusi Pemotongan Utang (Adjustment, BUKAN Kas Keluar)
            $utang = Utang::where('pembelian_id', $returData->referensi_id)->first();
            if ($utang) {
                // Tambahkan nilai ke kolom khusus potongan_dn
                $utang->potongan_dn = $utang->potongan_dn + $returData->nominal_potongan;
                
                $sisaTagihan = $utang->total_utang - $utang->potongan_dn - $utang->total_dibayar;
                if ($sisaTagihan <= 0) {
                    $utang->status_bayar = 'lunas';
                } elseif ($utang->total_dibayar > 0) {
                    $utang->status_bayar = 'cicil';
                } else {
                    $utang->status_bayar = 'belum_bayar';
                }
                $utang->save();
            }

            // 4. Ubah status retur menjadi Completed
            DB::table('returs')->where('id', $id)->update([
                'status_retur' => 'completed',
                'updated_at' => now()
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'RMA Berhasil Dieksekusi! Fisik barang rusak telah dipotong, dan tagihan utang supplier otomatis dikurangi senilai Rp '.number_format($returData->nominal_potongan,0,',','.'));

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'Gagal Mengeksekusi RMA Retur: ' . $e->getMessage()]);
        }
    }
}