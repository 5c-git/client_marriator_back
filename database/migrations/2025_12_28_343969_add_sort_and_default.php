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

        Schema::table('directory_medical_book', function (Blueprint $table) {
            $table->integer('sort')->nullable(false)->default(100);
            $table->boolean('default')->nullable(false)->default(false);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('directory_medical_book', function (Blueprint $table) {
            $table->dropColumn([
                'sort',
                'default',
            ]);
        });
    }
};
