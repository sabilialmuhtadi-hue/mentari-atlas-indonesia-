<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->string('status_barang')->default('pending')->after('tanggal_beli'); 
            $table->integer('qty_bagus')->default(0)->after('status_barang');
            $table->integer('qty_rusak')->default(0)->after('qty_bagus');
            $table->integer('qty_kurang')->default(0)->after('qty_rusak');
        });
    }

    public function down()
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn(['status_barang', 'qty_bagus', 'qty_rusak', 'qty_kurang']);
        });
    }
};