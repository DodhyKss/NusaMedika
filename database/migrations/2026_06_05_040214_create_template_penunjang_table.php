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
        Schema::create('template_penunjang', function (Blueprint $table) {
            $table->integer('template_penunjang_id')->primary();
            $table->timestamp('input_time', 6)->nullable();
            $table->integer('input_user_id')->nullable();
            $table->timestamp('mod_time', 6)->nullable();
            $table->integer('mod_user_id')->nullable();
            $table->smallInteger('status_batal')->nullable();
            $table->integer('tindakan_id')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('kesan')->nullable();
            $table->text('saran')->nullable();
            $table->integer('req_template_user_id')->nullable();

            $table->index(['template_penunjang_id', 'tindakan_id', 'req_template_user_id'], 'template_penunjang_template_penunjang_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_penunjang');
    }
};
