<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barangs', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique();
            $table->string('nama_barang');
            $table->string('kategori')->nullable();
            $table->string('merek')->nullable();
            $table->string('satuan')->nullable();
            $table->integer('stok_awal')->default(0);
            $table->integer('barang_masuk')->default(0);
            $table->integer('barang_keluar')->default(0);
            $table->integer('stok_akhir')->default(0);
            $table->integer('harga_beli')->default(0);
            $table->integer('harga_jual')->default(0);
            $table->string('lokasi_rak')->nullable();
            $table->string('supplier')->nullable();
            $table->date('tanggal_update')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
