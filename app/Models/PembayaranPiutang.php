<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPiutang extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_piutangs';
    
    protected $fillable = [
        'piutang_id',
        'jumlah_bayar',
        'tanggal_bayar',
        'metode_pembayaran',
        'diterima_oleh',
        'keterangan',
        'bukti_bayar' // <--- Ini yang baru ditambahkan
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
    ];

    public function piutang()
    {
        return $this->belongsTo(Piutang::class, 'piutang_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'diterima_oleh');
    }
}