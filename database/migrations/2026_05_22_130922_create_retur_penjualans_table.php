<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('no_retur_jual')->unique();
            $table->foreignId('penjualan_id')->constrained('penjualans')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->integer('qty_retur');
            $table->string('alasan');
            $table->enum('jenis_retur', ['fisik', 'harga_credit_note'])->default('fisik'); // BARU: Pilihan klaim
            $table->decimal('nominal_potongan', 15, 2)->default(0); // BARU: Nilai uang potongan harga
            $table->enum('status_kondisi', ['bagus', 'rusak'])->nullable(); // Diubah jadi nullable jika retur harga
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_penjualans');
    }
};