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
        Schema::table('satu_sehat_encounter', function (Blueprint $table) {
            $table->foreign(['no_mr'], 'fk_satu_sehat_encounter_pasien')->references(['no_mr'])->on('pasien')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('satu_sehat_encounter', function (Blueprint $table) {
            $table->dropForeign('fk_satu_sehat_encounter_pasien');
        });
    }
};
