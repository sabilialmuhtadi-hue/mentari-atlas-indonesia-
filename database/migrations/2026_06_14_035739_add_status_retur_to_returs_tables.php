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
        Schema::table('retur_pembelians', function (Blueprint $table) {
            $table->string('status_retur')->default('pending')->after('alasan');
        });

        Schema::table('retur_penjualans', function (Blueprint $table) {
            $table->string('status_retur')->default('pending')->after('alasan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('returs_tables', function (Blueprint $table) {
            //
        });
    }
};
