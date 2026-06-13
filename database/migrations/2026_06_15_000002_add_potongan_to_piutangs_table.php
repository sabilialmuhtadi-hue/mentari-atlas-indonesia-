<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('piutangs', function (Blueprint $table) {
            $table->decimal('potongan', 15, 2)->default(0)->after('total_tagihan');
        });
    }
    public function down(): void {
        Schema::table('piutangs', function (Blueprint $table) {
            $table->dropColumn('potongan');
        });
    }
};