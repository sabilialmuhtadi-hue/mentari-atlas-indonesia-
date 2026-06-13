<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembayaran_piutangs', function (Blueprint $table) {
            $table->id();
            
            // Menghubungkan cicilan ke tagihan piutang utama
            $table->foreignId('piutang_id')->constrained('piutangs')->onDelete('cascade');
            
            // Informasi nominal dan waktu transaksi secara presisi
            $table->double('jumlah_bayar'); 
            $table->dateTime('tanggal_bayar'); 
            
            // Metode transaksi (BCA, Mandiri, Tunai, dll)
            $table->string('metode_pembayaran')->nullable(); 
            
            // Mencatat user/admin yang menerima dan memverifikasi cicilan ini
            $table->foreignId('diterima_oleh')->constrained('users')->onDelete('cascade');
            
            // Catatan opsional dari admin invoice / kasir
            $table->text('keterangan')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_piutangs');
    }
};