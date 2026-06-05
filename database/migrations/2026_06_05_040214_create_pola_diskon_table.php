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
        Schema::create('pola_diskon', function (Blueprint $table) {
            $table->integer('pola_diskon_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('nasabah_id')->nullable();
            $table->integer('tindakan_detail_id')->nullable();
            $table->decimal('persen_diskon', 18)->nullable();

            $table->index(['pola_diskon_id', 'nasabah_id', 'tindakan_detail_id'], 'pola_diskon_pola_diskon_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pola_diskon');
    }
};
