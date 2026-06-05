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
        Schema::create('vacuum_log', function (Blueprint $table) {
            $table->increments('id');
            $table->text('schemaname')->nullable();
            $table->text('tbl_name')->nullable();
            $table->decimal('dead_pct')->nullable();
            $table->timestamp('executed_at')->nullable()->default(DB::raw("now()"));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacuum_log');
    }
};
