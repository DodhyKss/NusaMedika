<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form', function (Blueprint $table) {
            // Slug unik per form = nama folder/file EMR (basename) yang dipakai
            // sebagai form_name di route dinamis /emr/form/{form_name}/...
            $table->string('slug', 100)->nullable()->after('nama_form');
        });
    }

    public function down(): void
    {
        Schema::table('form', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
