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
        $returQuery = DB::table('returs')
            ->where('tipe', 'penjualan')
            ->selectRaw('referensi_id as penjualan_id, barang_id, SUM(qty) as total_qty_retur, SUM(nominal_potongan) as total_potongan_cn')
            ->groupBy('referensi_id', 'barang_id');

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
            SUM((pd.subtotal - COALESCE(r.total_potongan_cn, 0)) - ((pd.jumlah - COALESCE(r.total_qty_retur, 0)) * CASE WHEN pd.hpp > 0 THEN pd.hpp ELSE COALESCE(b.harga_beli, 0) END)) as total_laba
        ';

        $labaSales = (clone $baseQuery)->join('users as u', 'p.user_id', '=', 'u.id')->selectRaw("u.name as nama, " . $selectRawLogic)->groupBy('u.id', 'u.name')->orderBy('total_laba', 'desc')->get();
        $labaProduk = (clone $baseQuery)->selectRaw("b.kode_barang, b.nama_barang as nama, " . $selectRawLogic)->groupBy('b.id', 'b.kode_barang', 'b.nama_barang')->orderBy('total_laba', 'desc')->get();
        $labaMerek = (clone $baseQuery)->selectRaw("b.merek as nama, " . $selectRawLogic)->groupBy('b.merek')->orderBy('total_laba', 'desc')->get();
        $labaCustomer = (clone $baseQuery)->join('customers as c', 'p.customer_id', '=', 'c.id')->selectRaw("c.nama_customer as nama, " . $selectRawLogic)->groupBy('c.id', 'c.nama_customer')->orderBy('total_laba', 'desc')->get();

        // 3. PENGHITUNGAN BEBAN QC TERSEMBUNYI
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
            'labaSales', 'labaProduk', 'labaMerek', 'labaCustomer',
            'grandTotalPendapatan', 'grandTotalHpp', 'grandTotalLabaKotor',
            'totalUtang', 'totalPiutang', 'grandTotalLabaBersih',
            'filter_tipe', 'start_date', 'end_date'
        ));
    }
}