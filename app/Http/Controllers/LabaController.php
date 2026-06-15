<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LabaController extends Controller
{
    public function index(Request $request)
    {
        $statusValid = ['ready_to_invoice', 'completed'];

        $filter_tipe = $request->input('filter_tipe', 'semua');
        $start_date = null;
        $end_date = null;

        if ($filter_tipe === 'bulan_ini') {
            $start_date = Carbon::now()->startOfMonth()->format('Y-m-d');
            $end_date = Carbon::now()->endOfMonth()->format('Y-m-d');
        } elseif ($filter_tipe === 'tahun_ini') {
            $start_date = Carbon::now()->startOfYear()->format('Y-m-d');
            $end_date = Carbon::now()->endOfYear()->format('Y-m-d');
        } elseif ($filter_tipe === 'custom') {
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            if (!$start_date || !$end_date) {
                $filter_tipe = 'semua'; 
            }
        }

        // 1. SUB-QUERY: RETUR CUSTOMER
        $returQuery = DB::table('returs as r')
            ->join('penjualan_details as pd', function($join) {
                $join->on('r.referensi_id', '=', 'pd.penjualan_id')
                     ->on('r.barang_id', '=', 'pd.barang_id');
            })
            ->where('r.tipe', 'penjualan')
            ->selectRaw('r.referensi_id as penjualan_id, r.barang_id, SUM(r.qty) as total_qty_retur, SUM(r.nominal_potongan) as total_potongan_cn, SUM(r.qty * pd.harga_satuan) as total_omzet_retur')
            ->groupBy('r.referensi_id', 'r.barang_id');

        // 2. QUERY UTAMA: PENJUALAN
        $baseQuery = DB::table('penjualan_details as pd')
            ->join('penjualans as p', 'pd.penjualan_id', '=', 'p.id')
            ->join('barangs as b', 'pd.barang_id', '=', 'b.id')
            ->leftJoinSub($returQuery, 'r', function($join) {
                $join->on('pd.penjualan_id', '=', 'r.penjualan_id')->on('pd.barang_id', '=', 'r.barang_id');
            })->whereIn('p.status', $statusValid);

        if ($filter_tipe !== 'semua' && $start_date && $end_date) {
            $baseQuery->whereBetween(DB::raw('DATE(p.created_at)'), [$start_date, $end_date]);
        }

        $selectRawLogic = '
            SUM(pd.jumlah - COALESCE(r.total_qty_retur, 0)) as total_qty, 
            SUM(pd.subtotal - COALESCE(r.total_potongan_cn, 0)) as total_pendapatan, 
            SUM((pd.jumlah - COALESCE(r.total_qty_retur, 0)) * CASE WHEN pd.hpp > 0 THEN pd.hpp ELSE COALESCE(b.harga_beli, 0) END) as total_hpp, 
            SUM((pd.subtotal - COALESCE(r.total_potongan_cn, 0)) - ((pd.jumlah - COALESCE(r.total_qty_retur, 0)) * CASE WHEN pd.hpp > 0 THEN pd.hpp ELSE COALESCE(b.harga_beli, 0) END)) as total_laba,
            SUM(pd.jumlah) as qty_awal,
            SUM(pd.subtotal) as omzet_awal,
            SUM(pd.jumlah * CASE WHEN pd.hpp > 0 THEN pd.hpp ELSE COALESCE(b.harga_beli, 0) END) as hpp_awal,
            SUM(COALESCE(r.total_qty_retur, 0)) as qty_retur,
            SUM(COALESCE(r.total_potongan_cn, 0)) as potongan_cn,
            SUM(COALESCE(r.total_omzet_retur, 0)) as omzet_retur
        ';

        $labaSales = (clone $baseQuery)->join('users as u', 'p.user_id', '=', 'u.id')->selectRaw("u.name as nama, " . $selectRawLogic)->groupBy('u.id', 'u.name')->orderBy('total_laba', 'desc')->get();
        $labaProduk = (clone $baseQuery)->selectRaw("b.kode_barang, b.nama_barang as nama, " . $selectRawLogic)->groupBy('b.id', 'b.kode_barang', 'b.nama_barang')->orderBy('total_laba', 'desc')->get();
        $labaMerek = (clone $baseQuery)->selectRaw("b.merek as nama, " . $selectRawLogic)->groupBy('b.merek')->orderBy('total_laba', 'desc')->get();
        $labaCustomer = (clone $baseQuery)->join('customers as c', 'p.customer_id', '=', 'c.id')->selectRaw("c.nama_customer as nama, " . $selectRawLogic)->groupBy('c.id', 'c.nama_customer')->orderBy('total_laba', 'desc')->get();
        $labaTransaksi = (clone $baseQuery)->join('customers as c_trans', 'p.customer_id', '=', 'c_trans.id')->selectRaw("p.no_so, p.created_at as tanggal_so, c_trans.nama_customer as nama, " . $selectRawLogic)->groupBy('p.id', 'p.no_so', 'p.created_at', 'c_trans.nama_customer')->orderBy('total_laba', 'desc')->get();

        // 3. QUERY PEMBELIAN (Untuk tab baru: Per Pembelian)
        $returPembelianQuery = DB::table('returs as r')
            ->where('r.tipe', 'pembelian')
            ->selectRaw('r.referensi_id as pembelian_id, r.barang_id, SUM(r.qty) as total_qty_retur, SUM(r.nominal_potongan) as total_potongan_cn')
            ->groupBy('r.referensi_id', 'r.barang_id');

        $basePembelianQuery = DB::table('pembelians as p')
            ->leftJoinSub($returPembelianQuery, 'r', function($join) {
                $join->on('p.id', '=', 'r.pembelian_id')->on('p.barang_id', '=', 'r.barang_id');
            })
            ->where('p.status_barang', 'selesai'); // HANYA PO YANG SUDAH DISORTIR (QC)

        if ($filter_tipe !== 'semua' && $start_date && $end_date) {
            $basePembelianQuery->whereBetween(DB::raw('DATE(p.tanggal_beli)'), [$start_date, $end_date]);
        }

        $selectPembelianLogic = '
            SUM(p.jumlah_beli) as qty_awal,
            SUM(p.total_bayar) as pengeluaran_awal,
            SUM(COALESCE(r.total_qty_retur, 0)) as qty_retur,
            SUM(COALESCE(r.total_potongan_cn, 0)) as potongan_cn,
            SUM(COALESCE(r.total_qty_retur, 0) * p.harga_beli_hpp) as nilai_retur_asli,
            SUM(p.jumlah_beli - COALESCE(r.total_qty_retur, 0)) as qty_akhir,
            SUM(p.total_bayar - COALESCE(r.total_potongan_cn, 0)) as pengeluaran_akhir
        ';

        $labaPembelian = (clone $basePembelianQuery)->selectRaw("p.no_pembelian as no_po, p.tanggal_beli as tanggal_po, p.nama_supplier as nama, " . $selectPembelianLogic)
            ->groupBy('p.no_pembelian', 'p.tanggal_beli', 'p.nama_supplier')
            ->orderBy('pengeluaran_akhir', 'desc')
            ->get();

        $labaSupplier = (clone $basePembelianQuery)->selectRaw("p.nama_supplier as nama, " . $selectPembelianLogic)
            ->groupBy('p.nama_supplier')
            ->orderBy('qty_retur', 'desc')
            ->get();

        // 4. PENGHITUNGAN BEBAN QC TERSEMBUNYI
        $queryQC = DB::table('pembelians')->where(function($q) { $q->where('qty_rusak', '>', 0)->orWhere('qty_kurang', '>', 0); });
        if ($filter_tipe !== 'semua' && $start_date && $end_date) { $queryQC->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date]); }
        $bebanQCKotor = $queryQC->sum(DB::raw('(qty_rusak + qty_kurang) * harga_beli_hpp'));

        // PERBAIKAN FIXED: Sekarang mengambil data klaim DN dari tabel 'credit_notes' dengan kolom 'nominal'
        $queryRecovery = DB::table('credit_notes')->where('tipe', 'pembelian');
        if ($filter_tipe !== 'semua' && $start_date && $end_date) { $queryRecovery->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date]); }
        $recoveryRusak = $queryRecovery->sum('nominal');

        // Rugi bersih operasional gudang (Akan bernilai 0 jika semua barang rusak berhasil di-DN)
        $totalPenyusutanBarang = max(0, $bebanQCKotor - $recoveryRusak);

        // 4. KONDISI UTANG & PIUTANG (NILAI BERSIH TRANSAKSI)
        $queryUtang = DB::table('utangs');
        $queryPiutang = DB::table('piutangs'); 
        
        if ($filter_tipe !== 'semua' && $start_date && $end_date) {
            $queryUtang->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date]);
            $queryPiutang->whereBetween(DB::raw('DATE(created_at)'), [$start_date, $end_date]);
        }
        
        $totalUtang = $queryUtang->sum(DB::raw('total_utang - potongan_dn'));
        $totalPiutang = $queryPiutang->sum('total_tagihan');

        // 5. KALKULASI GRAND TOTAL
        $grandTotalPendapatan = $labaProduk->sum('total_pendapatan');
        $grandTotalHpp = $labaProduk->sum('total_hpp');
        $grandTotalLabaKotor = $labaProduk->sum('total_laba');
        
        // Nilai Laba Bersih Akhir yang sesungguhnya
        $grandTotalLabaBersih = $grandTotalLabaKotor - $totalPenyusutanBarang;

        return view('laba.index', compact(
            'labaSales', 'labaProduk', 'labaMerek', 'labaCustomer', 'labaTransaksi', 'labaPembelian', 'labaSupplier',
            'grandTotalPendapatan', 'grandTotalHpp', 'grandTotalLabaKotor',
            'totalUtang', 'totalPiutang', 'grandTotalLabaBersih',
            'filter_tipe', 'start_date', 'end_date'
        ));
    }
}