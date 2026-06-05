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
        Schema::create('zyx_migrate_tindakan', function (Blueprint $table) {
            $table->increments('zyx_migrate_tindakan_id');
            $table->integer('migrate_tindakan_id');
            $table->integer('bagian');
            $table->string('tindakan', 200);
            $table->integer('jasa_sarana');
            $table->integer('jasa_pelayanan');
            $table->integer('jasa_medis');
            $table->integer('jasa_biaya_umum');
            $table->integer('jasa_anastesi');
            $table->integer('bhp');
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_migrate_tindakan');
    }
};
