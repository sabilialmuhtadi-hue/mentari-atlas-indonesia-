<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BackOrder;
use App\Models\Barang;
use App\Models\StockHistory;
use Illuminate\Support\Facades\DB;

class BackOrderController extends Controller
{
    /**
     * Menampilkan halaman daftar antrean Back Order
     */
    public function index()
    {
        // 1. Ambil semua data BO beserta relasinya
        $data = BackOrder::with(['penjualan.customer', 'penjualan.user', 'barang'])->get();

        // 2. Sortir menggunakan PHP Collection agar urutan rapi secara visual
        // Prioritas: Status 'antrean' atau 'pending' muncul di atas, lalu urutkan nomor SO dari yang terlama
        $backOrders = $data->sortBy(function($bo) {
            $statusPriority = (strtolower($bo->status_bo) === 'terpenuhi' || strtolower($bo->status_bo) === 'selesai') ? 1 : 0;
            return [$statusPriority, $bo->penjualan->no_so];
        }, SORT_REGULAR, false); // <--- Sudah diubah menjadi false (Ascending/Terlama ke Terbaru)

        return view('backorder.index', compact('backOrders'));
    }

    /**
     * Memproses penebusan / pemenuhan stok antrean BO (Kemas Sisa)
     */
    public function penebusan($id)
    {
        $bo = BackOrder::findOrFail($id);

        // Validasi status ganda (jika sudah terpenuhi atau selesai)
        if (strtolower($bo->status_bo) === 'terpenuhi' || strtolower($bo->status_bo) === 'selesai') {
            return back()->withErrors(['error' => 'Antrean Back Order ini sudah terpenuhi sebelumnya.']);
        }

        $barang = $bo->barang;

        // Validasi ganda: Pastikan stok di gudang saat klik benar-benar mencukupi kuantitas kurangnya
        if ($barang->stok_akhir < $bo->jumlah_kurang) {
            return back()->withErrors(['error' => "Stok untuk barang '{$barang->nama_barang}' saat ini tidak mencukupi untuk memenuhi BO (Stok: {$barang->stok_akhir} Unit, Butuh: {$bo->jumlah_kurang} Unit)."]);
        }

        try {
            DB::beginTransaction();

            // 1. Kurangi stok akhir gudang & naikkan jumlah barang keluar
            $stokSebelumnya = $barang->stok_akhir;
            $barang->update([
                'stok_akhir' => $barang->stok_akhir - $bo->jumlah_kurang,
                'barang_keluar' => $barang->barang_keluar + $bo->jumlah_kurang
            ]);

            // 2. Catat riwayat mutasi kartu stok secara formal
            StockHistory::record(
                $barang,
                -$bo->jumlah_kurang,
                'backorder_fulfillment', // Kategori mutasi pelunasan BO
                $bo->penjualan->no_so,
                'Pengiriman Tahap 2 (Pelunasan sisa kuantitas Back Order).',
                $stokSebelumnya
            );

            // 3. Ubah status antrean BO menjadi terpenuhi
            $bo->update([
                'status_bo' => 'terpenuhi'
            ]);

            DB::commit();
            return back()->with('success', "Stok cadangan antrean BO untuk barang '{$barang->nama_barang}' berhasil dilepaskan dan dikemas untuk pengiriman tahap 2.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses pemenuhan BO: ' . $e->getMessage()]);
        }
    }
}