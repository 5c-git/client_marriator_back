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
        Schema::create('directory_medical_book', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->index();
            $table->string('name')->nullable(false)->default('');
            $table->boolean('active')->default(false)->index();
            $table->json('parentFields')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directory_medical_book');
    }
};
