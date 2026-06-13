<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjualan_details', function (Blueprint $table) {
            // Menambahkan kolom HPP setelah kolom barang_id
            $table->bigInteger('hpp')->default(0)->after('barang_id');
        });
    }

    public function down(): void
    {
        Schema::table('penjualan_details', function (Blueprint $table) {
            $table->dropColumn('hpp');
        });
    }
};