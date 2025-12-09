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
        Schema::table('directory_project', function (Blueprint $table) {
            $table->dateTime('date_start')->nullable(false)->default(now());
            $table->dateTime('date_end')->nullable(false)->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
