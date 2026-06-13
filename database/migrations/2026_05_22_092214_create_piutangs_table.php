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
        Schema::create('piutangs', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->unique();
            // Menghubungkan piutang dengan data penjualan SO
            $table->foreignId('penjualan_id')->constrained('penjualans')->onDelete('cascade');
            $table->decimal('total_tagihan', 15, 2);
            $table->decimal('total_dibayar', 15, 2)->default(0);
            $table->enum('status_bayar', ['belum_bayar', 'cicil', 'lunas'])->default('belum_bayar');
            $table->date('jatuh_tempo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piutangs');
    }
};