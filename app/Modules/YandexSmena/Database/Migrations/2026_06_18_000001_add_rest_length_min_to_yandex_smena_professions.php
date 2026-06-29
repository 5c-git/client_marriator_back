<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('yandex_smena_professions', function (Blueprint $table) {
            $table->unsignedInteger('rest_length_min')->default(0)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('yandex_smena_professions', function (Blueprint $table) {
            $table->dropColumn('rest_length_min');
        });
    }
};
