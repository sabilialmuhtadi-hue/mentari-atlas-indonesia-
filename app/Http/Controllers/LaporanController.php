<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;

class LaporanController extends Controller
{
    /**
     * Menampilkan halaman utama pusat laporan (Dasbor Hub).
     */
    public function index()
    {
        return view('laporan.hub');
    }

    /**
     * Memproses filter parameter dan menghasilkan output laporan (PDF/Excel).
     */
    public function generate(Request $request)
    {
        $kategori = $request->kategori_laporan;
        $periode = $request->periode;
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $format = $request->format_export;
        $groupBy = $request->group_by ?? [];

        // =========================================================================
        // 1. PENGATURAN PERIODE WAKTU (DATE FILTER)
        // =========================================================================
        $qStart = null;
        $qEnd = null;
        $periodeLabel = "Semua Waktu";

        if ($periode === 'bulan_ini') {
            $qStart = Carbon::now()->startOfMonth()->format('Y-m-d');
            $qEnd = Carbon::now()->endOfMonth()->format('Y-m-d');
            $periodeLabel = "Bulan Ini (" . Carbon::now()->translatedFormat('F Y') . ")";
        } elseif ($periode === 'tahun_ini') {
            $qStart = Carbon::now()->startOfYear()->format('Y-m-d');
            $qEnd = Carbon::now()->endOfYear()->format('Y-m-d');
            $periodeLabel = "Tahun Ini (" . Carbon::now()->format('Y') . ")";
        } elseif ($periode === 'custom' && $start_date && $end_date) {
            $qStart = $start_date;
            $qEnd = $end_date;
            $periodeLabel = Carbon::parse($start_date)->format('d/m/Y') . " s/d " . Carbon::parse($end_date)->format('d/m/Y');
        }

        // Closure helper untuk mempersingkat filter tanggal di Query Builder
        $applyDateFilter = function($query, $column) use ($qStart, $qEnd) {
            if ($qStart && $qEnd) {
                $query->whereBetween(DB::raw("DATE($column)"), [$qStart, $qEnd]);
            }
        };

        // =========================================================================
        // 2. EKSEKUSI QUERY DATABASE BERDASARKAN KATEGORI (100% SINKRON)
        // URUTAN: TERLAMA KE TERBARU ('asc')
        // =========================================================================
        $data = collect([]);
        $judulLaporan = "Laporan Custom";

        if ($kategori == 'penjualan') {
            $judulLaporan = "Laporan Penjualan (Sales Order)";
            $q = DB::table('penjualan_details as pd')
                ->join('penjualans as p', 'pd.penjualan_id', '=', 'p.id')
                ->join('barangs as b', 'pd.barang_id', '=', 'b.id')
                ->leftJoin('customers as c', 'p.customer_id', '=', 'c.id')
                ->leftJoin('users as u', 'p.user_id', '=', 'u.id')
                ->whereIn('p.status_approval', ['disetujui', 'completed']);
            
            $applyDateFilter($q, 'p.tanggal_order');
            $data = $q->select('p.tanggal_order as tanggal', 'p.no_so as nomor', 'c.nama_customer as customer', 'u.name as salesman', 'b.merek', 'b.nama_barang as barang', 'pd.jumlah as qty', 'pd.subtotal as nominal', 'p.status_approval as keterangan')->orderBy('p.tanggal_order', 'asc')->get();

        } elseif ($kategori == 'pembelian') {
            $judulLaporan = "Laporan Pembelian (Purchase Order)";
            $q = DB::table('pembelians as p')->join('barangs as b', 'p.barang_id', '=', 'b.id');
            
            $applyDateFilter($q, 'p.tanggal_beli');
            $data = $q->select('p.tanggal_beli as tanggal', 'p.no_pembelian as nomor', 'p.nama_supplier as supplier', 'b.merek', 'b.nama_barang as barang', 'p.jumlah_beli as qty', 'p.total_bayar as nominal', 'p.status_barang as keterangan')->orderBy('p.tanggal_beli', 'asc')->get();

        } elseif ($kategori == 'piutang') {
            $judulLaporan = "Laporan Keuangan (Piutang Customer)";
            $q = DB::table('piutangs as pt')
                ->join('penjualans as p', 'pt.penjualan_id', '=', 'p.id')
                ->leftJoin('customers as c', 'p.customer_id', '=', 'c.id');
            
            $applyDateFilter($q, 'pt.created_at');
            $data = $q->select('pt.created_at as tanggal', 'p.no_so as nomor', 'c.nama_customer as customer', DB::raw('1 as qty'), 'pt.total_tagihan as nominal', 'pt.status_bayar as keterangan', DB::raw('(pt.total_tagihan - pt.total_dibayar) as sisa'))->orderBy('pt.created_at', 'asc')->get();

        } elseif ($kategori == 'utang') {
            $judulLaporan = "Laporan Keuangan (Utang Supplier)";
            $q = DB::table('utangs as ut')->join('pembelians as p', 'ut.pembelian_id', '=', 'p.id');
            
            $applyDateFilter($q, 'ut.created_at');
            $data = $q->select('ut.created_at as tanggal', 'p.no_pembelian as nomor', 'p.nama_supplier as supplier', DB::raw('1 as qty'), 'ut.total_utang as nominal', 'ut.status_bayar as keterangan', DB::raw('(ut.total_utang - ut.potongan_dn - ut.total_dibayar) as sisa'))->orderBy('ut.created_at', 'asc')->get();

        } elseif ($kategori == 'cn') {
            $judulLaporan = "Laporan Credit Note (Potongan Piutang)";
            $q = DB::table('credit_notes as cn')
                ->join('penjualans as p', 'cn.referensi_id', '=', 'p.id')
                ->leftJoin('customers as c', 'p.customer_id', '=', 'c.id')
                ->where('cn.tipe', 'penjualan');
            
            $applyDateFilter($q, 'cn.created_at');
            $data = $q->select('cn.created_at as tanggal', 'cn.nomor_cn as nomor', 'c.nama_customer as customer', DB::raw('1 as qty'), 'cn.nominal', 'cn.keterangan')->orderBy('cn.created_at', 'asc')->get();

        } elseif ($kategori == 'dn') {
            $judulLaporan = "Laporan Debit Note (Potongan Utang)";
            $q = DB::table('credit_notes as dn')
                ->join('pembelians as p', 'dn.referensi_id', '=', 'p.id')
                ->where('dn.tipe', 'pembelian');
            
            $applyDateFilter($q, 'dn.created_at');
            $data = $q->select('dn.created_at as tanggal', 'dn.nomor_cn as nomor', 'p.nama_supplier as supplier', DB::raw('1 as qty'), 'dn.nominal', 'dn.keterangan')->orderBy('dn.created_at', 'asc')->get();

        } elseif ($kategori == 'retur_jual') {
            $judulLaporan = "Laporan Retur Penjualan (Dari Customer)";
            $q = DB::table('returs as r')
                ->join('penjualans as p', 'r.referensi_id', '=', 'p.id')
                ->join('barangs as b', 'r.barang_id', '=', 'b.id')
                ->leftJoin('customers as c', 'p.customer_id', '=', 'c.id')
                ->where('r.tipe', 'penjualan');
            
            $applyDateFilter($q, 'r.created_at');
            $data = $q->select('r.created_at as tanggal', 'r.no_retur as nomor', 'c.nama_customer as customer', 'b.merek', 'b.nama_barang as barang', 'r.qty', 'r.nominal_potongan as nominal', 'r.kondisi as keterangan')->orderBy('r.created_at', 'asc')->get();

        } elseif ($kategori == 'retur_beli') {
            $judulLaporan = "Laporan Retur Pembelian (Ke Supplier)";
            $q = DB::table('returs as r')
                ->join('pembelians as p', 'r.referensi_id', '=', 'p.id')
                ->join('barangs as b', 'r.barang_id', '=', 'b.id')
                ->where('r.tipe', 'pembelian');
            
            $applyDateFilter($q, 'r.created_at');
            $data = $q->select('r.created_at as tanggal', 'r.no_retur as nomor', 'p.nama_supplier as supplier', 'b.merek', 'b.nama_barang as barang', 'r.qty', 'r.nominal_potongan as nominal', 'r.kondisi as keterangan')->orderBy('r.created_at', 'asc')->get();

        } elseif ($kategori == 'backorder') {
            $judulLaporan = "Laporan Antrian Backorder";
            $q = DB::table('back_orders as bo')
                ->join('penjualans as p', 'bo.penjualan_id', '=', 'p.id')
                ->join('barangs as b', 'bo.barang_id', '=', 'b.id')
                ->leftJoin('customers as c', 'p.customer_id', '=', 'c.id');
            
            $applyDateFilter($q, 'bo.created_at');
            $data = $q->select('bo.created_at as tanggal', 'p.no_so as nomor', 'c.nama_customer as customer', 'b.merek', 'b.nama_barang as barang', 'bo.jumlah_kurang as qty', DB::raw('0 as nominal'), 'bo.status_bo as keterangan')->orderBy('bo.created_at', 'asc')->get();
        }

        // =========================================================================
        // 3. PROSES STRUKTUR PENGELOMPOKAN DATA (SMART REPORT GROUPING)
        // =========================================================================
        $groupedData = collect([]);
        if (empty($groupBy)) {
            $groupedData = $data->groupBy(function() { return 'Semua Data'; });
        } else {
            $groupedData = $data->groupBy(function($item) use ($groupBy) {
                $keys = [];
                foreach ($groupBy as $g) {
                    $val = $item->{$g} ?? 'Tidak Ada';
                    $label = ucwords(str_replace('_', ' ', $g)); 
                    $keys[] = $label . ' : ' . $val;
                }
                return implode('  |  ', $keys); 
            });
        }

        // =========================================================================
        // 4. GENERATE DAN LOGIKA OUTPUT DOKUMEN (EXCEL REAL VS VIEW PRINT)
        // =========================================================================
        $user = Auth::user();

        // JIKA USER MEMILIH FORMAT EXCEL
        if ($format === 'excel') {
            $fileName = str_replace(' ', '_', $judulLaporan) . '_' . time() . '.xlsx';
            
            // Memanggil Generator Maatwebsite Excel resmi (.xlsx asli)
            return Excel::download(
                new LaporanExport($groupedData, $judulLaporan, $periodeLabel, $kategori), 
                $fileName
            );
        }

        // JIKA USER MEMILIH FORMAT PDF / PRINT VIEW
        return view('laporan.cetak_kustom', compact('groupedData', 'judulLaporan', 'periodeLabel', 'user', 'kategori', 'groupBy', 'format'));
    }
}