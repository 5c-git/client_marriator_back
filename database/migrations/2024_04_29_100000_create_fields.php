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
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->index();
            $table->string('name')->nullable(false);
            $table->text('description')->nullable(true);
            $table->json('parentFields')->nullable(true);
            $table->smallInteger('type')->nullable(false);
            $table->string('directory')->nullable();
            $table->boolean('active')->default(false)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
