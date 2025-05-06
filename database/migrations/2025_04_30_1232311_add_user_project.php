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
        Schema::create('user_directory_project', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->index()->nullable(false);
            $table->unsignedBigInteger('project_id')->index()->nullable(false);
            $table->primary(['project_id', 'user_id']);
        });

        Schema::create('user_directory_place', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->index()->nullable(false);
            $table->unsignedBigInteger('place_id')->index()->nullable(false);
            $table->primary(['place_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_directory_project');
        Schema::dropIfExists('user_directory_place');
    }
};
