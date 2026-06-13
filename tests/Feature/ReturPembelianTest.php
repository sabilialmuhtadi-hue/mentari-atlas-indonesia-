<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\Utang;

class ReturPembelianTest extends TestCase
{
    use RefreshDatabase;

    public function test_debit_note_reduces_utang_and_creates_credit_note()
    {
        $user = User::factory()->create(['role' => 'warehouse']);
        $barang = Barang::create(['kode_barang' => 'PB1', 'nama_barang' => 'Produk PB', 'stok_akhir' => 20]);

        $pembelian = Pembelian::create([
            'no_pembelian' => 'PO-001',
            'nama_supplier' => 'Supplier A',
            'barang_id' => $barang->id,
            'jumlah_beli' => 5,
            'harga_beli_hpp' => 100,
            'total_bayar' => 500,
            'tanggal_beli' => now(),
        ]);

        Utang::create([
            'no_utang_jurnal' => 'UT-001',
            'pembelian_id' => $pembelian->id,
            'total_utang' => 500.00,
            'total_dibayar' => 0,
            'status_bayar' => 'belum_bayar',
            'tanggal_jatuh_tempo' => now(),
        ]);

        $nominal = 120;
        $response = $this->actingAs($user)->post('/warehouse/retur-pembelian', [
            'pembelian_id' => $pembelian->id,
            'barang_id' => $barang->id,
            'jenis_retur' => 'harga_debit_note',
            'qty_retur' => 2,
            'nominal_potongan' => $nominal,
            'alasan' => 'Kualitas kurang'
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('returs', [
            'tipe' => 'pembelian',
            'referensi_id' => $pembelian->id,
            'nominal_potongan' => $nominal,
        ]);

        $this->assertDatabaseHas('credit_notes', [
            'tipe' => 'pembelian',
            'referensi_id' => $pembelian->id,
            'nominal' => $nominal,
        ]);

        $this->assertDatabaseHas('utangs', [
            'pembelian_id' => $pembelian->id,
            'total_utang' => 380.00, // 500 - 120
        ]);
    }

    public function test_retur_fisik_reduces_stock_for_pembelian()
    {
        $user = User::factory()->create(['role' => 'warehouse']);
        $barang = Barang::create(['kode_barang' => 'PB2', 'nama_barang' => 'Produk PB2', 'stok_akhir' => 50]);

        $pembelian = Pembelian::create([
            'no_pembelian' => 'PO-002',
            'nama_supplier' => 'Supplier B',
            'barang_id' => $barang->id,
            'jumlah_beli' => 10,
            'harga_beli_hpp' => 200,
            'total_bayar' => 2000,
            'tanggal_beli' => now(),
        ]);

        $response = $this->actingAs($user)->post('/warehouse/retur-pembelian', [
            'pembelian_id' => $pembelian->id,
            'barang_id' => $barang->id,
            'jenis_retur' => 'fisik',
            'qty_retur' => 5,
            'alasan' => 'Overstock'
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('returs', [
            'tipe' => 'pembelian',
            'referensi_id' => $pembelian->id,
            'qty' => 5,
        ]);

        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'stok_akhir' => 45, // 50 - 5
        ]);
    }
}
