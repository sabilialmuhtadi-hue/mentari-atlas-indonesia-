<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Barang;
use App\Models\Piutang; 
use App\Models\Utang;
use App\Models\BackOrder; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = strtolower($user->role);
        $userId = $user->id;
        $isSales = in_array($role, ['sales', 'marketing']);

        // Total SO Keseluruhan
        $totalSO = Penjualan::when($isSales, function ($query) use ($userId) {
            return $query->where('user_id', $userId);
        })->count();
        
        // 1. Menunggu Approval (Pending)
        $menungguApproval = Penjualan::when($isSales, function ($query) use ($userId) {
            return $query->where('user_id', $userId);
        })->where('status_approval', 'pending')->count(); 
        
        $totalBarang = Barang::count();
        $stokKritis = Barang::where('stok_akhir', '<=', 15)->count();

        $salesBulan = [];
        $salesData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $salesBulan[] = $bulan->translatedFormat('M'); 
            
            $countSO = Penjualan::whereYear('created_at', $bulan->year)
                                ->whereMonth('created_at', $bulan->month)
                                ->when($isSales, function ($query) use ($userId) {
                                    return $query->where('user_id', $userId);
                                })
                                ->count();
            $salesData[] = $countSO;
        }
        
        $statusMenunggu = $menungguApproval; 

        // 2. Status Selesai / Disetujui 
        $statusSelesai = Penjualan::when($isSales, function ($query) use ($userId) {
            return $query->where('user_id', $userId);
        })->where('status_approval', 'disetujui')->count();
        
        // 3. Status Ditolak
        $statusDitolak = Penjualan::when($isSales, function ($query) use ($userId) {
            return $query->where('user_id', $userId);
        })->whereIn('status_approval', ['ditolak', 'batal'])->count();
        
        // PERBAIKAN: Array statusData sekarang HANYA berisi 3 elemen (Selesai, Menunggu, Ditolak).
        $statusData = [$statusSelesai, $statusMenunggu, $statusDitolak];

        return view('dashboard', compact(
            'totalSO', 'menungguApproval', 'totalBarang', 'stokKritis',
            'salesBulan', 'salesData', 'statusData'
        ));
    }

    public function warehouseIndex()
    {
        $totalProduk = Barang::count();
        $stokKritis = Barang::where('stok_akhir', '<=', 15)->count();
        $pesananMenungguPacking = Penjualan::where('status_approval', 'disetujui')
                                           ->where('status', 'draft')
                                           ->count();
        $antreanBackOrder = BackOrder::where('status_bo', 'antrean')->count();

        // UPGRADE: Mengambil 5 antrean SO teratas yang sudah disetujui tapi belum dipacking
        $tabelPacking = Penjualan::with('customer')
                            ->where('status_approval', 'disetujui')
                            ->where('status', 'draft')
                            ->orderBy('updated_at', 'asc') // Pakai asc agar yang paling lama menunggu muncul duluan
                            ->take(5)
                            ->get();

        return view('warehouse.dashboard', compact(
            'totalProduk', 'stokKritis', 'pesananMenungguPacking', 'antreanBackOrder', 'tabelPacking'
        )); 
    }

    public function keuanganIndex()
    {
        $totalOmzet = Piutang::sum('total_tagihan');

        $piutangBerjalan = Piutang::where('status_bayar', '!=', 'Lunas')->get()->sum(function($item) {
            return $item->total_tagihan - $item->total_dibayar - $item->potongan;
        });

        $kewajibanUtang = 0;
        if (class_exists('\App\Models\Utang')) {
            $kewajibanUtang = \App\Models\Utang::where('status_bayar', '!=', 'Lunas')->get()->sum(function($item) {
                return $item->total_utang - $item->total_dibayar;
            });
        }

        $soDisetujui = Penjualan::where('status_approval', 'disetujui')->count();
        $riwayat = Piutang::with('penjualan.customer')->orderBy('updated_at', 'desc')->take(10)->get();

        return view('keuangan.dashboard', compact('totalOmzet', 'piutangBerjalan', 'kewajibanUtang', 'soDisetujui', 'riwayat')); 
    }

    public function exportLaporan(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role);
        $userId = $user->id;
        $isSales = in_array($role, ['sales', 'marketing']);

        $kategori_laporan = $request->input('kategori_laporan', 'sales_order');
        $jenis_periode = $request->input('jenis_periode', 'all');
        $format_file = $request->input('format_file', 'excel');

        $periodeLabel = "Semua Waktu (Total Transaksi)";
        $mulai = null; $selesai = null; $bulan = null; $tahun = null;
        
        if ($jenis_periode === 'periode') {
            $mulai = $request->input('tgl_mulai');
            $selesai = $request->input('tgl_selesai') . ' 23:59:59';
            $periodeLabel = "Periode: " . date('d/m/Y', strtotime($mulai)) . " s/d " . date('d/m/Y', strtotime($selesai));
        } elseif ($jenis_periode === 'bulan') {
            $bulan = $request->input('bulan');
            $tahun = $request->input('tahun_bulan');
            $namaBulan = \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F');
            $periodeLabel = "Bulan: " . $namaBulan . " " . $tahun;
        } elseif ($jenis_periode === 'tahun') {
            $tahun = $request->input('tahun_opsi');
            $periodeLabel = "Tahun: " . $tahun;
        }

        $dataExport = collect();
        $columns = [];
        $fileNamePrefix = '';

        if ($kategori_laporan === 'sales_order') {
            $query = Penjualan::with(['customer', 'user'])->when($isSales, function ($q) use ($userId) { return $q->where('user_id', $userId); });
            if ($jenis_periode === 'periode') $query->whereBetween('created_at', [$mulai, $selesai]);
            elseif ($jenis_periode === 'bulan') $query->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
            elseif ($jenis_periode === 'tahun') $query->whereYear('created_at', $tahun);
            $dataExport = $query->orderBy('created_at', 'desc')->get();
            $columns = ['Tanggal Transaksi', 'Nomor SO', 'Nama Customer', 'Nama Sales', 'Status Approval', 'Total Tagihan (Rp)'];
            $fileNamePrefix = 'Laporan_SO';

        } elseif ($kategori_laporan === 'backorder') {
            $query = BackOrder::with(['penjualan.customer', 'barang']);
            if ($jenis_periode === 'periode') $query->whereBetween('created_at', [$mulai, $selesai]);
            elseif ($jenis_periode === 'bulan') $query->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
            elseif ($jenis_periode === 'tahun') $query->whereYear('created_at', $tahun);
            $dataExport = $query->orderBy('created_at', 'desc')->get();
            $columns = ['Tanggal BO', 'Nomor BO', 'Nomor SO', 'Nama Barang', 'Qty Kurang', 'Status BO'];
            $fileNamePrefix = 'Laporan_Backorder';

        } elseif ($kategori_laporan === 'retur') {
            $query = DB::table('returs')->join('barangs', 'returs.barang_id', '=', 'barangs.id')->select('returs.*', 'barangs.nama_barang');
            if ($jenis_periode === 'periode') $query->whereBetween('returs.created_at', [$mulai, $selesai]);
            elseif ($jenis_periode === 'bulan') $query->whereMonth('returs.created_at', $bulan)->whereYear('returs.created_at', $tahun);
            elseif ($jenis_periode === 'tahun') $query->whereYear('returs.created_at', $tahun);
            $dataExport = $query->orderBy('returs.created_at', 'desc')->get();
            $columns = ['Tanggal Retur', 'Nomor Retur', 'Tipe Retur', 'Nama Barang', 'Qty', 'Kondisi', 'Alasan / Deskripsi'];
            $fileNamePrefix = 'Laporan_Retur';

        } elseif ($kategori_laporan === 'keuangan_piutang') {
            $query = Piutang::with('penjualan.customer');
            if ($jenis_periode === 'periode') $query->whereBetween('created_at', [$mulai, $selesai]);
            elseif ($jenis_periode === 'bulan') $query->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
            elseif ($jenis_periode === 'tahun') $query->whereYear('created_at', $tahun);
            $dataExport = $query->orderBy('created_at', 'desc')->get();
            $columns = ['Tanggal Transaksi', 'Nomor SO', 'Nama Customer', 'Total Tagihan (Rp)', 'Total Dibayar (Rp)', 'Sisa Piutang (Rp)', 'Status Bayar'];
            $fileNamePrefix = 'Laporan_Piutang_Customer';

        } elseif ($kategori_laporan === 'keuangan_utang') {
            $query = Utang::with('pembelian');
            if ($jenis_periode === 'periode') $query->whereBetween('created_at', [$mulai, $selesai]);
            elseif ($jenis_periode === 'bulan') $query->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun);
            elseif ($jenis_periode === 'tahun') $query->whereYear('created_at', $tahun);
            $dataExport = $query->orderBy('created_at', 'desc')->get();
            $columns = ['Tanggal Transaksi', 'Nomor PO (Pembelian)', 'Nama Supplier', 'Total Utang (Rp)', 'Total Dibayar (Rp)', 'Sisa Utang (Rp)', 'Status Bayar'];
            $fileNamePrefix = 'Laporan_Utang_Supplier';
        }

        if ($format_file === 'excel') {
            $safeLabel = str_replace([':', ' ', '/'], '_', $periodeLabel);
            $fileName = $fileNamePrefix . '_MentariAtlas_' . $safeLabel . '_' . date('His') . '.csv';
            
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $callback = function() use($dataExport, $columns, $kategori_laporan) {
                $file = fopen('php://output', 'w');
                fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
                fputcsv($file, $columns, ';');

                foreach ($dataExport as $d) {
                    $row = [];
                    if ($kategori_laporan === 'sales_order') {
                        $row = [
                            $d->created_at->format('d/m/Y H:i'),
                            $d->no_so,
                            $d->customer ? $d->customer->nama_customer : '-',
                            $d->user ? $d->user->name : '-',
                            strtoupper($d->status_approval),
                            $d->total_semua
                        ];
                    } elseif ($kategori_laporan === 'backorder') {
                        $row = [
                            $d->created_at->format('d/m/Y H:i'),
                            'BO-' . str_pad($d->id, 4, '0', STR_PAD_LEFT),
                            $d->penjualan ? $d->penjualan->no_so : '-',
                            $d->barang ? $d->barang->nama_barang : '-',
                            $d->jumlah_kurang,
                            strtoupper($d->status_bo)
                        ];
                    } elseif ($kategori_laporan === 'retur') {
                        $row = [
                            Carbon::parse($d->created_at)->format('d/m/Y H:i'),
                            $d->no_retur ?? 'RE-'.$d->id,
                            strtoupper($d->tipe),
                            $d->nama_barang,
                            $d->qty,
                            strtoupper($d->kondisi ?? 'TIDAK ADA'),
                            $d->alasan
                        ];
                    } elseif ($kategori_laporan === 'keuangan_piutang') {
                        $sisa = $d->total_tagihan - $d->total_dibayar;
                        $row = [
                            $d->created_at->format('d/m/Y H:i'),
                            $d->penjualan ? $d->penjualan->no_so : '-',
                            $d->penjualan && $d->penjualan->customer ? $d->penjualan->customer->nama_customer : '-',
                            $d->total_tagihan,
                            $d->total_dibayar,
                            $sisa,
                            strtoupper($d->status_bayar)
                        ];
                    } elseif ($kategori_laporan === 'keuangan_utang') {
                        $sisa = $d->total_utang - $d->total_dibayar;
                        $row = [
                            $d->created_at->format('d/m/Y H:i'),
                            $d->pembelian ? $d->pembelian->no_pembelian : '-',
                            $d->pembelian ? $d->pembelian->nama_supplier : '-',
                            $d->total_utang,
                            $d->total_dibayar,
                            $sisa,
                            strtoupper($d->status_bayar)
                        ];
                    }
                    fputcsv($file, $row, ';');
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return view('laporan.cetak_pdf', compact('dataExport', 'periodeLabel', 'isSales', 'user', 'kategori_laporan'));
    }
}