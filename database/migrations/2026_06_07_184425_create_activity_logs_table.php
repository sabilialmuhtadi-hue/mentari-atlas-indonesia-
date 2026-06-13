<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            // user_id untuk mencatat siapa pelakunya (bisa null kalau sistem yang melakukan)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // Aksi singkat (misal: 'HAPUS BARANG', 'BUAT SO')
            $table->string('action');
            // Penjelasan detail (misal: 'Budi menghapus master barang Aki Motor (B001)')
            $table->text('description');
            // Mencatat IP Address untuk keamanan ekstra
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};