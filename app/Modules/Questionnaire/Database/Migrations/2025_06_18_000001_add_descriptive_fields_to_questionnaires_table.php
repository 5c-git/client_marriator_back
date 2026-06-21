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
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->json('expansion_data')->nullable()->after('data');
            $table->json('error_data')->nullable()->after('expansion_data');
            $table->json('requisites_data')->nullable()->after('error_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn(['expansion_data', 'error_data', 'requisites_data']);
        });
    }
};
