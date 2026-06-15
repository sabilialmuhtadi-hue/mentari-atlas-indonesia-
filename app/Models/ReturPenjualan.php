<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPenjualan extends Model
{
    protected $fillable = ['penjualan_id', 'barang_id', 'jumlah_retur', 'alasan', 'status_retur'];

    public function penjualan() {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    public function barang() {
        return $this->belongsTo(Barang::class, 'barang_id')->withTrashed();
    }
}