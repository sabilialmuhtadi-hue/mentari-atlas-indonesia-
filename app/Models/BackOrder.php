<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackOrder extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara spesifik
    protected $table = 'back_orders';

    // Kolom-kolom yang diizinkan untuk diisi massal
    protected $fillable = [
        'penjualan_id',
        'barang_id',
        'jumlah_diminta',
        'jumlah_kurang',
        'status_bo',
    ];

    /**
     * Relasi ke data Penjualan induk (Sales Order)
     */
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'penjualan_id');
    }

    /**
     * Relasi ke data Barang spesifik yang mengantre BO
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id')->withTrashed();
    }
}