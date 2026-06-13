<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom diinput_by di tabel piutangs
        Schema::table('piutangs', function (Blueprint $table) {
            $table->unsignedBigInteger('diinput_by')->nullable()->after('status_bayar');
            $table->foreign('diinput_by')->references('id')->on('users')->onDelete('set null');
        });

        // Tambah kolom diinput_by di tabel utangs
        Schema::table('utangs', function (Blueprint $table) {
            $table->unsignedBigInteger('diinput_by')->nullable()->after('status_bayar');
            $table->foreign('diinput_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('piutangs', function (Blueprint $table) {
            $table->dropForeign(['diinput_by']);
            $table->dropColumn('diinput_by');
        });

        Schema::table('utangs', function (Blueprint $table) {
            $table->dropForeign(['diinput_by']);
            $table->dropColumn('diinput_by');
        });
    }
};