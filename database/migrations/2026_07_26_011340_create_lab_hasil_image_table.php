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
        Schema::create('lab_hasil_image', function (Blueprint $table) {
            $table->integer('lab_hasil_image_id');
            $table->integer('lab_hasil_id');
            $table->text('image_path')->nullable();
            $table->string('image_name')->nullable();
            $table->timestamp('input_time')->nullable();
            $table->integer('status_batal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_hasil_image');
    }
};
