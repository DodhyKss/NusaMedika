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
        Schema::create('tanda_vital_system', function (Blueprint $table) {
            $table->integer('tanda_vital_system_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('registrasi_detail_id')->nullable();
            $table->integer('pasien_id')->nullable();
            $table->decimal('nbps', 18)->nullable();
            $table->decimal('nbpd', 18)->nullable();
            $table->decimal('nbpm', 18)->nullable();
            $table->decimal('hr', 18)->nullable();
            $table->decimal('spo2', 18)->nullable();
            $table->decimal('pulse', 18)->nullable();
            $table->decimal('rr', 18)->nullable();
            $table->decimal('tskin', 18)->nullable();
            $table->decimal('abps', 18)->nullable();
            $table->decimal('abpd', 18)->nullable();
            $table->decimal('abpm', 18)->nullable();
            $table->decimal('arts', 18)->nullable();
            $table->decimal('artd', 18)->nullable();
            $table->decimal('artm', 18)->nullable();
            $table->decimal('paps', 18)->nullable();
            $table->decimal('papd', 18)->nullable();
            $table->decimal('papm', 18)->nullable();
            $table->decimal('cvp', 18)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tanda_vital_system');
    }
};
