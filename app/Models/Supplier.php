<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'ktp',
        'npwp',
        'telepon',
        'alamat'
    ];

    // Menarik semua riwayat pembelian berdasarkan kesamaan nama supplier
    public function pembelians()
    {
        return $this->hasMany(Pembelian::class, 'nama_supplier', 'nama_supplier');
    }
}