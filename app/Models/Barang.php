<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\StockHistory;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'spesifikasi',
        'kategori',
        'merek',
        'satuan',
        'stok_awal',
        'barang_masuk',
        'barang_keluar',
        'stok_akhir',
        'stok_rusak', // <-- TAMBAHAN BARU
        'harga_beli',
        'harga_jual',
        'lokasi_rak',
        'supplier',
        'tanggal_update'
    ];

    public function penjualans() { return $this->hasMany(Penjualan::class); }
    public function pembelians() { return $this->hasMany(Pembelian::class); }
    public function stockHistories() { return $this->hasMany(StockHistory::class); }
}