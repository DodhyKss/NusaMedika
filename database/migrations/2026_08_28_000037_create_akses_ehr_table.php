<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akses_ehr', function (Blueprint $table) {
            $table->increments('akses_ehr_id');
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('profesi_id')->nullable();
            $table->integer('form_id')->nullable();
            $table->integer('level_id')->nullable();
            $table->integer('bagian_id')->nullable();

            $table->index('profesi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('akses_ehr');
    }
};
