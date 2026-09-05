<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('objek_form_control', function (Blueprint $table) {
            // Variabel = nama field di form EMR (mis. subjective, gcs_e) yang
            // dihubungkan ke objek tertentu. Dipakai EmrHelper untuk mapping
            // request <-> objek secara dinamis tanpa hardcode env('OBJEK_ID_*').
            $table->string('variabel', 250)->nullable()->after('objek_id');
        });
    }

    public function down(): void
    {
        Schema::table('objek_form_control', function (Blueprint $table) {
            $table->dropColumn('variabel');
        });
    }
};
