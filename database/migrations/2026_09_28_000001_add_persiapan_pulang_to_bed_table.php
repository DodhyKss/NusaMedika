<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bed', function (Blueprint $table) {
            $table->unsignedSmallInteger('flag_persiapan_pulang')->nullable()->comment('1 = Persiapan Pulang diaktifkan
2 = Tidak');
            $table->timestamp('tgl_pulang', 6)->nullable();
            $table->string('no_kamar', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bed', function (Blueprint $table) {
            $table->dropColumn(['flag_persiapan_pulang', 'tgl_pulang']);
            $table->integer('no_kamar')->nullable()->change();
        });
    }
};