<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpesifikasiToBarangsTable extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom spesifikasi untuk melengkapi format SKU Poin 13
     */
    public function up()
    {
        Schema::table('barangs', function (Blueprint $table) {
            // Menambahkan kolom spesifikasi setelah kolom nama_barang
            $table->string('spesifikasi')->nullable()->after('nama_barang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropColumn('spesifikasi');
        });
    }
}