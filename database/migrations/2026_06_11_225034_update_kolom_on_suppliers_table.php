<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Tambah KTP dan NPWP
            $table->string('ktp')->nullable()->after('nama_supplier');
            $table->string('npwp')->nullable()->after('ktp');
            // Hapus PIC
            $table->dropColumn('pic');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['ktp', 'npwp']);
            $table->string('pic')->nullable();
        });
    }
};