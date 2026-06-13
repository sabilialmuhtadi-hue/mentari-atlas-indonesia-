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
        // Hapus dulu jika secara tidak sengaja ada sisa tabel yang korup
        Schema::dropIfExists('returs');

        // Buat tabel returs secara bersih
        Schema::create('returs', function (Blueprint $table) {
            $table->id();
            $table->string('no_retur')->nullable()->unique();
            $table->string('tipe'); // penjualan / pembelian
            $table->string('jenis_retur')->default('fisik'); // fisik / harga_credit_note / harga_debit_note
            $table->unsignedBigInteger('referensi_id'); // penjualan_id atau pembelian_id
            $table->unsignedBigInteger('barang_id');
            $table->integer('qty');
            $table->string('kondisi')->default('rusak'); // bagus / rusak
            $table->bigInteger('nominal_potongan')->default(0);
            $table->text('alasan')->nullable();
            $table->timestamps();
        });

        // Buat tabel credit_notes jika belum ada
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_cn')->unique();
            $table->enum('tipe', ['penjualan', 'pembelian']);
            $table->unsignedBigInteger('referensi_id');
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('returs');
    }
};