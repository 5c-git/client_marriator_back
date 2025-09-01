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
        Schema::create('directory_radius', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->index();
            $table->unsignedInteger('value')->nullable(false)->default(1);
            $table->boolean('default')->default(false)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directory_radius');
    }
};
