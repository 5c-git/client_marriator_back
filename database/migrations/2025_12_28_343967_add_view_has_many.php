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
        Schema::create('view_activities_view_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('view_activities_one')->index()->nullable(false);
            $table->unsignedBigInteger('view_activities_two')->index()->nullable(false);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_activities_view_activities');
    }
};
