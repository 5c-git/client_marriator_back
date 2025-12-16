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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('external_id')->nullable(true)->index();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->string('external_id')->nullable(true)->index();
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->string('external_id')->nullable(true)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
