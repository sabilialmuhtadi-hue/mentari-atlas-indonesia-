<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPembelian extends Model
{
    protected $fillable = ['no_retur_beli', 'barang_id', 'qty_retur', 'nama_supplier', 'alasan'];

    protected static function booted()
    {
        // Otomatisasi Potong Stok Gudang saat Data Retur Pembelian Disimpan
        static::created(function ($retur) {
            $barang = Barang::find($retur->barang_id);
            if ($barang) {
                $stockKey = isset($barang->stock) ? 'stock' : 'stok';
                $barang->decrement($stockKey, $retur->qty_retur);
            }
        });
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}