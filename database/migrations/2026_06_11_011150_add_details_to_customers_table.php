<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('no_telp')->nullable()->after('tingkat_customer');
            $table->text('alamat')->nullable()->after('no_telp');
            $table->string('foto_ktp')->nullable()->after('ktp');
            $table->string('foto_npwp')->nullable()->after('npwp');
            $table->string('foto_toko')->nullable()->after('foto_npwp');
            $table->decimal('plafon', 15, 2)->default(0)->after('foto_toko'); // Limit utang
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['no_telp', 'alamat', 'foto_ktp', 'foto_npwp', 'foto_toko', 'plafon']);
        });
    }
};