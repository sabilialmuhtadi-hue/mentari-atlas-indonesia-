<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Barang;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Piutang;

class ReturPenjualanTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_note_reduces_piutang_and_creates_credit_note()
    {
        // Arrange
        $user = User::factory()->create(['role' => 'warehouse']);
        $customer = Customer::create(['id_cust' => 'CUST1', 'nama_customer' => 'Cust A']);
        $barang = Barang::create(['kode_barang' => 'B001', 'nama_barang' => 'Produk A', 'stok_akhir' => 10]);

        $penjualan = Penjualan::create([
            'no_so' => 'SO-001',
            'tanggal_order' => now(),
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'total_semua' => 200
        ]);

        PenjualanDetail::create([
            'penjualan_id' => $penjualan->id,
            'barang_id' => $barang->id,
            'jumlah' => 2,
            'harga_satuan' => 100,
            'subtotal' => 200
        ]);

        Piutang::create([
            'no_invoice' => 'INV-001',
            'penjualan_id' => $penjualan->id,
            'total_tagihan' => 200,
            'total_dibayar' => 0,
            'status_bayar' => 'belum_bayar',
            'jatuh_tempo' => now()
        ]);

        // Act
        $nominal = 50;
        $response = $this->actingAs($user)->post('/warehouse/retur-penjualan', [
            'penjualan_id' => $penjualan->id,
            'barang_id' => $barang->id,
            'jenis_retur' => 'harga_credit_note',
            'qty_retur' => 1,
            'nominal_potongan' => $nominal,
            'alasan' => 'Selisih harga'
        ]);

        // Assert
        $response->assertRedirect();

        $this->assertDatabaseHas('returs', [
            'tipe' => 'penjualan',
            'referensi_id' => $penjualan->id,
            'nominal_potongan' => $nominal,
        ]);

        $this->assertDatabaseHas('credit_notes', [
            'tipe' => 'penjualan',
            'referensi_id' => $penjualan->id,
            'nominal' => $nominal,
        ]);

        $this->assertDatabaseHas('piutangs', [
            'penjualan_id' => $penjualan->id,
            'total_tagihan' => 150.00,
        ]);
    }

    public function test_retur_fisik_returns_stock_when_bagus()
    {
        // Arrange
        $user = User::factory()->create(['role' => 'warehouse']);
        $customer = Customer::create(['id_cust' => 'CUST2', 'nama_customer' => 'Cust B']);
        $barang = Barang::create(['kode_barang' => 'B002', 'nama_barang' => 'Produk B', 'stok_akhir' => 5]);

        $penjualan = Penjualan::create([
            'no_so' => 'SO-002',
            'tanggal_order' => now(),
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'total_semua' => 100
        ]);

        PenjualanDetail::create([
            'penjualan_id' => $penjualan->id,
            'barang_id' => $barang->id,
            'jumlah' => 1,
            'harga_satuan' => 100,
            'subtotal' => 100
        ]);

        // Act
        $response = $this->actingAs($user)->post('/warehouse/retur-penjualan', [
            'penjualan_id' => $penjualan->id,
            'barang_id' => $barang->id,
            'jenis_retur' => 'fisik',
            'qty_retur' => 2,
            'status_kondisi' => 'bagus',
            'alasan' => 'Kirim salah'
        ]);

        // Assert
        $response->assertRedirect();

        $this->assertDatabaseHas('returs', [
            'tipe' => 'penjualan',
            'referensi_id' => $penjualan->id,
            'qty' => 2,
        ]);

        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'stok_akhir' => 7, // initial 5 + 2 returned
        ]);
    }
}
