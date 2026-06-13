<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cek & Perbaiki Tabel Suppliers (Tambah KTP & NPWP)
        if (!Schema::hasColumn('suppliers', 'ktp')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('ktp')->nullable();
                $table->string('npwp')->nullable();
            });
        }

        // 2. Cek & Perbaiki Tabel Penjualan Details (Tambah Pondasi HPP Laba)
        if (!Schema::hasColumn('penjualan_details', 'hpp')) {
            Schema::table('penjualan_details', function (Blueprint $table) {
                $table->bigInteger('hpp')->default(0);
            });
        }
    }

    public function down(): void
    {
        // Dikosongkan saja untuk fix darurat
    }
};