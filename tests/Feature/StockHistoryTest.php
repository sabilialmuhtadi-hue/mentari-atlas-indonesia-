<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Barang;
use App\Models\StockHistory;

class StockHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_history_created_on_purchase()
    {
        $user = User::factory()->create(['role' => 'direktur']);
        $barang = Barang::create([
            'kode_barang' => 'B100',
            'nama_barang' => 'Sparepart Tes',
            'stok_akhir' => 5,
            'barang_masuk' => 0,
            'barang_keluar' => 0,
            'harga_jual' => 50000,
        ]);

        $response = $this->actingAs($user)->post('/pembelian', [
            'nama_supplier' => 'Supplier Tes',
            'barang_id' => $barang->id,
            'jumlah_beli' => 10,
            'harga_beli_hpp' => 10000,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('stock_histories', [
            'barang_id' => $barang->id,
            'event_type' => 'purchase',
            'change' => 10,
            'stock_before' => 5,
            'stock_after' => 15,
        ]);
    }

    public function test_barang_history_page_displays_stock_history()
    {
        $user = User::factory()->create(['role' => 'admin_warehouse']);
        $barang = Barang::create([
            'kode_barang' => 'B101',
            'nama_barang' => 'Produk History',
            'stok_akhir' => 20,
            'barang_masuk' => 20,
            'barang_keluar' => 0,
            'harga_jual' => 75000,
        ]);

        StockHistory::create([
            'barang_id' => $barang->id,
            'event_type' => 'initial_stock',
            'event_reference' => 'MANUAL',
            'change' => 20,
            'stock_before' => 0,
            'stock_after' => 20,
            'keterangan' => 'Pengisian stok awal manual.',
        ]);

        $response = $this->actingAs($user)->get(route('barang.history', $barang->id));

        $response->assertOk();
        $response->assertSee('Riwayat Stok Barang');
        $response->assertSee('Pengisian stok awal manual.');
    }
}
