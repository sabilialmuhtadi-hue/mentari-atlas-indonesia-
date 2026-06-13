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
        Schema::create('back_orders', function (Blueprint $table) {
            $table->id();
            // Menghubungkan ke tabel penjualan induk
            $table->foreignId('penjualan_id')->constrained('penjualans')->onDelete('cascade');
            // Menghubungkan ke tabel barang spesifik yang kurang
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            
            $table->integer('jumlah_diminta');  // Jumlah awal yang diorder sales
            $table->integer('jumlah_kurang');   // Berapa sisa kurangnya yang dijadikan antrean BO
            $table->string('status_bo')->default('antrean'); // Status: 'antrean' atau 'terpenuhi'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('back_orders');
    }
};