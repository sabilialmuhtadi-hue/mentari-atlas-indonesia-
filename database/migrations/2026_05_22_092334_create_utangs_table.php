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
        // PASTIKAN DI SINI TERTULIS 'utangs', BUKAN 'piutangs'
        Schema::create('utangs', function (Blueprint $table) {
            $table->id();
            $table->string('no_utang_jurnal')->unique();
            // Menghubungkan utang dengan data transaksi pembelian PO dari supplier
            $table->foreignId('pembelian_id')->constrained('pembelians')->onDelete('cascade');
            $table->decimal('total_utang', 15, 2);
            $table->decimal('total_dibayar', 15, 2)->default(0);
            $table->enum('status_bayar', ['belum_bayar', 'cicil', 'lunas'])->default('belum_bayar');
            $table->date('tanggal_jatuh_tempo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utangs');
    }
};