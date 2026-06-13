<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_pembelians', function (Blueprint $table) {
            $table->id();
            $table->string('no_retur_beli')->unique();
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->integer('qty_retur');
            $table->string('nama_supplier');
            $table->string('alasan');
            $table->enum('jenis_retur', ['fisik', 'harga_debit_note'])->default('fisik'); // BARU: Pilihan klaim
            $table->decimal('nominal_potongan', 15, 2)->default(0); // BARU: Nilai uang potongan harga
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_pembelians');
    }
};