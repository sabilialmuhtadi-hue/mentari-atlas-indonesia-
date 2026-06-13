<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranUtang extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_utangs';
    
    protected $fillable = [
        'utang_id',
        'jumlah_bayar',
        'tanggal_bayar',
        'metode_pembayaran',
        'dibayar_oleh',
        'keterangan',
        'bukti_bayar'
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
    ];

    public function utang()
    {
        return $this->belongsTo(Utang::class, 'utang_id');
    }

    public function pembayar()
    {
        return $this->belongsTo(User::class, 'dibayar_oleh');
    }
}