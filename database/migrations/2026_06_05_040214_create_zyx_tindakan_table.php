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
        Schema::create('zyx_tindakan', function (Blueprint $table) {
            $table->increments('zyx_tindakan_id');
            $table->integer('tindakan_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->string('nama_tindakan')->nullable();
            $table->integer('jenis_tindakan_id')->nullable();
            $table->smallInteger('schema_tarif')->nullable();
            $table->smallInteger('schema_bmhp')->nullable();
            $table->string('tindakan_id_old', 100)->nullable();
            $table->string('kategori_tindakan_1')->nullable();
            $table->string('kategori_tindakan_2')->nullable();
            $table->timestamp('mod_change', 6)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zyx_tindakan');
    }
};
