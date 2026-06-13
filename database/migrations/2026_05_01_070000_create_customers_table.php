<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('id_cust')->unique();
            $table->string('nama_customer');
            $table->string('npwp')->nullable();
            $table->string('ktp')->nullable();
            
            // TAMBAHAN: Kolom tingkat_customer dengan tipe string biasa (bukan enum)
            $table->string('tingkat_customer')->default('Reguler');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};