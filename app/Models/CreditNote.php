<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNote extends Model
{
    use HasFactory;

    // Mengarahkan model ke nama tabel yang benar
    protected $table = 'credit_notes';

    // Kolom yang diizinkan untuk diisi secara massal
    protected $fillable = [
        'nomor_cn',
        'tipe',
        'referensi_id',
        'nominal',
        'keterangan',
    ];

    /**
     * Relasi opsional jika Anda ingin menghubungkan CN ke data Penjualan (SO)
     */
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'referensi_id');
    }

    /**
     * Relasi opsional jika Anda ingin menghubungkan CN ke data Pembelian (PO)
     */
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'referensi_id');
    }
}