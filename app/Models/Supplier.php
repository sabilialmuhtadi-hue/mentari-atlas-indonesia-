<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'kode_supplier',
        'nama_supplier',
        'ktp',
        'npwp',
        'telepon',
        'alamat',
        'jatuh_tempo_hari'
    ];

    // Menarik semua riwayat pembelian berdasarkan kesamaan nama supplier
    public function pembelians()
    {
        return $this->hasMany(Pembelian::class, 'nama_supplier', 'nama_supplier');
    }
}