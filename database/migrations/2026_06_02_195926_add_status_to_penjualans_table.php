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
        Schema::table('penjualans', function (Blueprint $table) {
            // Menambahkan kolom status setelah kolom id (atau bisa disesuaikan)
            // default 'draft' agar data lama tidak error
            $table->string('status')->default('draft')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjualans', function (Blueprint $table) {
            // Menghapus kolom status jika migrasi dibatalkan
            $table->dropColumn('status');
        });
    }
};