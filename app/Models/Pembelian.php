<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom di tabel pembelians untuk diisi oleh Controller
    protected $guarded = [];

    // Relasi ke tabel Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id')->withTrashed();
    }
}