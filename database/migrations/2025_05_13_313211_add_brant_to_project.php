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
        Schema::create('directory_project_directory_brand', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->index()->nullable(false);
            $table->unsignedBigInteger('brand_id')->index()->nullable(false);
            $table->primary(['project_id', 'brand_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directory_project_directory_brand');
    }
};
