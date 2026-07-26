<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maintenance_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestampTz('run_at')->nullable()->default(DB::raw("now()"));
            $table->text('action');
            $table->text('schema_name');
            $table->text('object_name');
            $table->decimal('bloat_ratio')->nullable();
            $table->text('object_size')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_log');
    }
};
