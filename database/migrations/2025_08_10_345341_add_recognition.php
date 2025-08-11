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
        Schema::create('recognition_documents', function (Blueprint $table) {
            $table->id();
            $table->string('link');
            $table->tinyInteger('status')->default(1)->index();
            $table->json('data')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(false)->index();
            $table->string('file_field');
            $table->integer('external_package_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recognition_documents');
    }
};
