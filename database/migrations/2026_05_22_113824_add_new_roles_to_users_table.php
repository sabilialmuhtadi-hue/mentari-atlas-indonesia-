<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mengubah kolom 'role' dari ENUM menjadi STRING biasa yang fleksibel
        // Serta memberikan nilai default 'sales' jika ada data baru tanpa role
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default('sales')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Jika di-rollback, kembalikan ke string biasa tanpa default
            $table->string('role')->change();
        });
    }
};