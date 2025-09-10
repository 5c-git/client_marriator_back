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
        Schema::create('report', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('bid_id')->index();
            $table->unsignedBigInteger('order_id')->nullable(true)->index();
            $table->unsignedBigInteger('task_id')->nullable(true)->index();
            $table->dateTime('date_start')->nullable(true);
            $table->dateTime('date_end')->nullable(true);
            $table->tinyInteger('status')->nullable(true)->default(0);
            $table->json('report')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report');
    }
};
