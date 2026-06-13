<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Penjualan;
use App\Models\Piutang;
use App\Models\Barang;
use App\Models\Pembelian;
use App\Models\Utang;

class KeuanganPaymentDescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_piutang_payment_generates_default_keterangan_when_empty()
    {
        $user = User::factory()->create(['role' => 'admin_keuangan']);
        $customer = Customer::create([
            'id_cust' => 'CUST001',
            'nama_customer' => 'PT Contoh Customer',
        ]);

        $penjualan = Penjualan::create([
            'no_so' => 'SO-001',
            'tanggal_order' => now()->toDateString(),
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'total_semua' => 100000,
            'status_approval' => 'disetujui',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $piutang = Piutang::create([
            'no_invoice' => 'INV-001',
            'penjualan_id' => $penjualan->id,
            'total_tagihan' => 100000,
            'total_dibayar' => 0,
            'status_bayar' => 'belum_bayar',
            'jatuh_tempo' => now()->addDays(30)->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('keuangan.piutang.bayar', $piutang->id), [
            'jumlah_bayar' => 25000,
            'metode_pembayaran' => 'Transfer BCA',
            'keterangan' => '',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pembayaran_piutangs', [
            'piutang_id' => $piutang->id,
            'jumlah_bayar' => 25000,
            'metode_pembayaran' => 'Transfer BCA',
            'keterangan' => 'Pembayaran cicilan untuk invoice SO-001 via Transfer BCA.',
        ]);
    }

    public function test_utang_payment_generates_default_keterangan_when_empty()
    {
        $user = User::factory()->create(['role' => 'admin_keuangan']);

        $barang = Barang::create([
            'kode_barang' => 'B200',
            'nama_barang' => 'Barang Supplier',
            'stok_akhir' => 50,
            'barang_masuk' => 50,
            'barang_keluar' => 0,
            'harga_jual' => 50000,
        ]);

        $pembelian = Pembelian::create([
            'no_pembelian' => 'PO-001',
            'nama_supplier' => 'Supplier A',
            'barang_id' => $barang->id,
            'jumlah_beli' => 10,
            'harga_beli_hpp' => 20000,
            'total_bayar' => 200000,
            'tanggal_beli' => now()->toDateString(),
        ]);

        $utang = Utang::create([
            'no_utang_jurnal' => 'UTG-001',
            'pembelian_id' => $pembelian->id,
            'total_utang' => 200000,
            'total_dibayar' => 0,
            'status_bayar' => 'belum_bayar',
            'tanggal_jatuh_tempo' => now()->addDays(30)->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('keuangan.utang.bayar', $utang->id), [
            'jumlah_bayar' => 50000,
            'metode_pembayaran' => 'Transfer BRI',
            'keterangan' => '',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('pembayaran_utangs', [
            'utang_id' => $utang->id,
            'jumlah_bayar' => 50000,
            'metode_pembayaran' => 'Transfer BRI',
            'keterangan' => 'Pembayaran utang UTG-001 via Transfer BRI.',
        ]);
    }
}
