<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrasi_detail', function (Blueprint $table) {
            $table->string('triase', 50)->nullable();
            $table->string('cara_masuk', 50)->nullable();
            $table->string('lokasi_rawat', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('registrasi_detail', function (Blueprint $table) {
            $table->dropColumn(['triase', 'cara_masuk']);
            $table->integer('lokasi_rawat')->nullable()->change();
        });
    }
};