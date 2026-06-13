<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_utangs', function (Blueprint $table) {
            $table->id();
            
            // Kolom yang dipanggil oleh aplikasi
            $table->foreignId('utang_id')->constrained('utangs')->onDelete('cascade');
            $table->double('jumlah_bayar');
            $table->dateTime('tanggal_bayar');
            $table->string('metode_pembayaran')->nullable();
            
            // Kolom untuk user/admin
            $table->foreignId('dibayar_oleh')->constrained('users')->onDelete('cascade');
            
            $table->text('keterangan')->nullable();
            $table->string('bukti_bayar')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_utangs');
    }
};