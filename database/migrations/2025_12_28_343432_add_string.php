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
        Schema::table('directory_standard', function (Blueprint $table) {
            $table->string('name_doc')->nullable(true)->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
