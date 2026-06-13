<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('returs', function (Blueprint $table) {
            // Kolom status retur (pending = masuk karantina/draf, completed = sudah dieksekusi)
            $table->string('status_retur')->default('completed')->after('kondisi'); 
        });

        Schema::table('utangs', function (Blueprint $table) {
            // Kolom khusus untuk menyimpan total potongan Debit Note secara terpisah
            $table->decimal('potongan_dn', 15, 2)->default(0)->after('total_utang');
        });
    }

    public function down()
    {
        Schema::table('returs', function (Blueprint $table) {
            $table->dropColumn('status_retur');
        });

        Schema::table('utangs', function (Blueprint $table) {
            $table->dropColumn('potongan_dn');
        });
    }
};