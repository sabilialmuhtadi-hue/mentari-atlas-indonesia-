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
    Schema::table('penjualan_details', function (Blueprint $table) {
        // Tambahkan default(0) agar SQLite tidak error saat migrasi
        $table->integer('jumlah_diajukan')->default(0)->after('barang_id');
    });
}    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualan_details', function (Blueprint $table) {
            //
        });
    }
};
