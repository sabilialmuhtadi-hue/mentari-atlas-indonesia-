<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_cust', 
        'nama_customer', 
        'tingkat_customer',
        'npwp', 
        'ktp',
        'no_telp',       
        'alamat',        
        'foto_ktp',      
        'foto_npwp',     
        'foto_toko',     
        'plafon'         
    ];

    // Mendaftarkan kolom bayangan (accessor) agar otomatis ikut terbaca saat data Customer dipanggil
    protected $appends = ['piutang_berjalan', 'sisa_plafon'];

    // Relasi ke Penjualan / Sales Order
    public function penjualans()
    {
        return $this->hasMany(Penjualan::class);
    }

    /**
     * RUMUS 1: MENGHITUNG TOTAL HUTANG BERJALAN (REAL-TIME)
     * Mengambil data dari tabel Piutang dengan melewati jembatan Penjualan
     */
    public function getPiutangBerjalanAttribute()
    {
        if (class_exists(\App\Models\Piutang::class)) {
            // Karena Piutang tidak punya 'customer_id', kita cari lewat relasi Penjualan
            return \App\Models\Piutang::whereHas('penjualan', function($query) {
                    $query->where('customer_id', $this->id);
                })
                ->where('status_bayar', '!=', 'lunas') // Hanya hitung yang belum lunas
                ->get()
                ->sum(function ($piutang) {
                    // Rumus akurat: Tagihan awal dikurangi total yang sudah dicicil
                    return $piutang->total_tagihan - $piutang->total_dibayar;
                });
        }

        // Return 0 jika modul Piutang belum terhubung/kosong
        return 0; 
    }

    /**
     * RUMUS 2: MENGHITUNG SISA LIMIT KREDIT
     * Plafon Maksimal dikurangi Hutang Berjalan. 
     * Otomatis naik kembali jika utang di modul keuangan dicicil/dilunasi.
     */
    public function getSisaPlafonAttribute()
    {
        $sisa = $this->plafon - $this->piutang_berjalan;
        
        // Jika sisa limit minus (karena hutang melewati plafon), amankan di angka 0
        return $sisa < 0 ? 0 : $sisa; 
    }
}