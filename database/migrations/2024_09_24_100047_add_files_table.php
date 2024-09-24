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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->index();
            $table->bigInteger('user_id')->nullable()->index();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('status')->nullable(true)->index();
            $table->string('status_signature')->nullable();
            $table->dateTime('date_signature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
